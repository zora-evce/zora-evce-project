import asyncio
import logging
import os
from dotenv import load_dotenv
from datetime import datetime, timezone
from typing import Optional
from urllib.parse import urlparse, parse_qs

import httpx
from websockets.server import serve
load_dotenv(dotenv_path='.env', override=True)
from ocpp.routing import on
from ocpp.v16 import ChargePoint as CP16
from ocpp.v16.enums import RegistrationStatus, Action
from ocpp.v16 import call_result, call

# 🔧 bring in the normalizer
from ocpp_bridge import _normalize_remote_cmd

# ------------ Config ------------
LARAVEL_BASE = os.getenv("LARAVEL_BASE", "https://zora.apenable.com")
OCPP_KEY     = os.getenv("OCPP_KEY")  # must be set
POLL_SEC     = float(os.getenv("COMMAND_POLL_SECONDS", "2"))
LISTEN_HOST  = os.getenv("OCPP_LISTEN_HOST", "127.0.0.1")
LISTEN_PORT  = int(os.getenv("OCPP_LISTEN_PORT", "9000"))

assert OCPP_KEY, "OCPP_KEY env must be set"

logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(name)s: %(message)s")      
log = logging.getLogger("ocpp-server")

# httpx async client (module-level; closed on process exit)
http = httpx.AsyncClient(timeout=10.0, verify=True)

# --- Key selection helpers ---
_OCPP_MAP = None
def _parse_key_map():
    global _OCPP_MAP
    if _OCPP_MAP is not None:
        return _OCPP_MAP
    raw = os.getenv("OCPP_KEY_MAP","").strip()
    m = {}
    if raw:
        # format: A=keyA,B=keyB
        for pair in raw.split(","):
            if "=" in pair:
                k,v = pair.split("=",1)
                m[k.strip()] = v.strip()
    _OCPP_MAP = m
    return _OCPP_MAP

def _key_for_station(station_code: str, default_key: str):
    m = _parse_key_map()
    return m.get(station_code, default_key or "")

def utcnow() -> str:
    return datetime.now(timezone.utc).isoformat()

# ------------ Helpers ------------
async def post_laravel(path: str, payload: dict) -> dict:
    url = f"{LARAVEL_BASE}/api/ocpp/{path.lstrip('/')}"
    st = (payload or {}).get("station_code") or ""
    key = _key_for_station(st, OCPP_KEY)
    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-OCPP-Key": key,
    }
    r = await http.post(url, headers=headers, json=payload)
    if r.status_code >= 400:
        import logging
        logger = logging.getLogger("ocpp-server")
        logger.error("Laravel error %s for %s: %s", r.status_code, url, r.text)
    r.raise_for_status()
    return r.json()

async def poll_command(station_code: str, connector: Optional[int]) -> Optional[dict]:
    """
    Returns the RAW JSON from Laravel (not just the inner 'command'),
    so we can normalize field names robustly.
    """
    params = {"station_code": station_code}
    if connector is not None:
        params["connector"] = connector
    # pilih key per-stasiun (fallback ke OCPP_KEY jika tidak ada di map)
    key = _key_for_station(station_code, OCPP_KEY)
    headers = {"X-OCPP-Key": key}
    url = f"{LARAVEL_BASE}/api/ocpp/commands/poll"
    r = await http.get(url, headers=headers, params=params)
    r.raise_for_status()
    return r.json()


# ------------ ChargePoint class ------------
class ChargePoint(CP16):
    def __init__(self, cp_id, ws):
        super().__init__(cp_id, ws)
        self.cp_id = cp_id
        self._running = True
        self._poller_task = None
        self._tx_seq = 0

        # hasil Authorize terakhir dari backend
        self._last_auth = None  # {"id_tag": str, "ok": bool, "card_status": str, "time": str}

        # sesi aktif per connector: connector_id -> {"tx_id": int, "id_tag": str, "started_at": str}   
        self._active_sessions = {}

        # transactionId terakhir yang aktif (dipakai untuk fallback RemoteStop)
        self.active_transaction_id = None

    async def start_poller(self):
        if self._poller_task is None or self._poller_task.done():
            self._running = True
            self._poller_task = asyncio.create_task(self._command_poller())

    async def stop_poller(self):
        self._running = False
        if self._poller_task:
            self._poller_task.cancel()
            try:
                await self._poller_task
            except Exception:
                pass

    async def _command_poller(self):
        connector_hint = None
        while self._running:
            try:
                raw = await poll_command(self.cp_id, connector_hint)
                if raw:
                    log.info("poll raw: %s", raw)
                norm = _normalize_remote_cmd(raw) if raw else None

                if norm and norm.get("name"):
                    name = norm["name"]
                    payload = norm.get("payload") or {}

                    # Keep/learn connector hint if present
                    if norm.get("connector") is not None:
                        connector_hint = norm["connector"]

                    if name == "RemoteStartTransaction":
                        id_tag = payload.get("idTag") or payload.get("id_tag") or "CARD"
                        connector_id = norm.get("connector") or payload.get("connectorId")

                        # simpan transaction_id dari Laravel (session_id)
                        tx_id = norm.get("transaction_id") or payload.get("transactionId")
                        self.active_transaction_id = tx_id

                        # Build request OCPP pakai Payload
                        if connector_id is not None:
                            req = call.RemoteStartTransactionPayload(
                                id_tag=id_tag,
                                connector_id=int(connector_id)
                            )
                        else:
                            req = call.RemoteStartTransactionPayload(id_tag=id_tag)

                        # id baris di tabel remote_commands (hasil _normalize_remote_cmd)
                        cmd_id = norm.get("id")

                        try:
                            await self.call(req)
                            # kirim ACK "sent" ke Laravel
                            if cmd_id:
                                asyncio.create_task(
                                    self._ack_remote_command(
                                        cmd_id,
                                        "sent",
                                        "RemoteStartTransaction dispatched"
                                    )
                                )
                        except Exception as e:
                            log.warning("Failed to send RemoteStartTransaction: %s", e)
                            if cmd_id:
                                asyncio.create_task(
                                    self._ack_remote_command(
                                        cmd_id,
                                        "error",
                                        str(e)
                                    )
                                )

                    elif name == "RemoteStopTransaction":
                        # Ambil transactionId dari payload atau dari state aktif
                        tx_id = payload.get("transactionId") \
                                 or payload.get("transaction_id") \
                                 or self.active_transaction_id

                        if not tx_id:
                            log.error("RemoteStopTransaction missing transaction_id")
                            continue

                        req = call.RemoteStopTransactionPayload(
                            transaction_id=int(tx_id)
                        )

                        cmd_id = norm.get("id")

                        try:
                            await self.call(req)
                            # kirim ACK "sent" ke Laravel
                            if cmd_id:
                                asyncio.create_task(
                                    self._ack_remote_command(
                                        cmd_id,
                                        "sent",
                                        f"RemoteStopTransaction dispatched (tx_id={tx_id})"
                                    )
                                )
                        except Exception as e:
                            log.warning("Failed to send RemoteStopTransaction: %s", e)
                            if cmd_id:
                                asyncio.create_task(
                                    self._ack_remote_command(
                                        cmd_id,
                                        "error",
                                        str(e)
                                    )
                                )


                await asyncio.sleep(POLL_SEC)
            except Exception as e:
                log.warning("poll/send command error: %s", e)
                await asyncio.sleep(POLL_SEC)

    #def _next_tx_id(self) -> int:
        #self._tx_seq += 1
        #return self._tx_seq

    async def _safe_post(self, endpoint: str, body: dict):
        try:
            await post_laravel(endpoint, body)
        except Exception as e:
            log.exception("%s post failed: %s", endpoint, e)

    async def _ack_remote_command(self, cmd_id: int, status: str, detail: Optional[str] = None):       
        """
        Kirim ACK ke Laravel untuk remote_commands.
        status: "sent", "error", "cancelled", dll (ikuti yang dipakai Laravel).
        """
        body = {
            "id": cmd_id,
            "status": status,
            "detail": detail,
            # penting: station_code dipakai untuk pilih X-OCPP-Key yg benar
            "station_code": self.cp_id,
        }
        try:
            await post_laravel("commands/ack", body)
        except Exception as e:
            log.exception("commands/ack failed for %s: %s", cmd_id, e)

    @on('BootNotification')
    async def on_boot_notification(self, **p):
        vendor = p.get("chargePointVendor") or p.get("vendor") or "Unknown"
        model = p.get("chargePointModel") or p.get("model") or "Unknown"
        firmware = p.get("firmwareVersion")
        asyncio.create_task(self._safe_post("boot-notification", {
            "station_code": self.cp_id,
            "vendor": vendor,
            "model": model,
            "firmware": firmware,
            "timestamp": utcnow(),
            "raw": {"action": "BootNotification", **p},
        }))
        return call_result.BootNotificationPayload(
            current_time=utcnow(),
            interval=30,
            status=RegistrationStatus.accepted,
        )

    @on('Authorize')
    async def on_authorize(self, **p):
        # ambil idTag dari payload OCPP (idTag camelCase) atau fallback ke id_tag snake_case
        id_tag = p.get("idTag") or p.get("id_tag") or ""

        body = {
            "station_code": self.cp_id,
            # kirim dua-duanya supaya Laravel happy apapun rule-nya
            "idTag": id_tag,
            "id_tag": id_tag,
            "raw": {"action": "Authorize", **p},
        }

        ok = False
        card_status = "unknown"

        try:
            # tanya Laravel dan ikut keputusannya
            resp = await post_laravel("authorize", body)
            ok = bool(resp.get("ok", False))
            card_status = (resp.get("card_status") or "unknown").lower()
        except Exception as e:
            log.exception("Authorize -> Laravel error for %s: %s", self.cp_id, e)
            ok = False
            card_status = "error"

        # simpan ke state lokal
        self._last_auth = {
            "id_tag": id_tag,
            "ok": ok,
            "card_status": card_status,
            "time": utcnow(),
        }

        # mapping card_status backend -> status OCPP
        if not ok:
            status = "Invalid"
        else:
            if card_status in ("allowed", "active", "unknown", ""):
                status = "Accepted"
            elif card_status in ("rejected", "blocked"):
                status = "Blocked"
            else:
                status = "Accepted"

        return call_result.AuthorizePayload(
            id_tag_info={"status": status}
        )


    @on('MeterValues')
    async def on_meter_values(self, **p):
        connector_id = int(p.get("connectorId") or 1)
        transaction_id = p.get("transactionId")
        meter_value = p.get("meterValue") or []
        asyncio.create_task(self._safe_post("meter-values", {
            "station_code": self.cp_id,
            "connector": connector_id,
            "transactionId": str(transaction_id or ""),
            "meterValue": meter_value,
            "raw": {"action": "MeterValues", **p},
        }))
        return call_result.MeterValuesPayload()

    @on('StopTransaction')
    async def on_stop_transaction(self, **p):
        connector_id = int(p.get("connectorId") or 1)
        tx_id = int(p.get("transactionId") or 0)
        meter_stop = int(p.get("meterStop") or p.get("meter_stop") or 0)
        ts = p.get("timestamp") or utcnow()
        reason = p.get("reason")
        id_tag = p.get("idTag") or p.get("id_tag")

        session = self._active_sessions.get(connector_id)
        mismatch_id_tag = False

        if session:
            if tx_id == 0:
                tx_id = session.get("tx_id") or 0

            active_tag = session.get("id_tag")
            if id_tag and active_tag and id_tag != active_tag:
                mismatch_id_tag = True
                log.warning(
                    "StopTransaction with DIFFERENT card on %s: connector=%s active=%r stop=%r",       
                    self.cp_id, connector_id, active_tag, id_tag
                )

        asyncio.create_task(self._safe_post("stop-transaction", {
            "station_code": self.cp_id,
            "connector": connector_id,
            "transactionId": str(tx_id),
            "idTag": id_tag,
            "meterStop": meter_stop,
            "reason": reason,
            "timestamp": ts,
            "mismatch_id_tag": mismatch_id_tag,
            "raw": {"action": "StopTransaction", **p},
        }))

        if session:
            self._active_sessions.pop(connector_id, None)

        return call_result.StopTransactionPayload(
            id_tag_info={"status": "Accepted"}
        )

    @on('StatusNotification')
    async def on_status_notification(self, **p):
        connector_id = int(p.get("connectorId") or 1)
        status = p.get("status") or "Available"
        error_code = p.get("errorCode") or "NoError"
        ts = p.get("timestamp") or utcnow()
        asyncio.create_task(self._safe_post("status-notification", {
            "station_code": self.cp_id,
            "connector": connector_id,
            "status": status,
            "errorCode": error_code,
            "timestamp": ts,
            "raw": {"action": "StatusNotification", **p},
        }))
        return call_result.StatusNotificationPayload()

    @on('Heartbeat')
    async def on_heartbeat(self, **p):
        asyncio.create_task(self._safe_post("heartbeat", {
            "station_code": self.cp_id,
            "timestamp": utcnow(),
            "raw": {"action": "Heartbeat", **p},
        }))
        return call_result.HeartbeatPayload(current_time=utcnow())

# ------------ WebSocket entry ------------
async def handler(websocket, path):
    """
    Accepts either:
      /ocpp/<StationId>            (recommended)
      /ocpp?chargePointId=<Id>     (legacy)
    """
    # 1) Parse cp_id
    cp_id = "Unknown"
    try:
        u = urlparse(path)
        parts = [p for p in u.path.split("/") if p]
        if len(parts) >= 2 and parts[0] == "ocpp":
            cp_id = parts[1]
        else:
            q = parse_qs(u.query or "")
            if q.get("chargePointId"):
                cp_id = q["chargePointId"][0]
    except Exception:
        pass

    # 2) Log path + negotiated subprotocol
    log.info("Incoming OCPP connection: cp_id=%s path=%s", cp_id, path)
    log.info("Client requested subprotocol: %s", websocket.subprotocol)

    # 3) Enforce OCPP 1.6 subprotocol
    if websocket.subprotocol != "ocpp1.6":
        log.error("Rejecting: subprotocol %s not 'ocpp1.6'", websocket.subprotocol)
        await websocket.close(code=1002, reason="Subprotocol required: ocpp1.6")
        return

    # 4) Create CP and run router + poller
    charge_point = ChargePoint(cp_id, websocket)
    try:
        log.info("Starting OCPP listener for %s ...", cp_id)
        await asyncio.gather(
            charge_point.start(),        # OCPP router (from CP16)
            charge_point.start_poller(), # our command poller
            websocket.wait_closed(),     # keep task alive
        )
        log.info("OCPP listener finished for %s", cp_id)
    finally:
        await charge_point.stop_poller()
        log.info("Connection closed: %s", cp_id)

async def main():
    log.info("OCPP server starting on %s:%d", LISTEN_HOST, LISTEN_PORT)
    async with serve(handler, LISTEN_HOST, LISTEN_PORT, subprotocols=["ocpp1.6"], ping_interval=None, ping_timeout=None, close_timeout=120):
        await asyncio.Future()  # run forever

if __name__ == "__main__":
    try:
        import uvloop
        uvloop.install()
    except Exception:
        pass
    asyncio.run(main())
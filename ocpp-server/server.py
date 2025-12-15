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
from ocpp.v16.enums import RegistrationStatus
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
    raw = os.getenv("OCPP_KEY_MAP", "").strip()
    m = {}
    if raw:
        # format: A=keyA,B=keyB
        for pair in raw.split(","):
            if "=" in pair:
                k, v = pair.split("=", 1)
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
        """
        Poll Laravel every POLL_SEC seconds and execute remote commands:
        - RemoteStartTransaction
        - RemoteStopTransaction

        RemoteStop has smart fallback for transactionId:
        1. Laravel-supplied transactionId
        2. self._active_sessions[connector_id]["tx_id"]
        3. self.active_transaction_id
        """
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
                    # id command dari Laravel, dipakai untuk ACK
                    cmd_id = norm.get("id") or (raw.get("id") if isinstance(raw, dict) else None)

                    # Simpan connector_hint kalau ada
                    if norm.get("connector") is not None:
                        connector_hint = norm["connector"]

                    # -----------------------------
                    # REMOTE START
                    # -----------------------------
                    if name == "RemoteStartTransaction":
                        id_tag = payload.get("idTag") or payload.get("id_tag") or "CARD"
                        connector_id = (
                            norm.get("connector")
                            or payload.get("connectorId")
                            or connector_hint
                            or 1
                        )

                        if connector_id is not None:
                            req = call.RemoteStartTransactionPayload(
                                id_tag=id_tag,
                                connector_id=int(connector_id),
                            )
                        else:
                            # fallback: tanpa connector_id, biar charger pilih
                            req = call.RemoteStartTransactionPayload(id_tag=id_tag)

                        try:
                            log.info("[%s] RemoteStart connector=%s id_tag=%s", self.cp_id, connector_id, id_tag)
                            await self.call(req)
                            if cmd_id is not None:
                                ack_body = {
                                    "id": cmd_id,
                                    "status": "dispatched",
                                    "detail": [],
                                    "station_code": self.cp_id,
                                }
                                await self._safe_post("commands/ack", ack_body)
                        except Exception as e:
                            log.exception("RemoteStartTransaction failed: %s", e)
                            if cmd_id is not None:
                                ack_body = {
                                    "id": cmd_id,
                                    "status": "failed",
                                    "detail": [str(e)],
                                    "station_code": self.cp_id,
                                }
                                await self._safe_post("commands/ack", ack_body)

                    # -----------------------------
                    # REMOTE STOP  (SMART VERSION)
                    # -----------------------------
                    elif name == "RemoteStopTransaction":
                        log.warning('log norm')
                        log.warning(norm)
                        log.warning('log payload')
                        log.warning(payload)
                        # Tentukan connector_id dulu
                        # connector_id = (
                        #     norm.get("connector")
                        #     or payload.get("connectorId")
                        #     or connector_hint
                        #     or 1
                        # )
                        connector_id = payload.get("connectorId")

                        # 1) Coba ambil transactionId dari payload Laravel
                        tx_id = None
                        # tx_raw = (
                        #     payload.get("transactionId")
                        #     or payload.get("transaction_id")
                        #     or payload.get("tx_id")
                        # )
                        
                        tx_raw = payload.get("transactionId")

                        if tx_raw is not None:
                            try:
                                tx_id = int(tx_raw)
                            except Exception:
                                tx_id = None

                        # 2) Fallback: active_sessions untuk connector ini
                        if (not tx_id or tx_id <= 0) and connector_id is not None:
                            session = self._active_sessions.get(int(connector_id))
                            if session:
                                tx_id = session.get("tx_id")

                        # 3) Fallback: global last active transaction
                        if not tx_id or tx_id <= 0:
                            tx_id = self.active_transaction_id

                        # Jika tetap tidak ada transactionId valid → laporkan gagal ke Laravel
                        if not tx_id or tx_id <= 0:
                            log.warning(
                                "No valid transactionId for RemoteStop on %s connector %s",
                                self.cp_id,
                                connector_id,
                            )
                            if cmd_id is not None:
                                ack_body = {
                                    "id": cmd_id,
                                    "status": "failed",
                                    "detail": ["No active transactionId available"],
                                    "station_code": self.cp_id,
                                }
                                await self._safe_post("commands/ack", ack_body)
                        else:
                            # Eksekusi RemoteStop
                            log.warning('masuk bro')
                            log.warning(int(tx_id))
                            req = call.RemoteStopTransactionPayload(transaction_id=int(tx_id))
                            log.warning(req)
                            try:
                                log.info("[%s] RemoteStop connector=%s tx_id=%s", self.cp_id, connector_id, tx_id)
                                await self.call(req)
                                # bersihkan sesi aktif di memori (kalau charger nanti tidak kirim StopTransaction)
                                try:
                                    if connector_id is not None:
                                        self._active_sessions.pop(int(connector_id), None)
                                except Exception:
                                    pass

                                if cmd_id is not None:
                                    ack_body = {
                                        "id": cmd_id,
                                        "status": "dispatched",
                                        "detail": [],
                                        "station_code": self.cp_id,
                                    }
                                    await self._safe_post("commands/ack", ack_body)
                            except Exception as e:
                                log.exception("RemoteStopTransaction failed: %s", e)
                                if cmd_id is not None:
                                    ack_body = {
                                        "id": cmd_id,
                                        "status": "failed",
                                        "detail": [str(e)],
                                        "station_code": self.cp_id,
                                    }
                                    await self._safe_post("commands/ack", ack_body)

                await asyncio.sleep(POLL_SEC)

            except Exception as e:
                log.warning("poll/send command error: %s", e)
                await asyncio.sleep(POLL_SEC)

    async def _safe_post(self, endpoint: str, body: dict):
        try:
            await post_laravel(endpoint, body)
        except Exception as e:
            log.exception("%s post failed: %s", endpoint, e)

    @on('BootNotification')
    async def on_boot_notification(self, **p):
        vendor = p.get("chargePointVendor") or p.get("vendor") or "Unknown"
        model = p.get("chargePointModel") or p.get("model") or "Unknown"
        firmware = p.get("firmwareVersion") or p.get("firmware")

        body = {
            "station_code": self.cp_id,
            # OCPP-style fields
            "chargePointVendor": vendor,
            "chargePointModel": model,
            "firmwareVersion": firmware,
            # Zora custom / legacy fields
            "vendor": vendor,
            "model": model,
            "firmware": firmware,
            "timestamp": utcnow(),
            "raw": {"action": "BootNotification", **p},
        }

        asyncio.create_task(self._safe_post("boot-notification", body))

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

    @on('StartTransaction')
    async def on_start_transaction(self, **p):
        """
        Handler StartTransaction dari charger.

        Tugas:
        - Normalisasi field dari payload charger
        - Generate / ambil transaction_id yang konsisten
        - Simpan sesi aktif di self._active_sessions per connector
        - Kirim event ke Laravel (/api/ocpp/start-transaction)
        - Balikkan StartTransactionPayload ke charger dengan transaction_id yang benar
        """
        connector_id = int(p.get("connectorId") or 1)
        id_tag = p.get("idTag") or p.get("id_tag") or ""
        meter_start = int(p.get("meterStart") or p.get("meter_start") or 0)
        ts = p.get("timestamp") or utcnow()

        # --- Tentukan transaction_id ---
        # Jika charger mengirim transactionId, pakai itu.
        raw_tx = p.get("transactionId")
        try:
            tx_id = int(raw_tx) if raw_tx is not None else 0
        except Exception:
            tx_id = 0

        # Jika tidak ada / 0, generate transactionId lokal (sequence)
        if tx_id <= 0:
            self._tx_seq += 1
            tx_id = self._tx_seq

        # Simpan state lokal sesi aktif untuk connector ini
        self._active_sessions[connector_id] = {
            "tx_id": tx_id,
            "id_tag": id_tag,
            "started_at": ts,
        }
        self.active_transaction_id = tx_id

        log.info(
            "[%s] StartTransaction: connector=%s, tx_id=%s, id_tag=%s",
            self.cp_id, connector_id, tx_id, id_tag
        )

        body = {
            "station_code": self.cp_id,
            "connector": connector_id,
            # kirim untuk logging/debug ke Laravel (walau DB utama pakai session_id)
            "transactionId": str(tx_id),
            "idTag": id_tag,
            "meterStart": meter_start,
            "timestamp": ts,
            "raw": {"action": "StartTransaction", **p},
        }

        # Kirim ke Laravel secara async (tidak blocking OCPP)
        asyncio.create_task(self._safe_post("start-transaction", body))

        log.warning('tx id bro')
        log.warning(str(tx_id))
        # Response ke charger: wajib pakai transaction_id yang sama
        return call_result.StartTransactionPayload(
            transaction_id=tx_id,
            id_tag_info={"status": "Accepted"},
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
            # Laravel side expects "values"
            "values": meter_value,
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

        log.info(
            "[%s] StopTransaction: connector=%s, tx_id=%s, id_tag=%s",
            self.cp_id, connector_id, tx_id, id_tag
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
    async with serve(
        handler,
        LISTEN_HOST,
        LISTEN_PORT,
        subprotocols=["ocpp1.6"],
        ping_interval=None,
        ping_timeout=None,
        close_timeout=120,
    ):
        await asyncio.Future()  # run forever


if __name__ == "__main__":
    try:
        import uvloop
        uvloop.install()
    except Exception:
        pass
    asyncio.run(main())

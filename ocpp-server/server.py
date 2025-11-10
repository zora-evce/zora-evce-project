import asyncio
import logging
import os
import json
from datetime import datetime, timezone
from typing import Optional, Dict
from urllib.parse import urlparse, parse_qs

import httpx
import aioredis
from websockets.server import serve
from ocpp.routing import on
from ocpp.v16 import ChargePoint as CP16
from ocpp.v16.enums import RegistrationStatus, Action
from ocpp.v16 import call_result, call

# ------------ Config ------------
LARAVEL_BASE = os.getenv("LARAVEL_BASE", "https://zora.apenable.com")
OCPP_KEY     = os.getenv("OCPP_KEY")  # must be set
# POLL_SEC     = float(os.getenv("COMMAND_POLL_SECONDS", "2"))
LISTEN_HOST  = os.getenv("OCPP_LISTEN_HOST", "127.0.0.1")
LISTEN_PORT  = int(os.getenv("OCPP_LISTEN_PORT", "9000"))
REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379")

assert OCPP_KEY, "OCPP_KEY env must be set"

logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(name)s: %(message)s")
log = logging.getLogger("ocpp-server")

# httpx async client (module-level; closed on process exit)
http = httpx.AsyncClient(timeout=10.0, verify=True)

ACTIVE_CONNECTIONS: Dict[str, "ChargePoint"] = {}

def utcnow() -> str:
    return datetime.now(timezone.utc).isoformat()

# ------------ Helpers ------------
async def post_laravel(path: str, payload: dict) -> dict:
    url = f"{LARAVEL_BASE}/api/ocpp/{path.lstrip('/')}"
    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json",          # <-- add this
        "X-OCPP-Key": OCPP_KEY,
    }
    r = await http.post(url, headers=headers, json=payload)
    r.raise_for_status()
    return r.json()

# async def poll_command(station_code: str, connector: Optional[int]) -> Optional[dict]:
#     params = {"station_code": station_code}
#     if connector is not None:
#         params["connector"] = connector
#     headers = {"X-OCPP-Key": OCPP_KEY}
#     url = f"{LARAVEL_BASE}/api/ocpp/commands/poll"
#     r = await http.get(url, headers=headers, params=params)
#     r.raise_for_status()
#     data = r.json()
#     return data.get("command")

# ------------ ChargePoint class ------------
class ChargePoint(CP16):
    def __init__(self, cp_id, ws):
        super().__init__(cp_id, ws)
        self.cp_id = cp_id
        # self._running = True
        # self._poller_task = None
        self._tx_seq = 0
        
    async def send_command(self, command_name: str, payload: dict):
        log.info("[%s] Menerima perintah dari Redis: %s", self.cp_id, command_name)
        try:
            if command_name == "RemoteStartTransaction":
                id_tag = payload.get("idTag") or "CARD"
                req = call.RemoteStartTransactionPayload(id_tag=id_tag)
                await self.call(req)
                log.info("[%s] Perintah RemoteStartTransaction terkirim", self.cp_id)
            elif command_name == "RemoteStopTransaction":
                tx = int(payload.get("transactionId") or 0)
                req = call.RemoteStopTransactionPayload(transaction_id=tx)
                await self.call(req)
                log.info("[%s] Perintah RemoteStopTransaction terkirim", self.cp_id)
            else:
                log.warning("[%s] Perintah tidak diketahui dari Redis: %s", self.cp_id, command_name)
        except Exception as e:
            log.error("[%s] Gagal mengirim perintah %s: %s", self.cp_id, command_name, e)

    # async def start_poller(self):
    #     if self._poller_task is None or self._poller_task.done():
    #         self._running = True
    #         self._poller_task = asyncio.create_task(self._command_poller())

    # async def stop_poller(self):
    #     self._running = False
    #     if self._poller_task:
    #         self._poller_task.cancel()
    #         try:
    #             await self._poller_task
    #         except Exception:
    #             pass

    # async def _command_poller(self):
    #     connector_hint = None
    #     while self._running:
    #         try:
    #             cmd = await poll_command(self.cp_id, connector_hint)
    #             if cmd:
    #                 name = cmd.get("command")
    #                 payload = cmd.get("payload") or {}
    #                 if name == "RemoteStartTransaction":
    #                     id_tag = payload.get("idTag") or "CARD"
    #                     req = call.RemoteStartTransactionPayload(id_tag=id_tag)
    #                     await self.call(req)
    #                 elif name == "RemoteStopTransaction":
    #                     tx = int(payload.get("transactionId") or 0)
    #                     req = call.RemoteStopTransactionPayload(transaction_id=tx)
    #                     await self.call(req)
    #             await asyncio.sleep(POLL_SEC)
    #         except Exception as e:
    #             log.warning("poll/send command error: %s", e)
    #             await asyncio.sleep(POLL_SEC)

    def _next_tx_id(self) -> int:
        self._tx_seq += 1
        return self._tx_seq

    async def _safe_post(self, endpoint: str, body: dict):
        try:
            await post_laravel(endpoint, body)
        except Exception as e:
            log.exception("%s post failed: %s", endpoint, e)

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
            interval=300,
            status=RegistrationStatus.accepted,
        )

    @on('Authorize')
    async def on_authorize(self, **p):
        id_tag = p.get("idTag") or ""
        asyncio.create_task(self._safe_post("authorize", {
            "station_code": self.cp_id,
            "idTag": id_tag,
            "raw": {"action": "Authorize", **p},
        }))
        return call_result.AuthorizePayload(id_tag_info={"status": "Accepted"})

    @on('StartTransaction')
    async def on_start_transaction(self, **p):
        connector_id = int(p.get("connectorId") or 1)
        id_tag = p.get("idTag") or ""
        meter_start = int(p.get("meterStart") or 0)
        ts = p.get("timestamp") or utcnow()
        tx_id = self._next_tx_id()
        asyncio.create_task(self._safe_post("start-transaction", {
            "station_code": self.cp_id,
            "connector": connector_id,
            "transactionId": str(tx_id),
            "idTag": id_tag,
            "meterStart": meter_start,
            "timestamp": ts,
            "raw": {"action": "StartTransaction", **p},
        }))
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
            "meterValue": meter_value,
            "raw": {"action": "MeterValues", **p},
        }))
        return call_result.MeterValuesPayload()

    @on('StopTransaction')
    async def on_stop_transaction(self, **p):
        tx_id = int(p.get("transactionId") or 0)
        meter_stop = int(p.get("meterStop") or 0)
        ts = p.get("timestamp") or utcnow()
        reason = p.get("reason")
        id_tag = p.get("idTag")
        asyncio.create_task(self._safe_post("stop-transaction", {
            "station_code": self.cp_id,
            "connector": p.get("connectorId") or 1,
            "transactionId": str(tx_id),
            "idTag": id_tag,
            "meterStop": meter_stop,
            "reason": reason,
            "timestamp": ts,
            "raw": {"action": "StopTransaction", **p},
        }))
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
    
    ACTIVE_CONNECTIONS[cp_id] = charge_point
    
    try:
        log.info("Starting OCPP listener for %s ...", cp_id)
        await asyncio.gather(
            charge_point.start(),       # OCPP router (from CP16)
            # charge_point.start_poller(),# our command poller
            websocket.wait_closed(),    # keep task alive
        )
        log.info("OCPP listener finished for %s", cp_id)
    finally:
        # await charge_point.stop_poller()
        ACTIVE_CONNECTIONS.pop(cp_id, None)
        log.info("Connection closed: %s", cp_id)

async def redis_subscriber():
    """
    Terhubung ke Redis dan mendengarkan perintah di channel 'ocpp:commands'.
    """
    log.info("Redis subscriber starting... Menghubungkan ke %s", REDIS_URL)
    while True:
        try:
            # Terhubung ke Redis
            redis = await aioredis.from_url(REDIS_URL)
            pubsub = redis.pubsub()
            
            # Berlangganan ke channel
            await pubsub.subscribe("ocpp:commands")
            log.info("Berhasil subscribe ke Redis channel 'ocpp:commands'")
            
            # Mulai mendengarkan pesan
            async for message in pubsub.listen():
                if message["type"] != "message":
                    continue
                
                try:
                    # 1. Ambil data pesan
                    data_str = message["data"].decode("utf-8")
                    command_data = json.loads(data_str)
                    
                    cp_id = command_data.get("cp_id")
                    command_name = command_data.get("command")
                    payload = command_data.get("payload", {})

                    if not cp_id or not command_name:
                        log.warning("Pesan Redis tidak valid: %s", data_str)
                        continue

                    # 2. Cari koneksi WebSocket yang aktif untuk cp_id ini
                    charge_point = ACTIVE_CONNECTIONS.get(cp_id)
                    
                    # 3. Jika ditemukan, kirim perintah
                    if charge_point:
                        # Menjalankannya sebagai task baru agar tidak memblokir
                        # loop subscriber
                        asyncio.create_task(
                            charge_point.send_command(command_name, payload)
                        )
                    else:
                        log.warning(
                            "Menerima perintah untuk CP '%s' tapi tidak ada koneksi aktif.", 
                            cp_id
                        )
                        
                except json.JSONDecodeError:
                    log.error("Gagal parse JSON dari Redis: %s", message["data"])
                except Exception as e:
                    log.exception("Error saat memproses pesan Redis: %s", e)

        except aioredis.RedisError as e:
            log.error("Koneksi Redis gagal: %s. Mencoba lagi dalam 5 detik...", e)
            await asyncio.sleep(5)
        except Exception as e:
            log.exception("Subscriber Redis crash: %s. Mencoba lagi dalam 5 detik...", e)
            await asyncio.sleep(5)

async def main():
    log.info("OCPP server starting on %s:%d", LISTEN_HOST, LISTEN_PORT)
    
    server = serve(handler, LISTEN_HOST, LISTEN_PORT, subprotocols=["ocpp1.6"])
    await asyncio.gather(
        server,
        redis_subscriber()
    )
    
    # async with serve(handler, LISTEN_HOST, LISTEN_PORT, subprotocols=["ocpp1.6"]):
    #     await asyncio.Future()  # run forever

if __name__ == "__main__":
    try:
        import uvloop
        uvloop.install()
    except Exception:
        pass
    asyncio.run(main())

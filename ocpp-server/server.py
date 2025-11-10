import asyncio
import json
import logging
import os
from datetime import datetime, timezone
from typing import Optional
from urllib.parse import urlparse, parse_qs

import aioredis
import httpx
from websockets.server import serve
from ocpp.routing import on
from ocpp.v16 import ChargePoint as CP16
from ocpp.v16.enums import RegistrationStatus, Action
from ocpp.v16 import call_result, call

# ------------ Config ------------
LARAVEL_BASE = os.getenv("LARAVEL_BASE", "https://zora.apenable.com")
OCPP_KEY     = os.getenv("OCPP_KEY")  # must be set
LISTEN_HOST  = os.getenv("OCPP_LISTEN_HOST", "127.0.0.1")
LISTEN_PORT  = int(os.getenv("OCPP_LISTEN_PORT", "9000"))
REDIS_HOST   = os.getenv("REDIS_HOST", "127.0.0.1")
REDIS_PORT   = int(os.getenv("REDIS_PORT", "6379"))
REDIS_DB     = int(os.getenv("REDIS_DB", "0"))
REDIS_CHANNEL = os.getenv("REDIS_CHANNEL", "ocpp:commands")

assert OCPP_KEY, "OCPP_KEY env must be set"

logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(name)s: %(message)s")
log = logging.getLogger("ocpp-server")

# httpx async client (module-level; closed on process exit)
http = httpx.AsyncClient(timeout=10.0, verify=True)

# Redis connection pool (module-level)
redis_pool: Optional[aioredis.Redis] = None

async def get_redis() -> aioredis.Redis:
    """Get or create Redis connection pool"""
    global redis_pool
    if redis_pool is None:
        redis_pool = aioredis.from_url(
            f"redis://{REDIS_HOST}:{REDIS_PORT}/{REDIS_DB}",
            encoding="utf-8",
            decode_responses=True
        )
    return redis_pool

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

# Removed poll_command - now using Redis pub/sub instead

# ------------ ChargePoint class ------------
class ChargePoint(CP16):
    def __init__(self, cp_id, ws):
        super().__init__(cp_id, ws)
        self.cp_id = cp_id
        self._running = True
        self._poller_task = None
        self._tx_seq = 0

    async def start_poller(self):
        """Start Redis subscriber for this charge point"""
        if self._poller_task is None or self._poller_task.done():
            self._running = True
            self._poller_task = asyncio.create_task(self._redis_subscriber())

    async def stop_poller(self):
        """Stop Redis subscriber"""
        self._running = False
        if self._poller_task:
            self._poller_task.cancel()
            try:
                await self._poller_task
            except asyncio.CancelledError:
                pass
            except Exception as e:
                log.warning("Error stopping Redis subscriber: %s", e)

    async def _redis_subscriber(self):
        """Subscribe to Redis channel and process commands for this cp_id"""
        redis = await get_redis()
        pubsub = redis.pubsub()
        
        try:
            await pubsub.subscribe(REDIS_CHANNEL)
            log.info("Subscribed to Redis channel '%s' for cp_id=%s", REDIS_CHANNEL, self.cp_id)
            
            while self._running:
                try:
                    # Wait for message with timeout to allow checking _running flag
                    try:
                        message = await asyncio.wait_for(
                            pubsub.get_message(ignore_subscribe_messages=True),
                            timeout=1.0
                        )
                    except asyncio.TimeoutError:
                        # Timeout is expected, continue loop to check _running
                        continue
                    
                    if message is None:
                        continue
                    
                    if message.get("type") != "message":
                        continue
                    
                    # Parse message data
                    try:
                        message_data = message.get("data")
                        if isinstance(message_data, bytes):
                            message_data = message_data.decode("utf-8")
                        data = json.loads(message_data)
                    except (json.JSONDecodeError, TypeError, AttributeError) as e:
                        log.warning("Invalid JSON in Redis message: %s", e)
                        continue
                    
                    # Check if message is for this charge point
                    msg_cp_id = data.get("cp_id")
                    if msg_cp_id != self.cp_id:
                        continue
                    
                    # Process command
                    command = data.get("command")
                    payload = data.get("payload") or {}
                    command_db_id = data.get("command_db_id")
                    
                    log.info("Received command '%s' for cp_id=%s (db_id=%s)", 
                            command, self.cp_id, command_db_id)
                    
                    try:
                        if command == "RemoteStartTransaction":
                            id_tag = payload.get("idTag") or "CARD"
                            req = call.RemoteStartTransactionPayload(id_tag=id_tag)
                            await self.call(req)
                            log.info("Sent RemoteStartTransaction for cp_id=%s", self.cp_id)
                            
                        elif command == "RemoteStopTransaction":
                            tx = int(payload.get("transactionId") or 0)
                            req = call.RemoteStopTransactionPayload(transaction_id=tx)
                            await self.call(req)
                            log.info("Sent RemoteStopTransaction for cp_id=%s (tx_id=%d)", 
                                    self.cp_id, tx)
                        else:
                            log.warning("Unknown command '%s' for cp_id=%s", command, self.cp_id)
                    except Exception as e:
                        log.exception("Error processing command '%s' for cp_id=%s: %s", 
                                    command, self.cp_id, e)
                        
                except Exception as e:
                    log.warning("Redis subscriber error for cp_id=%s: %s", self.cp_id, e)
                    await asyncio.sleep(1.0)
                    
        except asyncio.CancelledError:
            log.info("Redis subscriber cancelled for cp_id=%s", self.cp_id)
        except Exception as e:
            log.exception("Fatal error in Redis subscriber for cp_id=%s: %s", self.cp_id, e)
        finally:
            try:
                await pubsub.unsubscribe(REDIS_CHANNEL)
                await pubsub.close()
            except Exception as e:
                log.warning("Error closing pubsub for cp_id=%s: %s", self.cp_id, e)

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

    # 4) Create CP and run router + Redis subscriber
    charge_point = ChargePoint(cp_id, websocket)
    try:
        log.info("Starting OCPP listener for %s ...", cp_id)
        await asyncio.gather(
            charge_point.start(),       # OCPP router (from CP16)
            charge_point.start_poller(),# Redis subscriber
            websocket.wait_closed(),    # keep task alive
        )
        log.info("OCPP listener finished for %s", cp_id)
    finally:
        await charge_point.stop_poller()
        log.info("Connection closed: %s", cp_id)

async def main():
    log.info("OCPP server starting on %s:%d", LISTEN_HOST, LISTEN_PORT)
    async with serve(handler, LISTEN_HOST, LISTEN_PORT, subprotocols=["ocpp1.6"]):
        await asyncio.Future()  # run forever

if __name__ == "__main__":
    try:
        import uvloop
        uvloop.install()
    except Exception:
        pass
    asyncio.run(main())

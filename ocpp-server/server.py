import asyncio
import json
import logging
import os
from datetime import datetime, timezone
from typing import Optional
from urllib.parse import urlparse, parse_qs

import httpx
import aioredis
from websockets.server import serve
from ocpp.routing import on
from ocpp.v16 import ChargePoint as CP16
from ocpp.v16.enums import RegistrationStatus, Action
from ocpp.v16 import call_result, call

# 🔧 bring in the normalizer
# from ocpp_bridge import _normalize_remote_cmd

# ------------ Config ------------
LARAVEL_BASE = os.getenv("LARAVEL_BASE", "https://zora.apenable.com")
OCPP_KEY     = os.getenv("OCPP_KEY")  # must be set
LISTEN_HOST  = os.getenv("OCPP_LISTEN_HOST", "127.0.0.1")
LISTEN_PORT  = int(os.getenv("OCPP_LISTEN_PORT", "9000"))
REDIS_HOST   = os.getenv("REDIS_HOST", "redis_stag")
REDIS_PORT   = int(os.getenv("REDIS_PORT", "6379"))
REDIS_DB     = int(os.getenv("REDIS_DB", "0"))
REDIS_PASSWORD = os.getenv("REDIS_PASSWORD", None)
REDIS_CHANNEL = os.getenv("REDIS_CHANNEL", "ocpp:commands")

assert OCPP_KEY, "OCPP_KEY env must be set"

logging.basicConfig(level=logging.INFO, format="%(asctime)s %(levelname)s %(name)s: %(message)s")
log = logging.getLogger("ocpp-server")

# httpx async client (module-level; closed on process exit)
http = httpx.AsyncClient(timeout=10.0, verify=True)

# Redis connection (module-level; will be initialized in main)
redis_client: Optional[aioredis.Redis] = None

def utcnow() -> str:
    return datetime.now(timezone.utc).isoformat()

# ------------ Helpers ------------
async def post_laravel(path: str, payload: dict) -> dict:
    url = f"{LARAVEL_BASE}/api/ocpp/{path.lstrip('/')}"
    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-OCPP-Key": OCPP_KEY,
    }
    r = await http.post(url, headers=headers, json=payload)
    r.raise_for_status()
    return r.json()

async def update_command_status(command_id: int, status: str):
    """
    Update command status in Laravel after processing.
    """
    try:
        await post_laravel("commands/ack", {
            "id": command_id,
            "status": status,
        })
    except Exception as e:
        log.warning("Failed to update command status: %s", e)

# ------------ ChargePoint class ------------
class ChargePoint(CP16):
    def __init__(self, cp_id, ws):
        super().__init__(cp_id, ws)
        self.cp_id = cp_id
        self._running = True
        self._poller_task = None
        self._tx_seq = 0

    async def start_poller(self):
        if self._poller_task is None or self._poller_task.done():
            self._running = True
            self._poller_task = asyncio.create_task(self._command_poller())
            log.info("Started Redis command poller for cp_id: %s", self.cp_id)

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
        Subscribe to Redis channel and process commands in real-time.
        Uses async iterator for better reliability with aioredis 2.0.1
        """
        if not redis_client:
            log.error("Redis client not initialized for cp_id: %s", self.cp_id)
            return

        pubsub = None
        try:
            pubsub = redis_client.pubsub()
            await pubsub.subscribe(REDIS_CHANNEL)
            log.info("✅ Subscribed to Redis channel: %s for cp_id: %s", REDIS_CHANNEL, self.cp_id)
            log.info("Waiting for messages on channel: %s...", REDIS_CHANNEL)

            # Use async iterator to listen for messages
            async for message in pubsub.listen():
                if not self._running:
                    log.info("Stopping command poller for cp_id: %s", self.cp_id)
                    break
                
                # Skip subscribe/unsubscribe messages
                if message['type'] not in ('message', 'pmessage'):
                    log.debug("Skipping non-message type: %s", message['type'])
                    continue
                
                log.info("📨 Raw Redis message received: type=%s", message.get('type'))
                
                try:
                    # aioredis 2.0.1 returns bytes, decode to string
                    message_data = message.get('data')
                    if message_data is None:
                        log.warning("Message data is None, skipping")
                        continue
                        
                    if isinstance(message_data, bytes):
                        data_str = message_data.decode('utf-8')
                    else:
                        data_str = str(message_data)
                    
                    log.info("📝 Decoded message string (first 200 chars): %s", data_str[:200])
                    
                    data = json.loads(data_str)
                    log.info("📋 Parsed Redis message: %s", json.dumps(data, indent=2))
                    
                    # Filter by cp_id (station code)
                    received_cp_id = data.get('cp_id')
                    log.info("🔍 Filtering: received cp_id='%s' vs current cp_id='%s'", received_cp_id, self.cp_id)
                    
                    if received_cp_id != self.cp_id:
                        log.info("⏭️  Skipping command: cp_id mismatch (received: '%s', expected: '%s')", 
                                 received_cp_id, self.cp_id)
                        continue

                    log.info("✅ Command matched! Processing command for cp_id: %s", self.cp_id)
                    log.info("📦 Command details: id=%s, command=%s, payload=%s", 
                            data.get('id'), data.get('command'), data.get('payload'))
                    
                    # Process command
                    await self._process_command(data)
                    
                except json.JSONDecodeError as e:
                    log.error("❌ Failed to decode Redis message: %s, raw data: %s", e, data_str[:200] if 'data_str' in locals() else 'N/A')
                except Exception as e:
                    log.exception("❌ Error processing Redis command: %s", e)

        except asyncio.CancelledError:
            log.info("Command poller task cancelled for cp_id: %s", self.cp_id)
        except Exception as e:
            log.exception("❌ Redis subscription failed: %s", e)
        finally:
            if pubsub:
                try:
                    await pubsub.unsubscribe(REDIS_CHANNEL)
                    await pubsub.close()
                    log.info("Unsubscribed from Redis channel: %s for cp_id: %s", REDIS_CHANNEL, self.cp_id)
                except Exception as e:
                    log.warning("Error closing pubsub: %s", e)

    async def _process_command(self, data: dict):
        """
        Process a command received from Redis.
        """
        command_id = data.get('id')
        command_name = data.get('command')
        payload = data.get('payload') or {}
        connector = data.get('connector')  # connector number

        log.info("Processing command: %s (id=%s) for cp_id=%s", command_name, command_id, self.cp_id)
        log.info("Command payload: %s", json.dumps(payload, indent=2))
        log.info("Connector number: %s", connector)

        try:
            if command_name == "RemoteStartTransaction":
                id_tag = payload.get("idTag") or payload.get("id_tag") or "CARD"
                connector_id = connector or payload.get("connectorId")
                
                log.info("RemoteStartTransaction: idTag=%s, connector_id=%s", id_tag, connector_id)
                
                if connector_id is not None:
                    req = call.RemoteStartTransactionPayload(id_tag=id_tag, connector_id=int(connector_id))
                    log.info("Calling RemoteStartTransaction with connector_id=%s", int(connector_id))
                else:
                    req = call.RemoteStartTransactionPayload(id_tag=id_tag)
                    log.info("Calling RemoteStartTransaction without connector_id")
                
                result = await self.call(req)
                log.info("RemoteStartTransaction result: %s", result)
                
                # Update command status to 'ack'
                if command_id:
                    log.info("Updating command status to 'ack' for command_id=%s", command_id)
                    await update_command_status(command_id, "ack")

            elif command_name == "RemoteStopTransaction":
                # Laravel might send 'transactionId' (str or int)
                tx_raw = payload.get("transactionId") or payload.get("transaction_id") or 0
                try:
                    tx = int(tx_raw)
                except Exception:
                    tx = 0
                
                log.info("RemoteStopTransaction: transaction_id=%s", tx)
                
                req = call.RemoteStopTransactionPayload(transaction_id=tx)
                result = await self.call(req)
                log.info("RemoteStopTransaction result: %s", result)
                
                # Update command status to 'ack'
                if command_id:
                    log.info("Updating command status to 'ack' for command_id=%s", command_id)
                    await update_command_status(command_id, "ack")
            else:
                log.warning("Unknown command: %s", command_name)

        except Exception as e:
            log.exception("Failed to execute command %s: %s", command_name, e)
            # Update command status to 'error'
            if command_id:
                log.info("Updating command status to 'error' for command_id=%s", command_id)
                await update_command_status(command_id, "error")

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
            interval=30,
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

async def init_redis():
    """Initialize Redis connection."""
    global redis_client
    try:
        # Build Redis URL
        if REDIS_PASSWORD:
            redis_url = f"redis://:{REDIS_PASSWORD}@{REDIS_HOST}:{REDIS_PORT}/{REDIS_DB}"
        else:
            redis_url = f"redis://{REDIS_HOST}:{REDIS_PORT}/{REDIS_DB}"
        
        log.info("Connecting to Redis at %s:%d (db=%d)", REDIS_HOST, REDIS_PORT, REDIS_DB)
        log.info("Redis URL: %s", redis_url.replace(REDIS_PASSWORD or '', '***') if REDIS_PASSWORD else redis_url)
        
        redis_client = aioredis.from_url(redis_url, encoding="utf-8", decode_responses=False)
        
        # Test connection
        pong = await redis_client.ping()
        log.info("Redis connection established - PING response: %s", pong)
        
        # Test pubsub capability
        test_pubsub = redis_client.pubsub()
        await test_pubsub.subscribe("test_channel")
        log.info("Redis pubsub test successful")
        await test_pubsub.unsubscribe("test_channel")
        await test_pubsub.close()
        
    except Exception as e:
        log.exception("Failed to connect to Redis: %s", e)
        redis_client = None

async def close_redis():
    """Close Redis connection."""
    global redis_client
    if redis_client:
        try:
            await redis_client.close()
            log.info("Redis connection closed")
        except Exception:
            pass
        redis_client = None

async def main():
    # Initialize Redis connection
    await init_redis()
    
    if not redis_client:
        log.error("Redis connection failed. Server will start but commands won't work.")
    
    log.info("OCPP server starting on %s:%d", LISTEN_HOST, LISTEN_PORT)
    try:
        async with serve(handler, LISTEN_HOST, LISTEN_PORT, subprotocols=["ocpp1.6"], ping_interval=None, ping_timeout=None, close_timeout=120):
            await asyncio.Future()  # run forever
    finally:
        await close_redis()

if __name__ == "__main__":
    try:
        import uvloop
        uvloop.install()
    except Exception:
        pass
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        log.info("Server stopped by user")

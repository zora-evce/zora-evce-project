# /opt/zora-ev-charger/ocpp-server/ocpp_bridge.py
from typing import Dict, Any, List, Optional

def _normalize_remote_cmd(raw):
    # raw looks like {"ok":true,"command":{"id":4,"command":"RemoteStartTransaction","payload":{"idTag":"TEST123"},"station_id":1,"connector_id":1}}
    if not raw: 
        return None
    cmd = raw.get("command") if isinstance(raw, dict) else None
    if not cmd: 
        return None
    # Accept multiple key variants
    name = (cmd.get("command") if cmd else None) or cmd.get("name") or cmd.get("action")
    payload = (cmd.get("payload") if cmd else {}) or {}
    connector = (cmd.get("connector") or cmd.get("connector_id") or 
                 cmd.get("connectorId") or payload.get("connectorId"))
    try:
        connector = int(connector) if connector is not None else None
    except Exception:
        connector = None
    # Minimal normalized shape
    return {
        "id": cmd.get("id"),
        "name": name,
        "payload": payload,
        "connector": connector,
        "raw": raw
    }
from datetime import datetime, timezone

from config import STATION_CODE, CONNECTOR
from client import HttpPoster

def iso(dt: Optional[datetime]) -> str:
    return (dt or datetime.now(timezone.utc)).astimezone(timezone.utc).isoformat()

async def boot_notification(poster: HttpPoster,
                            vendor: str = "Zora",
                            model: str = "AC3000",
                            firmware: str = "1.0.0",
                            at: Optional[datetime] = None):
    payload = {
        "station_code": STATION_CODE,
        "vendor": vendor,
        "model": model,
        "firmware": firmware,
        "timestamp": iso(at),
    }
    return await poster.post_json("boot-notification", payload)

async def authorize(poster: HttpPoster, id_tag: str):
    payload = {
        "station_code": STATION_CODE,
        "idTag": id_tag,
    }
    return await poster.post_json("authorize", payload)

async def start_transaction(poster: HttpPoster,
                            transaction_id: str,
                            id_tag: str,
                            meter_start_wh: int,
                            at: Optional[datetime] = None,
                            idem_key: Optional[str] = None):
    payload = {
        "station_code": STATION_CODE,
        "connector": CONNECTOR,
        "transactionId": transaction_id,
        "idTag": id_tag,
        "meterStart": meter_start_wh,
        "timestamp": iso(at),
    }
    return await poster.post_json("start-transaction", payload, idem=idem_key)

async def meter_values(poster: HttpPoster,
                       transaction_id: str,
                       frames: List[Dict[str, Any]],
                       idem_key: Optional[str] = None):
    """
    frames: list of {
        "timestamp": "2025-08-18T04:06:00Z",
        "sampledValue": [
            {"measurand":"Voltage","value":"229.5","unit":"V"},
            {"measurand":"Energy.Active.Import.Register","value":"1001","unit":"Wh"},
            ...
        ]
    }
    """
    payload = {
        "station_code": STATION_CODE,
        "connector": CONNECTOR,
        "transactionId": transaction_id,
        "meterValue": frames,
    }
    return await poster.post_json("meter-values", payload, idem=idem_key)

async def stop_transaction(poster: HttpPoster,
                           transaction_id: str,
                           id_tag: str,
                           meter_stop_wh: int,
                           total_kwh: float,
                           total_cost: Optional[float] = None,
                           reason: str = "Local",
                           at: Optional[datetime] = None,
                           idem_key: Optional[str] = None):
    payload = {
        "station_code": STATION_CODE,
        "connector": CONNECTOR,
        "transactionId": transaction_id,
        "idTag": id_tag,
        "meterStop": meter_stop_wh,
        "timestamp": iso(at),
        "reason": reason,
        "total_kwh": total_kwh,
    }
    if total_cost is not None:
        payload["total_cost"] = total_cost
    return await poster.post_json("stop-transaction", payload, idem=idem_key)

async def status_notification(poster: HttpPoster,
                              status: str, # e.g., "available","charging","faulted","unavailable"
                              error_code: str = "NoError",
                              at: Optional[datetime] = None):
    payload = {
        "station_code": STATION_CODE,
        "connector": CONNECTOR,
        "status": status,
        "errorCode": error_code,
        "timestamp": iso(at),
    }
    return await poster.post_json("status-notification", payload)

async def heartbeat(poster: HttpPoster, at: Optional[datetime] = None):
    payload = {
        "station_code": STATION_CODE,
        "timestamp": iso(at),
    }
    return await poster.post_json("heartbeat", payload)

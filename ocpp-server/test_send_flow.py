# /opt/zora-ev-charger/ocpp-server/test_send_flow.py
import asyncio
from datetime import datetime, timezone, timedelta

from client import HttpPoster
from ocpp_bridge import (
    boot_notification, authorize, start_transaction, meter_values,
    stop_transaction, status_notification, heartbeat
)

async def main():
    poster = HttpPoster()
    try:
        now = datetime.now(timezone.utc).replace(microsecond=0)

        print("→ BootNotification")
        print(await boot_notification(poster, at=now))

        print("→ Authorize")
        print(await authorize(poster, id_tag="CARD123"))

        print("→ StartTransaction")
        print(await start_transaction(
            poster,
            transaction_id="TX-001",
            id_tag="CARD123",
            meter_start_wh=1000,
            at=now + timedelta(minutes=5),
            idem_key="tx-001"
        ))

        print("→ MeterValues (#1)")
        print(await meter_values(poster, "TX-001", frames=[{
            "timestamp": (now + timedelta(minutes=6)).isoformat().replace("+00:00","Z"),
            "sampledValue": [
                {"measurand":"Voltage","value":"229.5","unit":"V"},
                {"measurand":"Energy.Active.Import.Register","value":"1001","unit":"Wh"}
            ]
        }], idem_key="mv-001"))

        print("→ MeterValues (#2)")
        print(await meter_values(poster, "TX-001", frames=[{
            "timestamp": (now + timedelta(minutes=10)).isoformat().replace("+00:00","Z"),
            "sampledValue": [
                {"measurand":"Current.Import","value":"7.2","unit":"A"},
                {"measurand":"Power.Active.Import","value":"1200","unit":"W"}
            ]
        }], idem_key="mv-002"))

        print("→ StatusNotification (charging)")
        print(await status_notification(poster, status="charging", at=now + timedelta(minutes=10)))

        print("→ StopTransaction")
        print(await stop_transaction(
            poster,
            transaction_id="TX-001",
            id_tag="CARD123",
            meter_stop_wh=1500,
            total_kwh=0.5,
            total_cost=1.75,
            reason="Local",
            at=now + timedelta(minutes=20),
            idem_key="stop-001"
        ))

        print("→ Heartbeat")
        print(await heartbeat(poster, at=now + timedelta(minutes=25)))

    finally:
        await poster.close()

if __name__ == "__main__":
    asyncio.run(main())

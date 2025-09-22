# /opt/zora-ev-charger/ocpp-server/config.py
import os
from dotenv import load_dotenv

load_dotenv()

API_BASE = os.getenv("API_BASE", "http://127.0.0.1/api/ocpp").rstrip("/")
API_KEY = os.getenv("API_KEY", "")
STATION_CODE = os.getenv("STATION_CODE", "Zora1")
CONNECTOR = int(os.getenv("CONNECTOR", "1"))

RETRY_MAX_ATTEMPTS = int(os.getenv("RETRY_MAX_ATTEMPTS", "5"))
RETRY_BASE_DELAY = float(os.getenv("RETRY_BASE_DELAY", "0.5"))

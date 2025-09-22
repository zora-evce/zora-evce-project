#!/usr/bin/env bash
set -euo pipefail
cd /opt/zora-ev-charger/ocpp-server
source .venv/bin/activate
export $(grep -v '^#' .env | xargs -d '\n')
exec python server.py

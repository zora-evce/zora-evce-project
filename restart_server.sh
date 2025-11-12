#!/usr/bin/env bash
set -e
cd /opt/zora-ev-charger
if pgrep -f "/opt/zora-ev-charger/.venv/bin/python server.py" >/dev/null; then
  echo "server.py already running"; exit 0
fi
source ./.venv/bin/activate
nohup python server.py >> logs/server.out 2>&1 &
echo $! > run/server.pid
echo "started PID $(cat run/server.pid)"

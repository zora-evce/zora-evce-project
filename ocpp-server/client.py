# /opt/zora-ev-charger/ocpp-server/client.py
import asyncio, json, random
from typing import Any, Dict, Optional
import aiohttp

from config import API_BASE, API_KEY, RETRY_MAX_ATTEMPTS, RETRY_BASE_DELAY

class HttpPoster:
    def __init__(self, session: Optional[aiohttp.ClientSession] = None):
        self._owns = session is None
        self.session = session or aiohttp.ClientSession(
            timeout=aiohttp.ClientTimeout(total=30)
        )

    async def close(self):
        if self._owns and not self.session.closed:
            await self.session.close()

    async def post_json(self, path: str, payload: Dict[str, Any], *,
                        idem: Optional[str] = None) -> Dict[str, Any]:
        url = f"{API_BASE}/{path.lstrip('/')}"
        headers = {
            "Content-Type": "application/json",
            "X-OCPP-Key": API_KEY,
        }
        if idem:
            headers["X-Idempotency-Key"] = idem

        attempt = 0
        while True:
            attempt += 1
            try:
                async with self.session.post(url, headers=headers, json=payload) as resp:
                    # 2xx OK
                    if 200 <= resp.status < 300:
                        return await resp.json(content_type=None)
                    # 4xx → no retry (likely validation or auth)
                    if 400 <= resp.status < 500:
                        text = await resp.text()
                        raise RuntimeError(f"{resp.status} {text}")
                    # 5xx → retry
                    raise RuntimeError(f"Server {resp.status}")
            except Exception as e:
                if attempt >= RETRY_MAX_ATTEMPTS:
                    raise
                # exponential backoff with jitter
                delay = RETRY_BASE_DELAY * (2 ** (attempt - 1))
                delay = delay * (0.8 + 0.4 * random.random())
                await asyncio.sleep(delay)

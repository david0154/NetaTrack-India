"""
NetaTrack India — AutoBase
Base class for all auto-fetch engines.
"""
import time
import requests


class AutoBase:
    def __init__(self, db, ai, log_fn):
        self.db     = db
        self.ai     = ai
        self._log   = log_fn
        self._session = requests.Session()
        self._session.headers.update({
            "User-Agent": "NetaTrackBot/1.0 (https://github.com/david0154/NetaTrack-India)"
        })

    def log(self, msg: str):
        if callable(self._log):
            self._log(msg)

    def sleep(self, seconds: float = 1.0):
        time.sleep(seconds)

    def http_json(self, url: str, timeout: int = 10) -> dict | None:
        try:
            r = self._session.get(url, timeout=timeout)
            r.raise_for_status()
            return r.json()
        except Exception as e:
            self.log(f"    [HTTP] {url[:80]} — {e}")
            return None

    def http_text(self, url: str, timeout: int = 10) -> str | None:
        try:
            r = self._session.get(url, timeout=timeout)
            r.raise_for_status()
            return r.text
        except Exception as e:
            self.log(f"    [HTTP] {url[:80]} — {e}")
            return None

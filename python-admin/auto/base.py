"""Shared base for all auto modules — HTTP helpers. AI via AIRouter."""
import urllib.request
import json
import time


class AutoBase:
    def __init__(self, db, ai, log):
        """
        ai = AIRouter instance  OR  a plain gemini_key string (backward compat)
        """
        self.db = db
        self.log = log
        # Support both old (string key) and new (AIRouter) usage
        if isinstance(ai, str):
            from auto.ai_router import AIRouter
            self.ai = AIRouter(keys={"gemini": ai} if ai else {})
        else:
            self.ai = ai
        # backward compat: gemini_key attribute
        self.gemini_key = self.ai._keys.get("gemini", "") if hasattr(self.ai, '_keys') else ""

    def http_get(self, url: str, timeout=15) -> str:
        try:
            req = urllib.request.Request(
                url, headers={"User-Agent": "Mozilla/5.0 NetaTrackBot/1.0",
                               "Accept": "application/json, text/html, */*"})
            with urllib.request.urlopen(req, timeout=timeout) as r:
                return r.read().decode("utf-8", errors="ignore")
        except Exception as e:
            self.log(f"  [HTTP] {url[:60]} — {e}")
            return ""

    def http_json(self, url: str, timeout=15):
        text = self.http_get(url, timeout)
        if not text: return None
        try: return json.loads(text)
        except Exception: return None

    def gemini(self, prompt: str) -> str:
        return self.ai.ask(prompt)

    def gemini_json(self, prompt: str) -> dict:
        return self.ai.ask_json(prompt)

    def upsert_setting(self, key, value):
        self.db.execute(
            "INSERT INTO settings (`key`,`value`) VALUES (%s,%s) ON DUPLICATE KEY UPDATE `value`=%s",
            (key, value, value)
        )

    def sleep(self, seconds=1.2):
        time.sleep(seconds)

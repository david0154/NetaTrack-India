"""Shared base for all auto modules — Gemini AI helper + HTTP fetch helper."""
import urllib.request
import urllib.parse
import json
import re
import time


class AutoBase:
    def __init__(self, db, gemini_key: str, log):
        self.db = db
        self.gemini_key = gemini_key
        self.log = log

    # ── HTTP ───────────────────────────────────────────────────────────────
    def http_get(self, url: str, timeout=15) -> str:
        try:
            req = urllib.request.Request(
                url,
                headers={"User-Agent": "Mozilla/5.0 NetaTrackBot/1.0",
                         "Accept": "application/json, text/html, */*"}
            )
            with urllib.request.urlopen(req, timeout=timeout) as r:
                return r.read().decode("utf-8", errors="ignore")
        except Exception as e:
            self.log(f"  [HTTP error] {url[:60]} — {e}")
            return ""

    def http_json(self, url: str, timeout=15):
        text = self.http_get(url, timeout)
        if not text:
            return None
        try:
            return json.loads(text)
        except Exception:
            return None

    # ── Gemini AI ──────────────────────────────────────────────────────────
    def gemini(self, prompt: str, model="gemini-1.5-flash") -> str:
        if not self.gemini_key:
            return ""
        url = (f"https://generativelanguage.googleapis.com/v1beta/models/"
               f"{model}:generateContent?key={self.gemini_key}")
        payload = json.dumps({"contents": [{"parts": [{"text": prompt}]}]}).encode()
        try:
            req = urllib.request.Request(
                url, data=payload,
                headers={"Content-Type": "application/json"},
                method="POST"
            )
            with urllib.request.urlopen(req, timeout=30) as r:
                data = json.loads(r.read())
            return data["candidates"][0]["content"]["parts"][0]["text"].strip()
        except Exception as e:
            self.log(f"  [Gemini error] {e}")
            return ""

    def gemini_json(self, prompt: str) -> dict:
        raw = self.gemini(prompt + "\n\nRespond ONLY with valid JSON, no markdown.")
        m = re.search(r'\{[\s\S]*\}', raw)
        if m:
            try:
                return json.loads(m.group())
            except Exception:
                pass
        return {}

    # ── DB helpers ─────────────────────────────────────────────────────────
    def upsert_setting(self, key, value):
        self.db.execute(
            "INSERT INTO settings (`key`,`value`) VALUES (%s,%s) "
            "ON DUPLICATE KEY UPDATE `value`=%s",
            (key, value, value)
        )

    def sleep(self, seconds=1.2):
        time.sleep(seconds)

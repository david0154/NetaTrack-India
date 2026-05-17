"""
NetaTrack India — Multi-AI Router
Tries each configured API in priority order until one succeeds.
Supported: Gemini, OpenAI, OpenRouter, Claude (Anthropic), Sarvam AI
"""
import urllib.request
import json
import re


class AIRouter:
    """
    Usage:
        ai = AIRouter(db)          # loads all keys from settings table
        text = ai.ask(prompt)      # auto-selects first working API
        data = ai.ask_json(prompt) # returns dict
    """

    def __init__(self, db=None, keys: dict = None):
        """
        Pass either a db connection (keys loaded from settings table)
        or a dict with keys:
          {gemini, openai, openrouter, claude, sarvam}
        """
        if db:
            self._keys = self._load_from_db(db)
        else:
            self._keys = keys or {}

    def _load_from_db(self, db) -> dict:
        rows = db.fetchall(
            "SELECT `key`,`value` FROM settings WHERE `key` IN "
            "('gemini_api_key','openai_api_key','openrouter_api_key',"
            "'claude_api_key','sarvam_api_key')"
        )
        mapping = {
            "gemini_api_key":     "gemini",
            "openai_api_key":     "openai",
            "openrouter_api_key": "openrouter",
            "claude_api_key":     "claude",
            "sarvam_api_key":     "sarvam",
        }
        return {mapping[r["key"]]: r["value"] for r in rows if r["value"]}

    # ── Public ───────────────────────────────────────────────────────────────
    def ask(self, prompt: str) -> str:
        """Try all configured APIs in order, return first successful response."""
        order = ["gemini", "openai", "openrouter", "claude", "sarvam"]
        for provider in order:
            key = self._keys.get(provider)
            if not key:
                continue
            try:
                result = self._call(provider, key, prompt)
                if result:
                    return result
            except Exception:
                continue
        return ""

    def ask_json(self, prompt: str) -> dict:
        raw = self.ask(prompt + "\n\nRespond ONLY with valid JSON, no markdown, no explanation.")
        m = re.search(r'\{[\s\S]*\}', raw)
        if m:
            try:
                return json.loads(m.group())
            except Exception:
                pass
        return {}

    def has_any_key(self) -> bool:
        return bool(self._keys)

    def active_providers(self) -> list:
        return list(self._keys.keys())

    # ── Providers ─────────────────────────────────────────────────────────────
    def _call(self, provider: str, key: str, prompt: str) -> str:
        if provider == "gemini":
            return self._gemini(key, prompt)
        elif provider == "openai":
            return self._openai(key, prompt)
        elif provider == "openrouter":
            return self._openrouter(key, prompt)
        elif provider == "claude":
            return self._claude(key, prompt)
        elif provider == "sarvam":
            return self._sarvam(key, prompt)
        return ""

    def _post(self, url, payload: dict, headers: dict) -> dict:
        data = json.dumps(payload).encode()
        req = urllib.request.Request(url, data=data, headers=headers, method="POST")
        with urllib.request.urlopen(req, timeout=30) as r:
            return json.loads(r.read())

    def _gemini(self, key, prompt) -> str:
        url = (f"https://generativelanguage.googleapis.com/v1beta/models/"
               f"gemini-1.5-flash:generateContent?key={key}")
        resp = self._post(url, {"contents": [{"parts": [{"text": prompt}]}]},
                          {"Content-Type": "application/json"})
        return resp["candidates"][0]["content"]["parts"][0]["text"].strip()

    def _openai(self, key, prompt) -> str:
        resp = self._post(
            "https://api.openai.com/v1/chat/completions",
            {"model": "gpt-4o-mini", "messages": [{"role": "user", "content": prompt}], "max_tokens": 1024},
            {"Content-Type": "application/json", "Authorization": f"Bearer {key}"}
        )
        return resp["choices"][0]["message"]["content"].strip()

    def _openrouter(self, key, prompt) -> str:
        resp = self._post(
            "https://openrouter.ai/api/v1/chat/completions",
            {"model": "meta-llama/llama-3-8b-instruct:free",
             "messages": [{"role": "user", "content": prompt}]},
            {"Content-Type": "application/json",
             "Authorization": f"Bearer {key}",
             "HTTP-Referer": "https://netatrack.in",
             "X-Title": "NetaTrack India"}
        )
        return resp["choices"][0]["message"]["content"].strip()

    def _claude(self, key, prompt) -> str:
        resp = self._post(
            "https://api.anthropic.com/v1/messages",
            {"model": "claude-3-haiku-20240307",
             "max_tokens": 1024,
             "messages": [{"role": "user", "content": prompt}]},
            {"Content-Type": "application/json",
             "x-api-key": key,
             "anthropic-version": "2023-06-01"}
        )
        return resp["content"][0]["text"].strip()

    def _sarvam(self, key, prompt) -> str:
        # Sarvam AI chat completions endpoint
        resp = self._post(
            "https://api.sarvam.ai/v1/chat/completions",
            {"model": "sarvam-2b",
             "messages": [{"role": "user", "content": prompt}]},
            {"Content-Type": "application/json",
             "api-subscription-key": key}
        )
        return resp["choices"][0]["message"]["content"].strip()

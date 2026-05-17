"""Detect criminal, fake, and corruption cases against leaders from news."""
import hashlib
from auto.base import AutoBase

CRIME_KEYWORDS = [
    "arrested", "FIR", "chargesheet", "convicted", "bail", "CBI", "ED raid",
    "money laundering", "corruption", "scam", "fraud", "fake", "disproportionate assets",
    "hate speech", "sedition", "murder", "rape", "assault", "bribery",
    "PMLA", "Income Tax raid", "hawala"
]


class CaseDetector(AutoBase):
    def run(self):
        leaders = self.db.fetchall(
            "SELECT id, name FROM leaders WHERE status='active' ORDER BY id ASC LIMIT 100"
        )
        self.log(f"  Scanning {len(leaders)} leaders for criminal/fake cases...")
        detected = 0
        for l in leaders:
            count = self._detect(l)
            detected += count
            self.sleep(1.5)
        self.log(f"  ✓ Found/updated {detected} cases.")

    def _detect(self, leader: dict) -> int:
        name = leader["name"]
        # Search news for criminal/legal cases
        query = f"{name} case FIR arrest India 2024 OR 2025 OR 2026"
        q = query.replace(" ", "+")[:100]
        raw = self.http_json(f"https://api.duckduckgo.com/?q={q}&format=json&no_redirect=1")
        headlines = []
        if raw:
            headlines = [t.get("Text", "") for t in (raw.get("RelatedTopics") or [])[:8] if t.get("Text")]

        if not headlines or not self.gemini_key:
            return 0

        results = self.gemini_json(
            f"Indian politician: '{name}'\n\nNews snippets:\n" +
            "\n".join(f"- {h[:200]}" for h in headlines) +
            "\n\nIdentify any criminal, legal, or corruption cases mentioned.\n"
            'Return JSON: {"cases": [{"title": "", "type": "<criminal|corruption|fake|civil|other>",'
            ' "status": "<pending|convicted|acquitted|ongoing>",'
            ' "is_fake_case": <true|false>, "description": ""}]}'
        )
        cases = results.get("cases", [])
        saved = 0
        for c in cases:
            if not c.get("title"):
                continue
            uid = hashlib.md5((name + c["title"]).encode()).hexdigest()[:32]
            existing = self.db.fetchone("SELECT id FROM criminal_cases WHERE source_uid=%s", (uid,))
            if not existing:
                try:
                    self.db.execute(
                        "INSERT INTO criminal_cases (leader_id, source_uid, title, type, status, "
                        "is_fake, description, detected_at) VALUES (%s,%s,%s,%s,%s,%s,%s,NOW())",
                        (leader["id"], uid, c["title"][:255], c["type"],
                         c["status"], 1 if c.get("is_fake_case") else 0,
                         c.get("description", "")[:1000])
                    )
                    label = "[FAKE]" if c.get("is_fake_case") else ""
                    self.log(f"    {label} {name}: {c['type'].upper()} — {c['title'][:60]}")
                    saved += 1
                except Exception:
                    pass
        return saved

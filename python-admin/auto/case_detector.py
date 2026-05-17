"""
Case Detector — LocalAI pre-screens text, cloud AI confirms.
"""
import hashlib
from auto.base import AutoBase
from auto.local_ai import LocalAI

CRIME_KEYWORDS = [
    "arrested", "FIR", "chargesheet", "convicted", "bail", "CBI", "ED raid",
    "money laundering", "corruption", "scam", "fraud", "fake",
    "PMLA", "Income Tax raid", "hawala", "bribery", "sedition",
]


class CaseDetector(AutoBase):
    def __init__(self, db, ai, log):
        super().__init__(db, ai, log)
        self.local = LocalAI()

    def run(self):
        leaders = self.db.fetchall(
            "SELECT id, name FROM leaders WHERE status='active' ORDER BY id ASC LIMIT 100"
        )
        self.log(f"  Scanning {len(leaders)} leaders for cases...")
        detected = 0
        for l in leaders:
            detected += self._detect(l)
            self.sleep(1.2)
        self.log(f"  \u2713 {detected} cases found/updated.")

    def _detect(self, leader: dict) -> int:
        name = leader["name"]
        q = (name + " case FIR arrest India 2024 2025 2026").replace(" ", "+")[:100]
        raw = self.http_json(f"https://api.duckduckgo.com/?q={q}&format=json&no_redirect=1")
        snippets = []
        if raw:
            snippets = [t.get("Text", "") for t in (raw.get("RelatedTopics") or [])[:8] if t.get("Text")]
        if not snippets:
            return 0

        combined = " ".join(snippets)

        # Step 1: Local AI — quick pre-screen
        category = self.local.classify_category(combined)
        sentiment = self.local.classify_sentiment(combined)
        if category not in ("criminal", "corruption") and sentiment != "negative":
            return 0  # Skip — local AI says not a criminal case

        # Step 2: Cloud AI — detailed extraction (if available)
        if not self.ai.has_any_key():
            # Rule-based fallback
            hits = sum(1 for kw in CRIME_KEYWORDS if kw.lower() in combined.lower())
            if hits < 2: return 0
            uid = hashlib.md5((name + combined[:50]).encode()).hexdigest()[:32]
            if self.db.fetchone("SELECT id FROM criminal_cases WHERE source_uid=%s", (uid,)):
                return 0
            self.db.execute(
                "INSERT INTO criminal_cases (leader_id,source_uid,title,type,status,is_fake,description,detected_at) "
                "VALUES (%s,%s,%s,'criminal','ongoing',0,%s,NOW())",
                (leader["id"], uid, f"Possible case: {name}",
                 self.local.summarise(combined, 40))
            )
            return 1

        results = self.ai.ask_json(
            f"Indian politician: '{name}'\nSnippets:\n" +
            "\n".join(f"- {s[:200]}" for s in snippets) +
            "\n\nIdentify criminal/corruption cases.\n"
            '{"cases": [{"title": "", "type": "<criminal|corruption|fake|civil|other>",'
            ' "status": "<pending|convicted|acquitted|ongoing>",'
            ' "is_fake_case": <true|false>, "description": ""}]}'
        )
        saved = 0
        for c in (results.get("cases") or []):
            if not c.get("title"): continue
            uid = hashlib.md5((name + c["title"]).encode()).hexdigest()[:32]
            if not self.db.fetchone("SELECT id FROM criminal_cases WHERE source_uid=%s", (uid,)):
                try:
                    self.db.execute(
                        "INSERT INTO criminal_cases (leader_id,source_uid,title,type,status,is_fake,description,detected_at) "
                        "VALUES (%s,%s,%s,%s,%s,%s,%s,NOW())",
                        (leader["id"], uid, c["title"][:255], c["type"],
                         c["status"], 1 if c.get("is_fake_case") else 0,
                         c.get("description", "")[:1000])
                    )
                    saved += 1
                    label = "[FAKE]" if c.get("is_fake_case") else ""
                    self.log(f"    {label} {name}: {c['type']} — {c['title'][:55]}")
                except Exception:
                    pass
        return saved

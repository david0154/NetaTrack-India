"""Track fund allocation, utilization, and leakage for leaders/constituencies."""
from auto.base import AutoBase

MPLADS_URL = "https://mplads.gov.in/mplads/Content/Pdf/MPLADS_Guidelines.pdf"


class FundTracker(AutoBase):
    def run(self):
        leaders = self.db.fetchall(
            "SELECT id, name, constituency FROM leaders WHERE status='active' "
            "AND house='Lok Sabha' LIMIT 60"
        )
        self.log(f"  Tracking MPLADS funds for {len(leaders)} MPs...")
        tracked = 0
        for l in leaders:
            if self._track(l):
                tracked += 1
            self.sleep(1.5)
        self.log(f"  ✓ Tracked/updated {tracked} fund records.")

    def _track(self, leader: dict) -> bool:
        if not self.gemini_key:
            return False
        name  = leader["name"]
        const = leader["constituency"] or ""
        query = f"{name} {const} MPLADS fund utilization development work 2024 2025"
        q = query.replace(" ", "+")[:100]
        raw = self.http_json(f"https://api.duckduckgo.com/?q={q}&format=json&no_redirect=1")
        snippets = []
        if raw:
            snippets = [t.get("Text", "") for t in (raw.get("RelatedTopics") or [])[:6] if t.get("Text")]
        if not snippets:
            return False

        result = self.gemini_json(
            f"Indian MP: '{name}', Constituency: '{const}'\n"
            f"News/data snippets:\n" +
            "\n".join(f"- {s[:200]}" for s in snippets) +
            "\n\nExtract fund allocation and utilization data.\n"
            'Return JSON: {"allocated_cr": <number or null>, "utilized_cr": <number or null>,'
            ' "utilization_pct": <0-100 or null>, "projects_count": <number or null>,'
            ' "leakage_suspected": <true|false>, "summary": "<brief>"}'
        )
        if not result.get("summary"):
            return False
        try:
            self.db.execute(
                "INSERT INTO fund_records (leader_id, allocated_cr, utilized_cr, utilization_pct, "
                "projects_count, leakage_suspected, summary, recorded_at) "
                "VALUES (%s,%s,%s,%s,%s,%s,%s,NOW()) "
                "ON DUPLICATE KEY UPDATE utilized_cr=%s, utilization_pct=%s, summary=%s",
                (
                    leader["id"],
                    result.get("allocated_cr"), result.get("utilized_cr"),
                    result.get("utilization_pct"), result.get("projects_count"),
                    1 if result.get("leakage_suspected") else 0,
                    result.get("summary", "")[:1000],
                    result.get("utilized_cr"), result.get("utilization_pct"),
                    result.get("summary", "")[:1000]
                )
            )
            leakage = " ⚠️ LEAKAGE SUSPECTED" if result.get("leakage_suspected") else ""
            self.log(f"    {name}: {result.get('utilization_pct','-')}% utilized{leakage}")
            return True
        except Exception as e:
            self.log(f"    Fund DB error for {name}: {e}")
            return False

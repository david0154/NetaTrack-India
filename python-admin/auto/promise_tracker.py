"""Track fulfillment status of leader promises by searching news."""
from auto.base import AutoBase

NEWS_SEARCH = "https://newsapi.org/v2/everything?q={query}&language=en&pageSize=5&apiKey={key}"
GNEWS = "https://gnews.io/api/v4/search?q={query}&lang=en&max=5&apikey={key}"


class PromiseTracker(AutoBase):
    def run(self):
        promises = self.db.fetchall(
            "SELECT p.id, p.title, p.status, l.name AS leader_name "
            "FROM promises p JOIN leaders l ON p.leader_id=l.id "
            "WHERE p.status IN ('pending','in_progress') ORDER BY p.id DESC LIMIT 60"
        )
        self.log(f"  Checking {len(promises)} pending/in-progress promises...")
        updated = 0
        for p in promises:
            status = self._check_promise(p)
            if status and status != p["status"]:
                self.db.execute(
                    "UPDATE promises SET status=%s, last_checked=NOW() WHERE id=%s",
                    (status, p["id"])
                )
                self.log(f"    Promise #{p['id']} '{p['title'][:50]}' → {status}")
                updated += 1
            self.sleep(1)
        self.log(f"  ✓ Updated {updated} promise statuses.")

    def _check_promise(self, p) -> str:
        if not self.gemini_key:
            return ""
        # Search for news about this promise
        query = f"{p['leader_name']} {p['title'][:60]} India"
        headlines = self._search_news(query)
        if not headlines:
            return ""
        result = self.gemini_json(
            f"Indian politician '{p['leader_name']}' made this promise: '{p['title']}'\n\n"
            f"Recent news headlines related to this:\n" +
            "\n".join(f"- {h}" for h in headlines) +
            "\n\nBased on these headlines, what is the current status of this promise?\n"
            'Return JSON: {"status": "<pending|in_progress|fulfilled|broken|partial>",'
            ' "confidence": <0-100>, "summary": "<brief explanation>"}'
        )
        return result.get("status", "")

    def _search_news(self, query: str) -> list:
        # Use GNews free API (no key needed for basic)
        q = query.replace(" ", "+")[:80]
        data = self.http_json(f"https://gnews.io/api/v4/search?q={q}&lang=en&max=5&apikey=free")
        if data and data.get("articles"):
            return [a["title"] for a in data["articles"]]
        # Fallback: DuckDuckGo instant API (no key)
        raw = self.http_json(f"https://api.duckduckgo.com/?q={q}+India+politics&format=json&no_redirect=1")
        if raw and raw.get("RelatedTopics"):
            return [t.get("Text", "") for t in raw["RelatedTopics"][:5] if t.get("Text")]
        return []

"""Fetch political announcements from news RSS feeds and classify with Gemini AI."""
import xml.etree.ElementTree as ET
import hashlib
from auto.base import AutoBase

RSS_FEEDS = [
    ("NDTV Politics",        "https://feeds.feedburner.com/ndtvnews-india-news"),
    ("The Hindu Politics",   "https://www.thehindu.com/news/national/?service=rss"),
    ("Indian Express",       "https://indianexpress.com/section/political-pulse/feed/"),
    ("LiveMint Politics",    "https://www.livemint.com/rss/politics"),
    ("Times of India",       "https://timesofindia.indiatimes.com/rssfeeds/1221656.cms"),
    ("ANI News",             "https://www.aninews.in/rss/national.xml"),
]


class AnnouncementFetcher(AutoBase):
    def run(self):
        total = 0
        for source, url in RSS_FEEDS:
            self.log(f"  Fetching RSS: {source}")
            items = self._parse_rss(url)
            for item in items[:20]:
                if self._save_announcement(source, item):
                    total += 1
            self.sleep(1)
        self.log(f"  ✓ {total} new announcements saved.")

    def _parse_rss(self, url):
        raw = self.http_get(url)
        if not raw:
            return []
        items = []
        try:
            root = ET.fromstring(raw)
            ns = {"media": "http://search.yahoo.com/mrss/"}
            for item in root.iter("item"):
                title = (item.findtext("title") or "").strip()
                desc  = (item.findtext("description") or "").strip()
                link  = (item.findtext("link") or "").strip()
                date  = (item.findtext("pubDate") or "").strip()
                if title:
                    items.append({"title": title, "description": desc[:400],
                                  "link": link, "date": date})
        except Exception as e:
            self.log(f"    RSS parse error: {e}")
        return items

    def _save_announcement(self, source, item):
        title = item["title"]
        uid   = hashlib.md5((title + item["link"]).encode()).hexdigest()[:32]
        existing = self.db.fetchone("SELECT id FROM announcements WHERE source_uid=%s", (uid,))
        if existing:
            return False

        # Classify with Gemini AI
        category = "general"
        leader_name = ""
        if self.gemini_key:
            result = self.gemini_json(
                f"News headline: \"{title}\"\n"
                f"Description: \"{item['description'][:200]}\"\n"
                f"Classify this Indian political news. Return JSON:\n"
                f'{{"category": "<promise|project|fund|scam|criminal|election|general>",'
                f'"leader_name": "<name of Indian politician mentioned or empty string>",'
                f'"sentiment": "<positive|negative|neutral>",'
                f'"importance": <1-5>}}'
            )
            category    = result.get("category", "general")
            leader_name = result.get("leader_name", "")
            self.sleep(0.6)

        # Try to link to existing leader
        leader_id = None
        if leader_name:
            row = self.db.fetchone(
                "SELECT id FROM leaders WHERE name LIKE %s LIMIT 1",
                (f"%{leader_name.split()[-1]}%",)
            )
            leader_id = row["id"] if row else None

        self.db.execute(
            "INSERT INTO announcements (source_uid, source_name, title, description, link, "
            "category, leader_id, status, published_at) VALUES (%s,%s,%s,%s,%s,%s,%s,'pending',NOW())",
            (uid, source, title[:255], item["description"][:1000],
             item["link"][:500], category, leader_id)
        )
        self.log(f"    + [{category}] {title[:60]}")
        return True

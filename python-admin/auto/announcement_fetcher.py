"""
Announcement Fetcher — now uses LocalAI for offline analysis first,
then falls back to cloud AI router for richer classification.
"""
import xml.etree.ElementTree as ET
import hashlib
from auto.base import AutoBase
from auto.local_ai import LocalAI

RSS_FEEDS = [
    ("PMO India",           "https://www.pmindia.gov.in/en/feed/"),
    ("PIB India",           "https://www.pib.gov.in/RssMain.aspx"),
    ("MyGov",               "https://www.mygov.in/feeds/pib/"),
    ("NDTV Politics",       "https://feeds.feedburner.com/ndtvnews-india-news"),
    ("The Hindu",           "https://www.thehindu.com/news/national/?service=rss"),
    ("Indian Express",      "https://indianexpress.com/section/political-pulse/feed/"),
    ("LiveMint Politics",   "https://www.livemint.com/rss/politics"),
    ("Times of India",      "https://timesofindia.indiatimes.com/rssfeeds/1221656.cms"),
    ("Hindustan Times",     "https://www.hindustantimes.com/feeds/rss/india-news/rssfeed.xml"),
    ("ANI News",            "https://www.aninews.in/rss/national.xml"),
    ("ADR India",           "https://adrindia.org/feed"),
    ("Lok Sabha RSS",       "https://loksabha.nic.in/rss/lsrss.xml"),
    ("Rajya Sabha RSS",     "https://rajyasabha.nic.in/rsnew/rss/rss.xml"),
]


class AnnouncementFetcher(AutoBase):
    def __init__(self, db, ai, log):
        super().__init__(db, ai, log)
        self.local = LocalAI(use_transformers=True)

    def run(self):
        total = 0
        for source, url in RSS_FEEDS:
            self.log(f"  RSS: {source}")
            items = self._parse_rss(url)
            for item in items[:20]:
                if self._save(source, item):
                    total += 1
            self.sleep(0.6)
        self.log(f"  \u2713 {total} new items saved.")

    def _parse_rss(self, url):
        raw = self.http_get(url)
        if not raw: return []
        items = []
        try:
            root = ET.fromstring(raw)
            for item in root.iter("item"):
                title = (item.findtext("title") or "").strip()
                desc  = (item.findtext("description") or "").strip()
                link  = (item.findtext("link") or "").strip()
                if title:
                    items.append({"title": title, "description": desc[:500], "link": link})
        except Exception:
            pass
        return items

    def _save(self, source: str, item: dict) -> bool:
        uid = hashlib.md5((item["title"] + item["link"]).encode()).hexdigest()[:32]
        if self.db.fetchone("SELECT id FROM announcements WHERE source_uid=%s", (uid,)):
            return False

        # Step 1: Offline local AI analysis (always runs, no API key needed)
        local_result = self.local.analyse_news_item(item["title"], item["description"])
        category    = local_result["category"]
        sentiment   = local_result["sentiment"]
        importance  = local_result["importance"]
        summary     = local_result["summary"]
        leader_names = local_result["leader_names"]

        # Step 2: Cloud AI enrichment (only if API key exists, improves accuracy)
        if self.ai.has_any_key():
            cloud = self.ai.ask_json(
                f"Indian political news: \"{item['title']}\"\n"
                f"Description: \"{item['description'][:200]}\"\n"
                f"Local AI says category={category}. Verify and return JSON:\n"
                f'{{"category": "<promise|project|fund|scam|criminal|election|general>",'
                f'"leader_name": "<main politician or empty>",'
                f'"importance": <1-5>, "tags": ["tag1"]}}'
            )
            if cloud.get("category"):
                category = cloud["category"]
            if cloud.get("leader_name"):
                leader_names = [cloud["leader_name"]] + leader_names
            self.sleep(0.5)

        # Link to DB leader
        leader_id = None
        for name in leader_names:
            last = name.split()[-1] if name else ""
            if not last: continue
            row = self.db.fetchone(
                "SELECT id FROM leaders WHERE name LIKE %s LIMIT 1",
                (f"%{last}%",)
            )
            if row:
                leader_id = row["id"]
                break

        self.db.execute(
            "INSERT INTO announcements (source_uid,source_name,title,description,link,"
            "category,sentiment,importance,ai_summary,leader_id,status,published_at) "
            "VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,'pending',NOW())",
            (uid, source, item["title"][:255], item["description"][:1000],
             item["link"][:500], category, sentiment, importance, summary[:500], leader_id)
        )
        self.log(f"    [{category}/{sentiment}] {item['title'][:55]}")
        return True

"""
NetaTrack India — India Government Data Fetcher
Sources:
  - ECI (Election Commission of India) open data
  - Sansad.in (Lok Sabha official API)
  - Rajya Sabha member list
  - MyGov.in announcements RSS
  - Vidhan Sabha state portals
  - VigilanceIndia / Anti-Corruption Bureau
  - PMO India press releases
  - MoSPI (Ministry of Statistics)
  - RTI online portal news
  - ADR (Association for Democratic Reforms) data
"""
import xml.etree.ElementTree as ET
import hashlib
import re
from auto.base import AutoBase


GOVT_RSS_SOURCES = [
    # Central Government
    ("PMO India",           "https://www.pmindia.gov.in/en/feed/"),
    ("MyGov Announcements", "https://www.mygov.in/feeds/pib/"),
    ("PIB India",           "https://www.pib.gov.in/RssMain.aspx"),
    ("Lok Sabha Debates",   "https://loksabha.nic.in/rss/lsrss.xml"),
    ("Rajya Sabha",         "https://rajyasabha.nic.in/rsnew/rss/rss.xml"),
    # News
    ("NDTV India",          "https://feeds.feedburner.com/ndtvnews-india-news"),
    ("The Hindu Politics",  "https://www.thehindu.com/news/national/?service=rss"),
    ("Indian Express Pol",  "https://indianexpress.com/section/political-pulse/feed/"),
    ("Hindustan Times",     "https://www.hindustantimes.com/feeds/rss/india-news/rssfeed.xml"),
    ("LiveMint",            "https://www.livemint.com/rss/politics"),
    ("ANI News",            "https://www.aninews.in/rss/national.xml"),
    ("PTI",                 "https://www.ptinews.com/rss/"),
    # Accountability
    ("ADR India",           "https://adrindia.org/feed"),
    ("Transparency Intl",   "https://www.transparency.org/en/feed"),
    ("CommonCause India",   "https://www.commoncause.in/feed"),
]

# ECI Open Data endpoints
ECI_CANDIDATE_RESULTS = "https://results.eci.gov.in/ResultAcGenMar2024/index.htm"
ECI_AFFIDAVIT_API     = "https://myneta.info/api/v1/candidates?election_type=ls&year=2024&limit=100&offset={offset}"
ADR_API               = "https://adrindia.org/api/v1/candidates?limit=50&offset={offset}"
SANSAD_API            = "https://sansad.in/ls/api/members?limit=50&offset={offset}&format=json"
RAJYA_SABHA_API       = "https://rajyasabha.nic.in/rsnew/members/alpha_list_currenthousemembers.aspx"
MYNETA_CRIMINAL       = "https://myneta.info/ls2024/index.php?action=show_candidates&constituency_id={cid}&stat=criminal"


class GovtDataFetcher(AutoBase):
    def run(self):
        self._fetch_rss_all()
        self._fetch_eci_affidavits()
        self._fetch_rajya_sabha()
        self._fetch_myneta_criminal()
        self.log("  ✓ Government data fetch complete.")

    # ── RSS ───────────────────────────────────────────────────────────────
    def _fetch_rss_all(self):
        self.log(f"  Fetching {len(GOVT_RSS_SOURCES)} RSS/govt sources...")
        total = 0
        for source, url in GOVT_RSS_SOURCES:
            items = self._parse_rss(url)
            for item in items[:15]:
                if self._save_rss_item(source, item):
                    total += 1
            self.sleep(0.8)
        self.log(f"  + {total} new RSS items saved")

    def _parse_rss(self, url: str) -> list:
        raw = self.http_get(url)
        if not raw:
            return []
        items = []
        try:
            root = ET.fromstring(raw)
            for item in root.iter("item"):
                title = (item.findtext("title") or "").strip()
                desc  = (item.findtext("description") or "").strip()
                link  = (item.findtext("link") or "").strip()
                date  = (item.findtext("pubDate") or "").strip()
                if title:
                    items.append({"title": title, "description": desc[:500],
                                  "link": link, "date": date})
        except Exception:
            pass
        return items

    def _save_rss_item(self, source: str, item: dict) -> bool:
        uid = hashlib.md5((item["title"] + item["link"]).encode()).hexdigest()[:32]
        if self.db.fetchone("SELECT id FROM announcements WHERE source_uid=%s", (uid,)):
            return False
        self.db.execute(
            "INSERT INTO announcements (source_uid,source_name,title,description,link,category,status,published_at) "
            "VALUES (%s,%s,%s,%s,%s,'general','pending',NOW())",
            (uid, source, item["title"][:255], item["description"][:1000], item["link"][:500])
        )
        return True

    # ── ECI / MyNeta Affidavits ─────────────────────────────────────────────────
    def _fetch_eci_affidavits(self):
        self.log("  Fetching ECI/MyNeta candidate affidavits (LS 2024)...")
        fetched = 0
        for offset in range(0, 300, 100):
            data = self.http_json(ECI_AFFIDAVIT_API.format(offset=offset))
            if not data:
                break
            candidates = data.get("candidates") or data.get("data") or []
            if not candidates:
                break
            for c in candidates:
                self._upsert_eci_candidate(c)
                fetched += 1
            self.sleep(1)
        self.log(f"  + {fetched} ECI affidavit records processed")

    def _upsert_eci_candidate(self, c: dict):
        name  = (c.get("name") or c.get("candidate_name") or "").strip()
        if not name:
            return
        slug = re.sub(r'[^a-z0-9]+', '-', name.lower()).strip('-')
        party = c.get("party") or ""
        state = c.get("state") or c.get("state_name") or ""
        assets = c.get("total_assets") or c.get("assets") or ""
        liab   = c.get("liabilities") or ""
        crimes = int(c.get("criminal_cases") or c.get("total_criminal_cases") or 0)

        # Upsert party/state
        party_id = state_id = None
        if party:
            self.db.execute(
                "INSERT INTO parties (name,abbreviation) VALUES (%s,%s) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)",
                (party, party[:12])
            )
            row = self.db.fetchone("SELECT id FROM parties WHERE name=%s", (party,))
            party_id = row["id"] if row else None
        if state:
            self.db.execute(
                "INSERT INTO states (name) VALUES (%s) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)",
                (state,)
            )
            row = self.db.fetchone("SELECT id FROM states WHERE name=%s", (state,))
            state_id = row["id"] if row else None

        existing = self.db.fetchone("SELECT id FROM leaders WHERE slug=%s", (slug,))
        if existing:
            lid = existing["id"]
            self.db.execute(
                "UPDATE leaders SET eci_assets=%s, eci_liabilities=%s, eci_criminal_cases=%s, "
                "party_id=COALESCE(party_id,%s), state_id=COALESCE(state_id,%s) WHERE id=%s",
                (str(assets)[:100], str(liab)[:100], crimes, party_id, state_id, lid)
            )
            # Save criminal count as case record
            if crimes > 0:
                uid = hashlib.md5((name + "eci_affidavit").encode()).hexdigest()[:32]
                if not self.db.fetchone("SELECT id FROM criminal_cases WHERE source_uid=%s", (uid,)):
                    self.db.execute(
                        "INSERT INTO criminal_cases (leader_id,source_uid,title,type,status,is_fake,description,detected_at) "
                        "VALUES (%s,%s,%s,'criminal','ongoing',0,%s,NOW())",
                        (lid, uid, f"ECI Affidavit: {crimes} declared criminal case(s)",
                         f"Self-declared in ECI affidavit for LS 2024. Total: {crimes} cases.")
                    )
        else:
            self.db.execute(
                "INSERT INTO leaders (name,slug,party_id,state_id,eci_assets,eci_liabilities,"
                "eci_criminal_cases,status,house) VALUES (%s,%s,%s,%s,%s,%s,%s,'active','Lok Sabha')",
                (name, slug, party_id, state_id, str(assets)[:100], str(liab)[:100], crimes)
            )

    # ── Rajya Sabha ────────────────────────────────────────────────────────────
    def _fetch_rajya_sabha(self):
        self.log("  Fetching Rajya Sabha member list...")
        for offset in range(0, 250, 50):
            data = self.http_json(f"https://sansad.in/rs/api/members?limit=50&offset={offset}&format=json")
            if not data:
                break
            members = data.get("members") or data.get("data") or []
            if not members:
                break
            for m in members:
                name = (m.get("name") or m.get("memberName") or "").strip()
                if not name:
                    continue
                slug = re.sub(r'[^a-z0-9]+', '-', name.lower()).strip('-')
                party = m.get("party") or ""
                state = m.get("state") or ""
                party_id = state_id = None
                if party:
                    self.db.execute(
                        "INSERT INTO parties (name,abbreviation) VALUES (%s,%s) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)",
                        (party, party[:12])
                    )
                    row = self.db.fetchone("SELECT id FROM parties WHERE name=%s", (party,))
                    party_id = row["id"] if row else None
                if state:
                    self.db.execute(
                        "INSERT INTO states (name) VALUES (%s) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)",
                        (state,)
                    )
                    row = self.db.fetchone("SELECT id FROM states WHERE name=%s", (state,))
                    state_id = row["id"] if row else None
                ex = self.db.fetchone("SELECT id FROM leaders WHERE slug=%s", (slug,))
                if not ex:
                    self.db.execute(
                        "INSERT INTO leaders (name,slug,designation,party_id,state_id,house,status) "
                        "VALUES (%s,%s,'Rajya Sabha MP',%s,%s,'Rajya Sabha','active')",
                        (name, slug, party_id, state_id)
                    )
                    self.log(f"    + RS: {name}")
            self.sleep(0.8)

    # ── MyNeta Criminal Data ─────────────────────────────────────────────────
    def _fetch_myneta_criminal(self):
        self.log("  Fetching MyNeta criminal MP data...")
        data = self.http_json("https://myneta.info/api/v1/candidates?election_type=ls&year=2024&criminal=1&limit=100")
        if not data:
            return
        candidates = data.get("candidates") or data.get("data") or []
        for c in candidates:
            name = (c.get("name") or c.get("candidate_name") or "").strip()
            if not name:
                continue
            slug = re.sub(r'[^a-z0-9]+', '-', name.lower()).strip('-')
            row = self.db.fetchone("SELECT id FROM leaders WHERE slug=%s", (slug,))
            if row:
                crimes = int(c.get("criminal_cases") or 0)
                uid = hashlib.md5((name + "myneta_criminal").encode()).hexdigest()[:32]
                if crimes > 0 and not self.db.fetchone("SELECT id FROM criminal_cases WHERE source_uid=%s", (uid,)):
                    self.db.execute(
                        "INSERT INTO criminal_cases (leader_id,source_uid,title,type,status,is_fake,description,detected_at) "
                        "VALUES (%s,%s,%s,'criminal','ongoing',0,%s,NOW())",
                        (row["id"], uid,
                         f"MyNeta: {crimes} criminal case(s) declared",
                         f"Source: myneta.info LS 2024. Total criminal cases: {crimes}")
                    )
        self.log(f"  + MyNeta criminal data merged")

"""Fetch Indian political leader data from Wikipedia + Lok Sabha/RS open APIs."""
import re
from auto.base import AutoBase


WIKIPEDIA_SEARCH = "https://en.wikipedia.org/w/api.php?action=query&list=search&srsearch={q}&srlimit=5&format=json"
WIKIPEDIA_SUMMARY = "https://en.wikipedia.org/api/rest_v1/page/summary/{title}"
LOK_SABHA_MEMBERS = "https://sansad.in/ls/api/members?limit=50&offset={offset}&format=json"
RS_MEMBERS        = "https://rajyasabha.nic.in/rsnew/members/alpha_list_currenthousemembers.aspx"


class LeaderFetcher(AutoBase):
    def run(self):
        self.log("  Fetching Lok Sabha MPs from Sansad API...")
        fetched = 0
        for offset in range(0, 550, 50):
            data = self.http_json(LOK_SABHA_MEMBERS.format(offset=offset))
            if not data:
                break
            members = data.get("members") or data.get("data") or []
            if not members:
                break
            for m in members:
                self._save_member(m, "Lok Sabha")
                fetched += 1
            self.sleep(0.8)
        self.log(f"  Fetched {fetched} MPs from Sansad. Enriching with Wikipedia...")
        self._enrich_from_wikipedia()
        self.log(f"  ✓ Leader fetch complete.")

    def _save_member(self, m: dict, house: str):
        name = (m.get("name") or m.get("memberName") or "").strip()
        if not name or len(name) < 3:
            return
        slug = re.sub(r'[^a-z0-9]+', '-', name.lower()).strip('-')
        constituency = m.get("constituency") or m.get("pcName") or ""
        party_name   = m.get("party") or m.get("partyName") or ""
        state_name   = m.get("state") or m.get("stateName") or ""

        # Upsert party
        party_id = None
        if party_name:
            self.db.execute(
                "INSERT INTO parties (name, abbreviation) VALUES (%s, %s) "
                "ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)",
                (party_name, party_name[:12])
            )
            row = self.db.fetchone("SELECT id FROM parties WHERE name=%s", (party_name,))
            party_id = row["id"] if row else None

        # Upsert state
        state_id = None
        if state_name:
            self.db.execute(
                "INSERT INTO states (name) VALUES (%s) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)",
                (state_name,)
            )
            row = self.db.fetchone("SELECT id FROM states WHERE name=%s", (state_name,))
            state_id = row["id"] if row else None

        # Upsert leader
        existing = self.db.fetchone("SELECT id FROM leaders WHERE slug=%s", (slug,))
        if existing:
            self.db.execute(
                "UPDATE leaders SET designation=%s, constituency=%s, party_id=%s, state_id=%s, "
                "house=%s, status='active' WHERE id=%s",
                (house + " MP", constituency, party_id, state_id, house, existing["id"])
            )
        else:
            self.db.execute(
                "INSERT INTO leaders (name, slug, designation, constituency, party_id, state_id, house, status) "
                "VALUES (%s,%s,%s,%s,%s,%s,%s,'active')",
                (name, slug, house + " MP", constituency, party_id, state_id, house)
            )
        self.log(f"    + {name} ({party_name})")

    def _enrich_from_wikipedia(self):
        """Fill bio/photo for leaders missing them."""
        leaders = self.db.fetchall(
            "SELECT id, name FROM leaders WHERE (bio IS NULL OR bio='') AND status='active' LIMIT 40"
        )
        for l in leaders:
            title = l["name"].replace(" ", "_")
            data = self.http_json(WIKIPEDIA_SUMMARY.format(title=title))
            if data and data.get("extract"):
                bio   = data["extract"][:1000]
                thumb = (data.get("thumbnail") or {}).get("source", "")
                self.db.execute(
                    "UPDATE leaders SET bio=%s, photo_url=%s WHERE id=%s",
                    (bio, thumb, l["id"])
                )
                self.log(f"    Wiki: {l['name']} ✓")
            self.sleep(0.5)

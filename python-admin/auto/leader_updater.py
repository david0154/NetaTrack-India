"""
NetaTrack India — Leader Updater
Smart update engine for all leaders.

Sources (in order):
  1. Sansad.in Lok Sabha API   — all 543 MPs (name, party, state, constituency)
  2. Sansad.in Rajya Sabha API — all 245 RS MPs
  3. Wikipedia REST API        — bio text + photo URL
  4. Local AI                  — credibility + sentiment of bio

Smart diff: only updates DB fields that actually changed.
Safe to run daily — never duplicates.
"""
import re
import time
from auto.base import AutoBase
from auto.local_ai import LocalAI

# API endpoints
LS_API   = "https://sansad.in/ls/api/members?limit=50&offset={offset}&format=json"
RS_API   = "https://sansad.in/rs/api/members?limit=50&offset={offset}&format=json"
WIKI_API = "https://en.wikipedia.org/api/rest_v1/page/summary/{title}"
WIKI_SEARCH = "https://en.wikipedia.org/w/api.php?action=query&list=search&srsearch={q}+politician+India&srlimit=3&format=json"


class LeaderUpdater(AutoBase):

    def __init__(self, db, ai, log_fn):
        super().__init__(db, ai, log_fn)
        self.local_ai = LocalAI()
        self.added   = 0
        self.updated = 0
        self.skipped = 0

    # ------------------------------------------------------------------ #
    #  Main entry points
    # ------------------------------------------------------------------ #

    def run_full_update(self, update_photos: bool = True,
                        update_bios: bool = True,
                        sources: list = None):
        """
        Full update from all sources.
        sources = ['lok_sabha', 'rajya_sabha', 'wikipedia', 'scores']
        """
        sources = sources or ['lok_sabha', 'rajya_sabha', 'wikipedia', 'scores']
        self.added = self.updated = self.skipped = 0

        if 'lok_sabha' in sources:
            self.log("\n[▶] Updating Lok Sabha MPs...")
            self._fetch_sansad(LS_API, "Lok Sabha")

        if 'rajya_sabha' in sources:
            self.log("\n[▶] Updating Rajya Sabha MPs...")
            self._fetch_sansad(RS_API, "Rajya Sabha")

        if 'wikipedia' in sources:
            self.log("\n[▶] Enriching bios & photos from Wikipedia...")
            self._enrich_wikipedia(
                update_photos=update_photos,
                update_bios=update_bios
            )

        if 'scores' in sources:
            self.log("\n[▶] Recalculating scores...")
            self._recalc_scores()

        self.log(
            f"\n[✓] Update complete — "
            f"{self.added} added, {self.updated} updated, {self.skipped} unchanged."
        )
        return {
            'added': self.added,
            'updated': self.updated,
            'skipped': self.skipped
        }

    def run_single_leader(self, leader_id: int):
        """Refresh one specific leader (Wikipedia + scores)."""
        row = self.db.fetchone(
            "SELECT id, name, photo_url FROM leaders WHERE id=%s", (leader_id,)
        )
        if not row:
            self.log(f"[!] Leader #{leader_id} not found.")
            return
        self.log(f"[\u25b6] Refreshing: {row['name']}")
        self._wiki_enrich_one(row['id'], row['name'], force=True)
        self.log(f"[✓] {row['name']} refreshed.")

    # ------------------------------------------------------------------ #
    #  Sansad API fetch
    # ------------------------------------------------------------------ #

    def _fetch_sansad(self, api_template: str, house: str):
        fetched = 0
        for offset in range(0, 600, 50):
            data = self.http_json(api_template.format(offset=offset))
            if not data:
                break
            members = (data.get("members")
                       or data.get("data")
                       or data.get("results")
                       or [])
            if not members:
                break
            for m in members:
                self._upsert_member(m, house)
                fetched += 1
            self.log(f"  {house}: {fetched} processed...")
            self.sleep(0.8)
        self.log(f"  {house}: {fetched} total from Sansad API.")

    def _upsert_member(self, m: dict, house: str):
        name = (m.get("name") or m.get("memberName") or
                m.get("member_name") or "").strip()
        if not name or len(name) < 3:
            return

        constituency = (m.get("constituency") or m.get("pcName") or
                        m.get("constitutioncy") or "").strip()
        party_name   = (m.get("party") or m.get("partyName") or
                        m.get("party_name") or "").strip()
        state_name   = (m.get("state") or m.get("stateName") or
                        m.get("state_name") or "").strip()
        photo        = (m.get("photo") or m.get("image") or
                        m.get("photo_url") or "").strip()
        slug = re.sub(r'[^a-z0-9]+', '-', name.lower()).strip('-')

        party_id = self._upsert_party(party_name) if party_name else None
        state_id = self._upsert_state(state_name) if state_name else None

        existing = self.db.fetchone(
            "SELECT id, constituency, party_id, state_id, house, photo_url "
            "FROM leaders WHERE slug=%s OR name=%s LIMIT 1",
            (slug, name)
        )

        if existing:
            # Smart diff — only update changed fields
            changes = {}
            if constituency and existing["constituency"] != constituency:
                changes["constituency"] = constituency
            if party_id and existing["party_id"] != party_id:
                changes["party_id"] = party_id
            if state_id and existing["state_id"] != state_id:
                changes["state_id"] = state_id
            if house and existing["house"] != house:
                changes["house"] = house
            if photo and not existing["photo_url"]:
                changes["photo_url"] = photo
            if changes:
                set_clause = ", ".join(f"{k}=%s" for k in changes)
                self.db.execute(
                    f"UPDATE leaders SET {set_clause}, updated_at=NOW() WHERE id=%s",
                    list(changes.values()) + [existing["id"]]
                )
                self.updated += 1
                self.log(f"    ↻ {name} — updated: {', '.join(changes.keys())}")
            else:
                self.skipped += 1
        else:
            self.db.execute(
                "INSERT INTO leaders "
                "(name, slug, role, constituency, party_id, state_id, house, photo_url, status, created_at) "
                "VALUES (%s,%s,%s,%s,%s,%s,%s,%s,'active',NOW())",
                (name, slug, house + " MP", constituency,
                 party_id, state_id, house, photo)
            )
            self.added += 1
            self.log(f"    + {name} ({party_name or '?'}) — {constituency}")

    # ------------------------------------------------------------------ #
    #  Wikipedia enrichment
    # ------------------------------------------------------------------ #

    def _enrich_wikipedia(self, update_photos: bool, update_bios: bool,
                          limit: int = 200):
        """Enrich leaders missing bio or photo from Wikipedia."""
        where_parts = []
        if update_bios:   where_parts.append("(bio IS NULL OR bio='' OR LENGTH(bio)<50)")
        if update_photos: where_parts.append("(photo_url IS NULL OR photo_url='')")

        if not where_parts:
            self.log("  Nothing to enrich.")
            return

        where = " OR ".join(where_parts)
        leaders = self.db.fetchall(
            f"SELECT id, name, photo_url FROM leaders "
            f"WHERE ({where}) AND status='active' "
            f"ORDER BY total_score DESC LIMIT %s",
            (limit,)
        )
        self.log(f"  Enriching {len(leaders)} leaders from Wikipedia...")
        for l in leaders:
            self._wiki_enrich_one(
                l["id"], l["name"],
                update_photo=update_photos and not l["photo_url"],
                update_bio=update_bios
            )
            self.sleep(0.5)

    def _wiki_enrich_one(self, leader_id: int, name: str,
                         update_photo: bool = True,
                         update_bio: bool = True,
                         force: bool = False):
        # Try direct title first
        data = self.http_json(WIKI_API.format(
            title=name.replace(" ", "_")
        ))
        # Fallback: search
        if not data or not data.get("extract"):
            search = self.http_json(WIKI_SEARCH.format(
                q=name.replace(" ", "+")
            ))
            results = (search or {}).get("query", {}).get("search", [])
            if results:
                best_title = results[0]["title"].replace(" ", "_")
                data = self.http_json(WIKI_API.format(title=best_title))

        if not data:
            return

        changes = {}
        bio   = (data.get("extract") or "")[:1000].strip()
        thumb = ((data.get("thumbnail") or {}).get("source") or "").strip()
        dob_match = re.search(
            r'born[^\n]{0,40}?(\d{1,2}\s+\w+\s+\d{4}|\d{4}-\d{2}-\d{2})',
            bio, re.I
        )

        if bio and (update_bio or force):
            changes["bio"] = bio
        if thumb and (update_photo or force):
            changes["photo_url"] = thumb
        if dob_match:
            changes["dob_raw"] = dob_match.group(1)

        if changes:
            set_clause = ", ".join(f"{k}=%s" for k in changes
                                    if k != "dob_raw")
            vals = [v for k, v in changes.items() if k != "dob_raw"]
            if set_clause:
                self.db.execute(
                    f"UPDATE leaders SET {set_clause}, updated_at=NOW() WHERE id=%s",
                    vals + [leader_id]
                )
            self.updated += 1
            self.log(f"    Wiki ✓ {name} — {', '.join(k for k in changes if k != 'dob_raw')}")

    # ------------------------------------------------------------------ #
    #  Score recalculation
    # ------------------------------------------------------------------ #

    def _recalc_scores(self):
        """Recalculate total_score for all active leaders."""
        leaders = self.db.fetchall(
            "SELECT id, name, promise_score, fund_score, criminal_score, "
            "attendance_score, transparency_score "
            "FROM leaders WHERE status='active'"
        )
        for l in leaders:
            p  = float(l["promise_score"]  or 50)
            f  = float(l["fund_score"]     or 50)
            cr = float(l["criminal_score"] or 50)
            at = float(l["attendance_score"] or 50)
            tr = float(l["transparency_score"] or 50)
            total = round(p*0.20 + f*0.15 + cr*0.25 + at*0.20 + tr*0.20)
            self.db.execute(
                "UPDATE leaders SET total_score=%s, updated_at=NOW() WHERE id=%s",
                (total, l["id"])
            )
        self.log(f"  ✓ Scores recalculated for {len(leaders)} leaders.")

    # ------------------------------------------------------------------ #
    #  Helpers
    # ------------------------------------------------------------------ #

    def _upsert_party(self, name: str) -> int | None:
        abbr = name[:15]
        self.db.execute(
            "INSERT INTO parties (name, abbreviation) VALUES (%s,%s) "
            "ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)",
            (name, abbr)
        )
        row = self.db.fetchone("SELECT id FROM parties WHERE name=%s", (name,))
        return row["id"] if row else None

    def _upsert_state(self, name: str) -> int | None:
        self.db.execute(
            "INSERT INTO states (name) VALUES (%s) "
            "ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)",
            (name,)
        )
        row = self.db.fetchone("SELECT id FROM states WHERE name=%s", (name,))
        return row["id"] if row else None

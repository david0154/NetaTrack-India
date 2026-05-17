"""
NetaTrack India — Push data directly from Python app to website via REST API.
Requires site_url and admin_api_token in DB settings.
"""
import urllib.request
import urllib.parse
import json
from auto.base import AutoBase


class WebsitePusher(AutoBase):
    """
    Push leaders, scores, cases, announcements to the live website
    via the PHP REST API (/api/v1/push/*) with Bearer token auth.
    """

    def __init__(self, db, log):
        super().__init__(db, "", log)
        row = db.fetchone("SELECT `value` FROM settings WHERE `key`='site_url'")
        self.site_url = (row["value"].rstrip("/") if row and row["value"] else "") 
        row2 = db.fetchone("SELECT `value` FROM settings WHERE `key`='admin_api_token'")
        self.token = row2["value"] if row2 and row2["value"] else ""

    def _api(self, endpoint: str, payload: dict) -> dict:
        if not self.site_url or not self.token:
            raise ValueError("site_url or admin_api_token not configured in settings.")
        url = f"{self.site_url}/api/v1/push/{endpoint}"
        data = json.dumps(payload).encode()
        req = urllib.request.Request(
            url, data=data,
            headers={
                "Content-Type": "application/json",
                "Authorization": f"Bearer {self.token}",
                "User-Agent": "NetaTrackPythonAdmin/1.0",
            },
            method="POST"
        )
        with urllib.request.urlopen(req, timeout=20) as r:
            return json.loads(r.read())

    def push_leaders(self, limit=100):
        self.log("  [Push] Pushing leaders to website...")
        leaders = self.db.fetchall(
            "SELECT l.id, l.name, l.slug, l.designation, l.constituency, "
            "l.bio, l.photo_url, l.total_score, l.score_rank, l.status, "
            "l.is_verified, l.house, l.eci_assets, l.eci_liabilities, l.eci_criminal_cases, "
            "COALESCE(p.name,'') AS party, COALESCE(s.name,'') AS state "
            "FROM leaders l "
            "LEFT JOIN parties p ON l.party_id=p.id "
            "LEFT JOIN states s ON l.state_id=s.id "
            "WHERE l.status='active' ORDER BY l.total_score DESC LIMIT %s",
            (limit,)
        )
        resp = self._api("leaders", {"leaders": leaders})
        self.log(f"  [Push] Leaders: {resp.get('message','done')}")
        return resp

    def push_scores(self):
        self.log("  [Push] Pushing scores to website...")
        scores = self.db.fetchall(
            "SELECT id, total_score, score_rank, score_promise_completion, "
            "score_project_delivery, score_transparency, score_criminal_record, "
            "score_fund_utilization FROM leaders WHERE status='active'"
        )
        resp = self._api("scores", {"scores": scores})
        self.log(f"  [Push] Scores: {resp.get('message','done')}")
        return resp

    def push_cases(self):
        self.log("  [Push] Pushing criminal cases to website...")
        cases = self.db.fetchall(
            "SELECT c.id, c.leader_id, c.title, c.type, c.status, c.is_fake, "
            "c.description, c.detected_at, l.name AS leader_name "
            "FROM criminal_cases c JOIN leaders l ON c.leader_id=l.id "
            "ORDER BY c.id DESC LIMIT 500"
        )
        resp = self._api("cases", {"cases": cases})
        self.log(f"  [Push] Cases: {resp.get('message','done')}")
        return resp

    def push_announcements(self):
        self.log("  [Push] Pushing approved announcements to website...")
        items = self.db.fetchall(
            "SELECT id, source_name, title, description, link, category, "
            "leader_id, published_at FROM announcements WHERE status='approved' "
            "ORDER BY id DESC LIMIT 200"
        )
        resp = self._api("announcements", {"announcements": items})
        self.log(f"  [Push] Announcements: {resp.get('message','done')}")
        return resp

    def push_fund_records(self):
        self.log("  [Push] Pushing fund records to website...")
        records = self.db.fetchall(
            "SELECT f.id, f.leader_id, f.allocated_cr, f.utilized_cr, "
            "f.utilization_pct, f.projects_count, f.leakage_suspected, "
            "f.summary, l.name AS leader_name "
            "FROM fund_records f JOIN leaders l ON f.leader_id=l.id"
        )
        resp = self._api("funds", {"funds": records})
        self.log(f"  [Push] Funds: {resp.get('message','done')}")
        return resp

    def push_all(self):
        self.push_leaders()
        self.push_scores()
        self.push_cases()
        self.push_announcements()
        self.push_fund_records()
        self.log("  [✓] All data pushed to website.")

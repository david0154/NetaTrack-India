"""Auto-calculate leader performance scores from DB data using Gemini AI."""
from auto.base import AutoBase


class ScoreCalculator(AutoBase):
    def run(self):
        leaders = self.db.fetchall(
            "SELECT id, name FROM leaders WHERE status='active' ORDER BY id ASC LIMIT 200"
        )
        self.log(f"  Calculating scores for {len(leaders)} leaders...")
        for l in leaders:
            self._calc(l)
            self.sleep(0.8)
        # Update rank
        self.db.execute(
            "SET @rank=0; UPDATE leaders SET score_rank=(@rank:=@rank+1) "
            "ORDER BY total_score DESC"
        )
        self.log("  ✓ Score calculation complete. Rankings updated.")

    def _calc(self, leader: dict):
        lid = leader["id"]
        name = leader["name"]

        # Gather data from DB
        promises = self.db.fetchall(
            "SELECT status FROM promises WHERE leader_id=%s", (lid,))
        projects = self.db.fetchall(
            "SELECT status FROM projects WHERE leader_id=%s", (lid,))
        cases    = self.db.fetchall(
            "SELECT is_fake, status FROM criminal_cases WHERE leader_id=%s", (lid,))
        fund     = self.db.fetchone(
            "SELECT utilization_pct, leakage_suspected FROM fund_records WHERE leader_id=%s ORDER BY id DESC LIMIT 1", (lid,))
        reports  = self.db.fetchone(
            "SELECT COUNT(*) AS c FROM reports WHERE leader_id=%s AND status='approved'", (lid,))

        total_p    = len(promises)
        fulfilled_p = sum(1 for p in promises if p["status"] == "fulfilled")
        total_proj = len(projects)
        completed  = sum(1 for p in projects if p["status"] == "completed")
        real_cases = sum(1 for c in cases if not c["is_fake"] and c["status"] not in ("acquitted",))
        fund_pct   = (fund["utilization_pct"] or 50) if fund else 50
        leakage    = fund["leakage_suspected"] if fund else False
        report_ct  = (reports["c"] if reports else 0)

        # Rule-based scores
        score_promise = int((fulfilled_p / total_p * 100)) if total_p > 0 else 50
        score_project = int((completed / total_proj * 100)) if total_proj > 0 else 50
        score_fund    = max(0, int(fund_pct) - (20 if leakage else 0))
        score_criminal = max(0, 100 - (real_cases * 25))
        score_reports  = min(100, 50 + report_ct * 5)
        score_transp   = max(0, 100 - (real_cases * 15) - (20 if leakage else 0))

        # Gemini AI holistic score
        ai_score = 50
        if self.gemini_key:
            result = self.gemini_json(
                f"Indian politician: {name}\n"
                f"Promise fulfilment: {fulfilled_p}/{total_p}\n"
                f"Projects completed: {completed}/{total_proj}\n"
                f"Criminal/pending cases: {real_cases}\n"
                f"Fund utilization: {fund_pct}%\n"
                f"Fund leakage suspected: {leakage}\n"
                f"Public reports filed: {report_ct}\n\n"
                f"Calculate an overall performance score 0-100 for this Indian politician.\n"
                'Return JSON: {"score": <0-100>, "grade": "<A|B|C|D|F>", "summary": "<one sentence>"}'
            )
            ai_score = result.get("score", 50)

        total = int((
            score_promise * 0.20 + score_project * 0.15 +
            score_fund    * 0.15 + score_criminal * 0.25 +
            score_transp  * 0.10 + ai_score * 0.15
        ))
        total = max(0, min(100, total))

        self.db.execute(
            "UPDATE leaders SET "
            "score_promise_completion=%s, score_project_delivery=%s, "
            "score_transparency=%s, score_criminal_record=%s, "
            "score_fund_utilization=%s, total_score=%s "
            "WHERE id=%s",
            (score_promise, score_project, score_transp,
             score_criminal, score_fund, total, lid)
        )
        self.log(f"    {name}: {total}/100")

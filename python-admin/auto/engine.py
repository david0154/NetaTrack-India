"""NetaTrack Auto-Fetch Engine — orchestrates all auto tasks."""
import threading
import datetime
from auto.leader_fetcher import LeaderFetcher
from auto.promise_tracker import PromiseTracker
from auto.case_detector import CaseDetector
from auto.fund_tracker import FundTracker
from auto.score_calculator import ScoreCalculator
from auto.announcement_fetcher import AnnouncementFetcher
from auto.govt_data_fetcher import GovtDataFetcher
from auto.website_pusher import WebsitePusher
from auto.ai_router import AIRouter


class AutoEngine:
    def __init__(self, db, keys: dict, log_callback=None):
        """
        keys = {gemini, openai, openrouter, claude, sarvam}
        Any subset is fine — AIRouter tries all in priority order.
        """
        self.db = db
        self.ai = AIRouter(keys=keys)
        self.log = log_callback or print
        self._running = False
        self._thread = None

    def start(self, tasks: list):
        if self._running:
            self.log("[!] Engine already running."); return
        self._running = True
        self._thread = threading.Thread(target=self._run, args=(tasks,), daemon=True)
        self._thread.start()

    def stop(self):
        self._running = False

    def _run(self, tasks):
        self.log(f"[*] Auto Engine started — {datetime.datetime.now().strftime('%H:%M:%S')}")
        providers = self.ai.active_providers()
        self.log(f"[*] Active AI providers: {', '.join(providers) if providers else 'None (rule-based only)'}")
        try:
            if "govt_data" in tasks:
                self.log("\n[1/8] Fetching India govt data (ECI, Sansad, RS, MyNeta, RSS)...")
                GovtDataFetcher(self.db, self.ai, self.log).run()

            if "leaders" in tasks:
                self.log("\n[2/8] Enriching leader data (Wikipedia + Sansad API)...")
                LeaderFetcher(self.db, self.ai, self.log).run()

            if "announcements" in tasks:
                self.log("\n[3/8] Classifying announcements with AI...")
                AnnouncementFetcher(self.db, self.ai, self.log).run()

            if "promises" in tasks:
                self.log("\n[4/8] Tracking promise fulfillment...")
                PromiseTracker(self.db, self.ai, self.log).run()

            if "cases" in tasks:
                self.log("\n[5/8] Detecting criminal/fake cases...")
                CaseDetector(self.db, self.ai, self.log).run()

            if "funds" in tasks:
                self.log("\n[6/8] Tracking fund allocation & leakage...")
                FundTracker(self.db, self.ai, self.log).run()

            if "scores" in tasks:
                self.log("\n[7/8] Recalculating leader scores...")
                ScoreCalculator(self.db, self.ai, self.log).run()

            if "push" in tasks:
                self.log("\n[8/8] Pushing data to website...")
                WebsitePusher(self.db, self.log).push_all()

        except Exception as e:
            self.log(f"[✗] Engine error: {e}")
        finally:
            self._running = False
            self.log("\n[✓] Auto Engine finished.")

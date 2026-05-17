"""NetaTrack Auto-Fetch Engine — orchestrates all auto tasks."""
import threading
import datetime
from auto.leader_fetcher import LeaderFetcher
from auto.promise_tracker import PromiseTracker
from auto.case_detector import CaseDetector
from auto.fund_tracker import FundTracker
from auto.score_calculator import ScoreCalculator
from auto.announcement_fetcher import AnnouncementFetcher


class AutoEngine:
    def __init__(self, db, gemini_key: str, log_callback=None):
        self.db = db
        self.gemini_key = gemini_key
        self.log = log_callback or print
        self._running = False
        self._thread = None

    def start(self, tasks: list):
        """Start selected tasks in background thread."""
        if self._running:
            self.log("[!] Engine already running.")
            return
        self._running = True
        self._thread = threading.Thread(target=self._run, args=(tasks,), daemon=True)
        self._thread.start()

    def stop(self):
        self._running = False

    def _run(self, tasks):
        self.log(f"[*] Auto Engine started at {datetime.datetime.now().strftime('%H:%M:%S')}")
        try:
            if "leaders" in tasks:
                self.log("\n[1/6] Fetching leader data from internet...")
                LeaderFetcher(self.db, self.gemini_key, self.log).run()

            if "announcements" in tasks:
                self.log("\n[2/6] Collecting announcements...")
                AnnouncementFetcher(self.db, self.gemini_key, self.log).run()

            if "promises" in tasks:
                self.log("\n[3/6] Tracking promise fulfillment...")
                PromiseTracker(self.db, self.gemini_key, self.log).run()

            if "cases" in tasks:
                self.log("\n[4/6] Detecting criminal/fake cases...")
                CaseDetector(self.db, self.gemini_key, self.log).run()

            if "funds" in tasks:
                self.log("\n[5/6] Tracking fund allocation...")
                FundTracker(self.db, self.gemini_key, self.log).run()

            if "scores" in tasks:
                self.log("\n[6/6] Recalculating all leader scores...")
                ScoreCalculator(self.db, self.gemini_key, self.log).run()

        except Exception as e:
            self.log(f"[✗] Engine error: {e}")
        finally:
            self._running = False
            self.log("\n[✓] Auto Engine finished.")

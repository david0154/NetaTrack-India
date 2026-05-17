import tkinter as tk
from tkinter import ttk, messagebox
from db.connection import DBConnection
from ui.tabs.dashboard_tab import DashboardTab
from ui.tabs.leaders_tab import LeadersTab
from ui.tabs.reports_tab import ReportsTab
from ui.tabs.users_tab import UsersTab
from ui.tabs.settings_tab import SettingsTab
from ui.tabs.scraper_tab import ScraperTab


class MainWindow:
    def __init__(self, root: tk.Tk, db: DBConnection):
        self.root = root
        self.db = db
        self.root.title("NetaTrack India — Admin")
        self.root.configure(bg="#0f172a")
        self.root.deiconify()
        self._set_size(1280, 760)
        self.root.protocol("WM_DELETE_WINDOW", self._quit)
        self._build()

    def _set_size(self, w, h):
        sw = self.root.winfo_screenwidth()
        sh = self.root.winfo_screenheight()
        self.root.geometry(f"{w}x{h}+{(sw-w)//2}+{(sh-h)//2}")

    def _build(self):
        # Top bar
        topbar = tk.Frame(self.root, bg="#1e293b", height=50)
        topbar.pack(fill="x")
        topbar.pack_propagate(False)
        tk.Label(topbar, text="🇮🇳 NetaTrack India Admin", font=("Segoe UI", 13, "bold"),
                 bg="#1e293b", fg="#f8fafc", padx=16).pack(side="left", pady=12)
        tk.Button(topbar, text="🔄 Refresh", bg="#0f172a", fg="#94a3b8", relief="flat",
                  font=("Segoe UI", 9), cursor="hand2", command=self._refresh).pack(side="right", padx=8, pady=10)
        tk.Button(topbar, text="🚪 Disconnect", bg="#0f172a", fg="#f87171", relief="flat",
                  font=("Segoe UI", 9), cursor="hand2", command=self._quit).pack(side="right", pady=10)

        # Status bar
        self._statusbar = tk.Label(self.root, text="🟢 Connected", bg="#0f172a", fg="#22c55e",
                                   font=("Segoe UI", 8), anchor="w", padx=10)
        self._statusbar.pack(side="bottom", fill="x")

        # Notebook tabs
        self.nb = ttk.Notebook(self.root)
        self.nb.pack(fill="both", expand=True, padx=0, pady=0)

        self.tabs = [
            ("  📊 Dashboard  ",  DashboardTab),
            ("  👤 Leaders    ",  LeadersTab),
            ("  📋 Reports    ",  ReportsTab),
            ("  👥 Users      ",  UsersTab),
            ("  🤖 Scraper    ",  ScraperTab),
            ("  ⚙️ Settings   ",  SettingsTab),
        ]
        self.tab_instances = []
        for label, TabClass in self.tabs:
            frame = ttk.Frame(self.nb)
            self.nb.add(frame, text=label)
            instance = TabClass(frame, self.db, self._set_status)
            self.tab_instances.append(instance)

    def _set_status(self, msg, color="#94a3b8"):
        self._statusbar.config(text=msg, fg=color)

    def _refresh(self):
        idx = self.nb.index(self.nb.select())
        if hasattr(self.tab_instances[idx], "load"):
            self.tab_instances[idx].load()
        self._set_status("Refreshed.", "#22c55e")

    def _quit(self):
        self.db.close()
        self.root.quit()
        self.root.destroy()

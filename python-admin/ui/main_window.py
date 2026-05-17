"""NetaTrack India — Main Window with logo in topbar."""
import tkinter as tk
from tkinter import ttk
from db.connection import DBConnection
from ui.tabs.dashboard_tab import DashboardTab
from ui.tabs.leaders_tab import LeadersTab
from ui.tabs.reports_tab import ReportsTab
from ui.tabs.users_tab import UsersTab
from ui.tabs.settings_tab import SettingsTab
from ui.tabs.scraper_tab import ScraperTab
from ui.tabs.auto_tab import AutoTab


class MainWindow:
    def __init__(self, root: tk.Tk, db: DBConnection, logo_img=None):
        self.root = root
        self.db = db
        self.logo_img = logo_img
        self.root.title("NetaTrack India — Admin")
        self.root.configure(bg="#0f172a")
        self.root.deiconify()
        self._set_size(1320, 780)
        self.root.protocol("WM_DELETE_WINDOW", self._quit)
        self._build()

    def _set_size(self, w, h):
        sw = self.root.winfo_screenwidth()
        sh = self.root.winfo_screenheight()
        self.root.geometry(f"{w}x{h}+{(sw-w)//2}+{(sh-h)//2}")

    def _build(self):
        # Topbar
        topbar = tk.Frame(self.root, bg="#1e293b", height=52)
        topbar.pack(fill="x")
        topbar.pack_propagate(False)

        # Logo image in topbar
        if self.logo_img:
            tk.Label(topbar, image=self.logo_img, bg="#1e293b",
                     padx=10).pack(side="left", pady=8)

        tk.Label(topbar, text="NetaTrack India  —  Admin Panel",
                 font=("Segoe UI", 13, "bold"),
                 bg="#1e293b", fg="#f8fafc", padx=(4 if self.logo_img else 16)).pack(side="left", pady=14)

        tk.Label(topbar, text="🇮🇳",
                 font=("Segoe UI", 16), bg="#1e293b").pack(side="left", pady=14)

        tk.Button(topbar, text="🔄 Refresh", bg="#0f172a", fg="#94a3b8",
                  relief="flat", font=("Segoe UI", 9),
                  cursor="hand2", command=self._refresh).pack(side="right", padx=8, pady=10)
        tk.Button(topbar, text="🚪 Disconnect", bg="#0f172a", fg="#f87171",
                  relief="flat", font=("Segoe UI", 9),
                  cursor="hand2", command=self._quit).pack(side="right", pady=10)

        # Status bar
        self._statusbar = tk.Label(
            self.root, text="🟢 Connected",
            bg="#020617", fg="#22c55e",
            font=("Segoe UI", 8), anchor="w", padx=10
        )
        self._statusbar.pack(side="bottom", fill="x")

        # Notebook tabs
        style = ttk.Style()
        style.theme_use("clam")
        style.configure("TNotebook", background="#0f172a", borderwidth=0)
        style.configure("TNotebook.Tab", background="#1e293b", foreground="#94a3b8",
                        padding=[12, 6], font=("Segoe UI", 9))
        style.map("TNotebook.Tab",
                  background=[("selected", "#0f172a")],
                  foreground=[("selected", "#f8fafc")])

        self.nb = ttk.Notebook(self.root)
        self.nb.pack(fill="both", expand=True)

        tabs = [
            ("  📊 Dashboard  ",  DashboardTab),
            ("  🤖 Auto-Fetch  ",  AutoTab),
            ("  👤 Leaders    ",  LeadersTab),
            ("  📋 Reports    ",  ReportsTab),
            ("  👥 Users      ",  UsersTab),
            ("  📰 Scraper    ",  ScraperTab),
            ("  ⚙️ Settings   ",  SettingsTab),
        ]
        self.tab_instances = []
        for label, TabClass in tabs:
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

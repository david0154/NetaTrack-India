"""
NetaTrack India — Main Window
Builds the tabbed main application window.
"""
import tkinter as tk
from tkinter import ttk


class MainWindow:
    def __init__(self, root: tk.Tk, db, logo_img=None):
        self.root = root
        self.db   = db
        self.root.deiconify()
        self.root.title("NetaTrack India — Admin Panel")
        self.root.configure(bg="#0f172a")
        self.root.geometry("1100x700")
        self.root.minsize(900, 600)

        self._build_topbar(logo_img)
        self._build_tabs()
        self._status_bar()

    # ------------------------------------------------------------------ #
    def _build_topbar(self, logo_img):
        bar = tk.Frame(self.root, bg="#1e293b", height=48)
        bar.pack(fill="x")
        bar.pack_propagate(False)

        if logo_img:
            tk.Label(bar, image=logo_img,
                     bg="#1e293b").pack(side="left", padx=(12, 6))
        tk.Label(bar,
                 text="NetaTrack India — Admin Panel",
                 font=("Segoe UI", 12, "bold"),
                 bg="#1e293b", fg="#f8fafc").pack(side="left")

        self._status_var = tk.StringVar(value="Ready")
        tk.Label(bar, textvariable=self._status_var,
                 font=("Segoe UI", 9),
                 bg="#1e293b", fg="#64748b").pack(side="right", padx=16)

    # ------------------------------------------------------------------ #
    def _build_tabs(self):
        style = ttk.Style()
        style.theme_use("default")
        style.configure("TNotebook",
                        background="#0f172a", borderwidth=0)
        style.configure("TNotebook.Tab",
                        background="#1e293b", foreground="#94a3b8",
                        font=("Segoe UI", 9),
                        padding=[14, 6])
        style.map("TNotebook.Tab",
                  background=[("selected", "#0f172a")],
                  foreground=[("selected", "#f8fafc")])

        nb = ttk.Notebook(self.root)
        nb.pack(fill="both", expand=True)

        tabs = [
            ("\ud83d\udcca Dashboard",       self._tab_dashboard),
            ("\ud83e\udd16 Auto-Fetch",      self._tab_autofetch),
            ("\ud83d\udd04 Update Leaders",   self._tab_leaders_update),
            ("\ud83d\udc64 Leaders",          self._tab_leaders),
            ("\ud83d\udccb Reports",          self._tab_reports),
            ("\ud83d\udc65 Users",            self._tab_users),
            ("\u2699\ufe0f Settings",         self._tab_settings),
        ]

        for title, builder in tabs:
            frame = tk.Frame(nb, bg="#0f172a")
            nb.add(frame, text=title)
            try:
                builder(frame)
            except Exception as e:
                tk.Label(frame,
                         text=f"Error loading tab:\n{e}",
                         font=("Segoe UI", 10),
                         bg="#0f172a", fg="#ef4444",
                         justify="left").pack(padx=24, pady=24)

    # ------------------------------------------------------------------ #
    def _status_bar(self):
        bar = tk.Frame(self.root, bg="#020617", height=24)
        bar.pack(fill="x", side="bottom")
        bar.pack_propagate(False)
        tk.Label(bar, textvariable=self._status_var,
                 font=("Consolas", 8),
                 bg="#020617", fg="#475569").pack(side="left", padx=8)

    def set_status(self, msg: str, color: str = "#64748b"):
        self._status_var.set(msg)
        # update topbar label color dynamically
        for w in self.root.winfo_children():
            if isinstance(w, tk.Frame) and w.cget("bg") == "#1e293b":
                for lbl in w.winfo_children():
                    if isinstance(lbl, tk.Label) and \
                       lbl.cget("textvariable") == str(self._status_var):
                        lbl.config(fg=color)

    # ------------------------------------------------------------------ #
    #  Tab builders
    # ------------------------------------------------------------------ #

    def _tab_dashboard(self, frame):
        from ui.tabs.dashboard_tab import DashboardTab
        DashboardTab(frame, self.db, self.set_status)

    def _tab_autofetch(self, frame):
        try:
            from ui.tabs.auto_tab import AutoTab
            AutoTab(frame, self.db, self.set_status)
        except ImportError:
            self._placeholder(frame, "\ud83e\udd16 Auto-Fetch", "auto_tab.py")

    def _tab_leaders_update(self, frame):
        from ui.tabs.leaders_update_tab import LeadersUpdateTab
        LeadersUpdateTab(frame, self.db, self.set_status)

    def _tab_leaders(self, frame):
        try:
            from ui.tabs.leaders_tab import LeadersTab
            LeadersTab(frame, self.db, self.set_status)
        except ImportError:
            self._placeholder(frame, "\ud83d\udc64 Leaders", "leaders_tab.py")

    def _tab_reports(self, frame):
        try:
            from ui.tabs.reports_tab import ReportsTab
            ReportsTab(frame, self.db, self.set_status)
        except ImportError:
            self._placeholder(frame, "\ud83d\udccb Reports", "reports_tab.py")

    def _tab_users(self, frame):
        try:
            from ui.tabs.users_tab import UsersTab
            UsersTab(frame, self.db, self.set_status)
        except ImportError:
            self._placeholder(frame, "\ud83d\udc65 Users", "users_tab.py")

    def _tab_settings(self, frame):
        from ui.tabs.settings_tab import SettingsTab
        SettingsTab(frame, self.db, self.set_status)

    def _placeholder(self, frame, title, filename):
        tk.Label(frame,
                 text=f"{title}\n\nComing soon — add {filename} to ui/tabs/",
                 font=("Segoe UI", 11),
                 bg="#0f172a", fg="#475569",
                 justify="center").pack(expand=True)

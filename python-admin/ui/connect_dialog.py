"""
NetaTrack India — DB Connect Dialog
Shown on startup. Lets user enter MySQL credentials.
Auto-fills from config/settings.json or env vars if available.
"""
import tkinter as tk
from tkinter import messagebox
import os
import json

SETTINGS_FILE = os.path.join(os.path.dirname(__file__), '..', 'config', 'settings.json')


def _load_saved() -> dict:
    try:
        if os.path.exists(SETTINGS_FILE):
            with open(SETTINGS_FILE) as f:
                return json.load(f)
    except Exception:
        pass
    return {}


class ConnectDialog:
    def __init__(self, root: tk.Tk, db):
        self.db = db
        self.window = tk.Toplevel(root)
        self.window.title("NetaTrack India — Connect to Database")
        self.window.configure(bg="#0f172a")
        self.window.geometry("420x400")
        self.window.resizable(False, False)
        self.window.grab_set()
        self.window.protocol("WM_DELETE_WINDOW", self._cancel)

        # Centre on screen
        self.window.update_idletasks()
        x = (self.window.winfo_screenwidth()  - 420) // 2
        y = (self.window.winfo_screenheight() - 400) // 2
        self.window.geometry(f"+{x}+{y}")

        saved = _load_saved()

        # ---- Logo + title ----
        tk.Label(self.window,
                 text="🇮🇳  NetaTrack India",
                 font=("Segoe UI", 15, "bold"),
                 bg="#0f172a", fg="#f8fafc").pack(pady=(28, 2))
        tk.Label(self.window,
                 text="Connect to MySQL Database",
                 font=("Segoe UI", 9),
                 bg="#0f172a", fg="#64748b").pack(pady=(0, 20))

        # ---- Fields ----
        fields = [
            ("Host",     "db_host", "127.0.0.1", False),
            ("Port",     "db_port", "3306",      False),
            ("Database", "db_name", "netatrack", False),
            ("Username", "db_user", "root",      False),
            ("Password", "db_pass", "",          True),
        ]
        self._vars = {}
        form = tk.Frame(self.window, bg="#0f172a")
        form.pack(padx=36, fill="x")

        for label, key, default, secret in fields:
            env_map = {
                "db_host": "DB_HOST", "db_port": "DB_PORT",
                "db_name": "DB_NAME", "db_user": "DB_USER", "db_pass": "DB_PASS"
            }
            value = (os.getenv(env_map.get(key, ""))
                     or saved.get(key, default))

            row = tk.Frame(form, bg="#0f172a")
            row.pack(fill="x", pady=4)
            tk.Label(row, text=label, font=("Segoe UI", 9),
                     bg="#0f172a", fg="#94a3b8",
                     width=10, anchor="w").pack(side="left")

            var = tk.StringVar(value=value)
            self._vars[key] = var
            e = tk.Entry(row, textvariable=var,
                         font=("Segoe UI", 10),
                         bg="#1e293b", fg="#f8fafc",
                         insertbackground="white",
                         relief="flat",
                         show="*" if secret else "")
            e.pack(side="left", fill="x", expand=True, ipady=6)

        # ---- Status label ----
        self._status = tk.Label(self.window, text="",
                                font=("Segoe UI", 9),
                                bg="#0f172a", fg="#ef4444",
                                wraplength=360)
        self._status.pack(pady=(10, 0))

        # ---- Buttons ----
        btn_row = tk.Frame(self.window, bg="#0f172a")
        btn_row.pack(pady=16)

        tk.Button(btn_row,
                  text="  Connect  ",
                  font=("Segoe UI", 10, "bold"),
                  bg="#3b82f6", fg="#fff",
                  relief="flat", cursor="hand2",
                  padx=18, pady=8,
                  command=self._connect).pack(side="left", padx=8)

        tk.Button(btn_row,
                  text="  Cancel  ",
                  font=("Segoe UI", 10),
                  bg="#1e293b", fg="#94a3b8",
                  relief="flat", cursor="hand2",
                  padx=18, pady=8,
                  command=self._cancel).pack(side="left", padx=8)

        # Auto-connect if all env vars set
        if all(os.getenv(v) for v in ["DB_HOST","DB_NAME","DB_USER"]):
            self.window.after(200, self._connect)

        # Enter key connects
        self.window.bind("<Return>", lambda e: self._connect())

    def _connect(self):
        host = self._vars["db_host"].get().strip()
        port = int(self._vars["db_port"].get().strip() or 3306)
        name = self._vars["db_name"].get().strip()
        user = self._vars["db_user"].get().strip()
        pwd  = self._vars["db_pass"].get()

        self._status.config(text="Connecting...", fg="#f59e0b")
        self.window.update()

        try:
            self.db.connect(host, name, user, pwd, port)
            self.window.destroy()
        except Exception as e:
            self._status.config(
                text=f"Connection failed: {e}", fg="#ef4444"
            )

    def _cancel(self):
        self.db._connected = False
        self.window.destroy()

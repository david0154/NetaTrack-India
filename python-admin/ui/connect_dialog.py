"""
NetaTrack India — API Connect Dialog
Shown on startup. User enters Site URL + API Key (no MySQL needed).
Auto-fills from config/settings.json or .env file if available.
"""
import tkinter as tk
from tkinter import messagebox
import os
import json

SETTINGS_FILE = os.path.join(os.path.dirname(__file__), '..', 'config', 'settings.json')
ENV_FILE      = os.path.join(os.path.dirname(__file__), '..', '.env')


def _load_env_file() -> dict:
    """Read .env file key=value pairs into a dict."""
    result = {}
    try:
        if os.path.exists(ENV_FILE):
            with open(ENV_FILE) as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith('#') and '=' in line:
                        k, _, v = line.partition('=')
                        result[k.strip()] = v.strip()
    except Exception:
        pass
    return result


def _load_saved() -> dict:
    try:
        if os.path.exists(SETTINGS_FILE):
            with open(SETTINGS_FILE) as f:
                return json.load(f)
    except Exception:
        pass
    return {}


def _save_settings(data: dict):
    try:
        os.makedirs(os.path.dirname(SETTINGS_FILE), exist_ok=True)
        with open(SETTINGS_FILE, 'w') as f:
            json.dump(data, f, indent=2)
    except Exception:
        pass


class ConnectDialog:
    def __init__(self, root: tk.Tk, db):
        self.db = db
        self.window = tk.Toplevel(root)
        self.window.title("NetaTrack India — Connect")
        self.window.configure(bg="#0f172a")
        self.window.geometry("440x360")
        self.window.resizable(False, False)
        self.window.grab_set()
        self.window.protocol("WM_DELETE_WINDOW", self._cancel)

        self.window.update_idletasks()
        x = (self.window.winfo_screenwidth()  - 440) // 2
        y = (self.window.winfo_screenheight() - 360) // 2
        self.window.geometry(f"+{x}+{y}")

        saved   = _load_saved()
        env     = _load_env_file()

        # Priority: OS env > .env file > saved settings.json > default
        def get(env_key, saved_key, default=''):
            return (os.getenv(env_key)
                    or env.get(env_key)
                    or saved.get(saved_key, default))

        default_url = get('API_BASE_URL', 'api_base_url', 'https://yoursite.com')
        default_key = get('API_KEY',      'api_key',      '')

        # ---- Header ----
        tk.Label(self.window,
                 text="\U0001f1ee\U0001f1f3  NetaTrack India",
                 font=("Segoe UI", 15, "bold"),
                 bg="#0f172a", fg="#f8fafc").pack(pady=(32, 2))
        tk.Label(self.window,
                 text="Connect to your NetaTrack site via API",
                 font=("Segoe UI", 9),
                 bg="#0f172a", fg="#64748b").pack(pady=(0, 24))

        # ---- Fields ----
        form = tk.Frame(self.window, bg="#0f172a")
        form.pack(padx=36, fill="x")

        # Site URL
        tk.Label(form, text="Site URL",
                 font=("Segoe UI", 9), bg="#0f172a", fg="#94a3b8",
                 anchor="w").pack(fill="x")
        self._url_var = tk.StringVar(value=default_url)
        tk.Entry(form, textvariable=self._url_var,
                 font=("Segoe UI", 10),
                 bg="#1e293b", fg="#f8fafc",
                 insertbackground="white", relief="flat") \
            .pack(fill="x", ipady=7, pady=(2, 12))

        # API Key
        tk.Label(form, text="API Key  (Admin Panel \u2192 Settings \u2192 API Keys)",
                 font=("Segoe UI", 9), bg="#0f172a", fg="#94a3b8",
                 anchor="w").pack(fill="x")
        self._key_var = tk.StringVar(value=default_key)
        tk.Entry(form, textvariable=self._key_var,
                 font=("Segoe UI", 10),
                 bg="#1e293b", fg="#f8fafc",
                 insertbackground="white", relief="flat",
                 show="\u2022") \
            .pack(fill="x", ipady=7, pady=(2, 0))

        # Hint
        tk.Label(form,
                 text="No MySQL needed \u2014 works on any hosting",
                 font=("Segoe UI", 8), bg="#0f172a", fg="#334155",
                 anchor="w").pack(fill="x", pady=(4, 0))

        # ---- Status ----
        self._status = tk.Label(self.window, text="",
                                font=("Segoe UI", 9),
                                bg="#0f172a", fg="#ef4444",
                                wraplength=380)
        self._status.pack(pady=(12, 0))

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

        # Auto-connect if URL + key already in env/.env
        if default_url and default_url != 'https://yoursite.com' and default_key:
            self.window.after(200, self._connect)

        self.window.bind("<Return>", lambda e: self._connect())

    def _connect(self):
        url = self._url_var.get().strip().rstrip('/')
        key = self._key_var.get().strip()

        if not url or url == 'https://yoursite.com':
            self._status.config(text="Please enter your site URL.", fg="#ef4444")
            return
        if not key:
            self._status.config(text="Please enter your API Key.", fg="#ef4444")
            return

        self._status.config(text="Connecting...", fg="#f59e0b")
        self.window.update()

        try:
            self.db.connect(base_url=url, api_key=key)
            if not self.db.is_connected():
                self._status.config(
                    text="Could not reach the API. Check URL and API Key.",
                    fg="#ef4444"
                )
                return
            # Save for next launch
            _save_settings({'api_base_url': url, 'api_key': key})
            self.window.destroy()
        except Exception as e:
            self._status.config(text=f"Error: {e}", fg="#ef4444")

    def _cancel(self):
        self.db._connected = False
        self.window.destroy()

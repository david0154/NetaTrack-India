"""
NetaTrack India — Python Admin Settings Tab

Sections:
  1. Website Connection  (Site URL, Push API Token, test button)
  2. AI API Keys         (Gemini, OpenAI, OpenRouter, Claude, Sarvam)
  3. Database            (host, port, name, user, password)
  4. SMTP / Email        (host, port, user, pass, from)
  5. Auto-Fetch          (schedule, delay, max workers)

All settings saved to:
  - python-admin/config/settings.json  (primary)
  - python-admin/.env                  (for env-var access)
"""
import tkinter as tk
from tkinter import ttk, messagebox
import json
import os
import threading
import requests

SETTINGS_FILE = os.path.join(os.path.dirname(__file__), '..', '..', 'config', 'settings.json')
ENV_FILE      = os.path.join(os.path.dirname(__file__), '..', '..', '.env')


def load_settings() -> dict:
    if os.path.exists(SETTINGS_FILE):
        with open(SETTINGS_FILE, 'r') as f:
            return json.load(f)
    return {}


def save_settings(data: dict):
    os.makedirs(os.path.dirname(SETTINGS_FILE), exist_ok=True)
    with open(SETTINGS_FILE, 'w') as f:
        json.dump(data, f, indent=2)
    # Also write .env
    env_map = {
        'DB_HOST':         data.get('db_host', '127.0.0.1'),
        'DB_PORT':         data.get('db_port', '3306'),
        'DB_NAME':         data.get('db_name', 'netatrack'),
        'DB_USER':         data.get('db_user', 'root'),
        'DB_PASS':         data.get('db_pass', ''),
        'SITE_URL':        data.get('site_url', ''),
        'PUSH_API_TOKEN':  data.get('push_token', ''),
        'GEMINI_API_KEY':  data.get('gemini_key', ''),
        'OPENAI_API_KEY':  data.get('openai_key', ''),
        'OPENROUTER_KEY':  data.get('openrouter_key', ''),
        'CLAUDE_API_KEY':  data.get('claude_key', ''),
        'SARVAM_API_KEY':  data.get('sarvam_key', ''),
        'SMTP_HOST':       data.get('smtp_host', ''),
        'SMTP_PORT':       data.get('smtp_port', '587'),
        'SMTP_USER':       data.get('smtp_user', ''),
        'SMTP_PASS':       data.get('smtp_pass', ''),
    }
    with open(ENV_FILE, 'w') as f:
        for k, v in env_map.items():
            f.write(f'{k}={v}\n')


class SettingsTab:
    def __init__(self, parent: tk.Widget, db, set_status):
        self.db = db
        self.set_status = set_status
        self._data = load_settings()
        self._vars = {}   # key -> tk.StringVar
        self._build(parent)

    # ------------------------------------------------------------------ #
    #  Build UI
    # ------------------------------------------------------------------ #

    def _build(self, parent):
        # Scrollable canvas
        canvas = tk.Canvas(parent, bg="#0f172a", highlightthickness=0)
        sb = ttk.Scrollbar(parent, orient="vertical", command=canvas.yview)
        canvas.configure(yscrollcommand=sb.set)
        sb.pack(side="right", fill="y")
        canvas.pack(side="left", fill="both", expand=True)

        frame = tk.Frame(canvas, bg="#0f172a")
        win_id = canvas.create_window((0, 0), window=frame, anchor="nw")

        def _resize(e):
            canvas.configure(scrollregion=canvas.bbox("all"))
            canvas.itemconfig(win_id, width=canvas.winfo_width())
        frame.bind("<Configure>", _resize)
        canvas.bind("<Configure>", lambda e: canvas.itemconfig(win_id, width=e.width))
        # Mouse wheel scroll
        canvas.bind_all("<MouseWheel>", lambda e: canvas.yview_scroll(-1*(e.delta//120), "units"))

        pad = dict(padx=24, pady=6, fill="x")

        tk.Label(frame, text="⚙️  Settings",
                 font=("Segoe UI", 15, "bold"),
                 bg="#0f172a", fg="#f8fafc").pack(anchor="w", padx=24, pady=(20, 4))

        # ---- 1. Website Connection --------------------------------------
        self._section(frame, "🌐  Website Connection")

        self._field(frame, "site_url",    "Website URL",
                    placeholder="https://yoursite.com")
        self._field(frame, "push_token",  "Push API Token",
                    placeholder="Get from PHP Admin → Settings → Admin API Token",
                    show_btn=("Test Connection", self._test_connection))

        # ---- 2. AI API Keys --------------------------------------------
        self._section(frame, "🤖  AI API Keys")

        ai_fields = [
            ("gemini_key",     "Gemini API Key",
             "https://aistudio.google.com  —  Free tier available"),
            ("openai_key",     "OpenAI API Key",
             "https://platform.openai.com"),
            ("openrouter_key", "OpenRouter API Key",
             "https://openrouter.ai  —  100+ models, free tier"),
            ("claude_key",     "Claude API Key",
             "https://console.anthropic.com"),
            ("sarvam_key",     "Sarvam AI Key",
             "https://sarvam.ai  —  Best for Hindi/Indian languages"),
        ]
        for key, label, hint in ai_fields:
            self._field(frame, key, label, placeholder=hint, secret=True)

        ai_hint = tk.Label(
            frame,
            text="ℹ️  You need at least ONE AI key. App uses: Gemini → OpenAI → OpenRouter → Claude → Sarvam (in order)",
            font=("Segoe UI", 8), bg="#0f172a", fg="#f59e0b", wraplength=640, justify="left"
        )
        ai_hint.pack(anchor="w", **pad)

        # ---- 3. Database -----------------------------------------------
        self._section(frame, "🗄️  Database (MySQL)")

        row_db = tk.Frame(frame, bg="#0f172a")
        row_db.pack(fill="x", padx=24)
        self._inline_field(row_db, "db_host", "Host",     default="127.0.0.1", width=22)
        self._inline_field(row_db, "db_port", "Port",     default="3306",      width=8)
        self._inline_field(row_db, "db_name", "Database", default="netatrack", width=18)
        self._inline_field(row_db, "db_user", "Username", default="root",      width=16)
        self._inline_field(row_db, "db_pass", "Password", default="",          width=20, secret=True)

        # ---- 4. SMTP ---------------------------------------------------
        self._section(frame, "📧  SMTP / Email (optional)")

        row_smtp = tk.Frame(frame, bg="#0f172a")
        row_smtp.pack(fill="x", padx=24)
        self._inline_field(row_smtp, "smtp_host",  "SMTP Host", placeholder="smtp.gmail.com", width=26)
        self._inline_field(row_smtp, "smtp_port",  "Port",      default="587",               width=8)
        self._inline_field(row_smtp, "smtp_user",  "Email",     placeholder="you@gmail.com", width=26)
        self._inline_field(row_smtp, "smtp_pass",  "Password",  default="",                  width=20, secret=True)
        self._field(frame, "smtp_from", "From Name",
                    placeholder="NetaTrack India")

        # ---- 5. Auto-Fetch Settings ------------------------------------
        self._section(frame, "⏰  Auto-Fetch Settings")

        row_af = tk.Frame(frame, bg="#0f172a")
        row_af.pack(fill="x", padx=24)
        self._inline_field(row_af, "fetch_delay",   "Delay between requests (sec)", default="1.0",  width=8)
        self._inline_field(row_af, "fetch_workers", "Max parallel workers",         default="2",    width=6)
        self._inline_field(row_af, "wiki_limit",    "Wikipedia enrich limit",       default="200",  width=8)

        self._field(frame, "push_after_fetch",
                    "Auto-push to website after engine run? (yes/no)",
                    placeholder="no")

        # ---- Save button -----------------------------------------------
        btn_row = tk.Frame(frame, bg="#0f172a")
        btn_row.pack(fill="x", padx=24, pady=(20, 32))

        tk.Button(
            btn_row,
            text="💾  Save All Settings",
            font=("Segoe UI", 11, "bold"),
            bg="#22c55e", fg="#000",
            relief="flat", cursor="hand2",
            padx=24, pady=10,
            command=self._save
        ).pack(side="left", padx=(0, 12))

        tk.Button(
            btn_row,
            text="📂  Open Settings File",
            font=("Segoe UI", 9),
            bg="#1e293b", fg="#94a3b8",
            relief="flat", cursor="hand2",
            padx=16, pady=10,
            command=self._open_file
        ).pack(side="left", padx=(0, 12))

        tk.Button(
            btn_row,
            text="🔄  Reload",
            font=("Segoe UI", 9),
            bg="#1e293b", fg="#94a3b8",
            relief="flat", cursor="hand2",
            padx=16, pady=10,
            command=self._reload
        ).pack(side="left")

        self._save_label = tk.Label(
            btn_row, text="", font=("Segoe UI", 9),
            bg="#0f172a", fg="#22c55e"
        )
        self._save_label.pack(side="left", padx=16)

    # ------------------------------------------------------------------ #
    #  Field builders
    # ------------------------------------------------------------------ #

    def _section(self, parent, title: str):
        tk.Frame(parent, bg="#1e293b", height=1).pack(
            fill="x", padx=24, pady=(16, 8)
        )
        tk.Label(parent, text=title,
                 font=("Segoe UI", 10, "bold"),
                 bg="#0f172a", fg="#60a5fa").pack(
            anchor="w", padx=24, pady=(0, 4)
        )

    def _field(self, parent, key: str, label: str,
               placeholder: str = "", secret: bool = False,
               show_btn=None, default: str = ""):
        row = tk.Frame(parent, bg="#0f172a")
        row.pack(fill="x", padx=24, pady=4)

        tk.Label(row, text=label, font=("Segoe UI", 9),
                 bg="#0f172a", fg="#94a3b8", width=28,
                 anchor="w").pack(side="left")

        var = tk.StringVar(value=self._data.get(key, default))
        self._vars[key] = var

        show = "*" if secret else ""
        entry = tk.Entry(
            row, textvariable=var,
            font=("Segoe UI", 10),
            bg="#1e293b", fg="#f8fafc",
            insertbackground="#f8fafc",
            relief="flat", show=show,
            width=52
        )
        entry.pack(side="left", ipady=5, padx=(0, 8))

        if placeholder and not var.get():
            entry.insert(0, placeholder)
            entry.config(fg="#475569")
            entry.bind("<FocusIn>",  lambda e, w=entry, p=placeholder: (
                (w.delete(0, "end"), w.config(fg="#f8fafc"))
                if w.get() == p else None
            ))
            entry.bind("<FocusOut>", lambda e, w=entry, p=placeholder: (
                (w.insert(0, p), w.config(fg="#475569"))
                if not w.get() else None
            ))

        if secret:
            show_var = tk.BooleanVar(value=False)
            def _toggle(e=entry, s=show_var):
                s.set(not s.get())
                e.config(show="" if s.get() else "*")
            tk.Button(
                row, text="👁️", font=("Segoe UI", 9),
                bg="#1e293b", fg="#64748b",
                relief="flat", cursor="hand2",
                command=_toggle
            ).pack(side="left", padx=(0, 8))

        if show_btn:
            btn_label, btn_cmd = show_btn
            tk.Button(
                row, text=btn_label,
                font=("Segoe UI", 9),
                bg="#3b82f6", fg="#fff",
                relief="flat", cursor="hand2",
                padx=10, pady=4,
                command=btn_cmd
            ).pack(side="left")

    def _inline_field(self, parent, key: str, label: str,
                      placeholder: str = "", default: str = "",
                      width: int = 18, secret: bool = False):
        col = tk.Frame(parent, bg="#0f172a")
        col.pack(side="left", padx=(0, 20), pady=4)

        tk.Label(col, text=label, font=("Segoe UI", 8),
                 bg="#0f172a", fg="#94a3b8").pack(anchor="w")

        var = tk.StringVar(value=self._data.get(key, default))
        self._vars[key] = var

        show = "*" if secret else ""
        entry = tk.Entry(
            col, textvariable=var,
            font=("Segoe UI", 10),
            bg="#1e293b", fg="#f8fafc",
            insertbackground="#f8fafc",
            relief="flat", show=show, width=width
        )
        entry.pack(ipady=5)
        if placeholder and not var.get():
            entry.insert(0, placeholder)
            entry.config(fg="#475569")

    # ------------------------------------------------------------------ #
    #  Actions
    # ------------------------------------------------------------------ #

    def _collect(self) -> dict:
        """Collect all field values into a dict."""
        return {k: v.get().strip() for k, v in self._vars.items()}

    def _save(self):
        data = self._collect()
        save_settings(data)
        self._data = data
        self._save_label.config(text="✓ Saved!", fg="#22c55e")
        self.set_status("✓ Settings saved", "#22c55e")
        self._save_label.after(3000, lambda: self._save_label.config(text=""))

    def _reload(self):
        self._data = load_settings()
        for k, var in self._vars.items():
            var.set(self._data.get(k, ""))
        self._save_label.config(text="↻ Reloaded", fg="#60a5fa")
        self._save_label.after(2000, lambda: self._save_label.config(text=""))

    def _open_file(self):
        import subprocess, sys
        path = os.path.abspath(SETTINGS_FILE)
        try:
            if sys.platform == "win32":
                os.startfile(path)
            elif sys.platform == "darwin":
                subprocess.Popen(["open", path])
            else:
                subprocess.Popen(["xdg-open", path])
        except Exception as e:
            messagebox.showerror("Error", str(e))

    def _test_connection(self):
        """Test website push API connection."""
        data = self._collect()
        url   = data.get("site_url", "").rstrip("/")
        token = data.get("push_token", "")

        if not url:
            messagebox.showwarning("Missing URL", "Enter the Website URL first.")
            return
        if not token:
            messagebox.showwarning("Missing Token",
                "Enter the Push API Token.\n\n"
                "Get it from: PHP Admin → Settings → Admin API Token")
            return

        def _ping():
            try:
                r = requests.get(
                    f"{url}/api/v1/ping",
                    headers={"Authorization": f"Bearer {token}"},
                    timeout=8
                )
                if r.status_code == 200:
                    messagebox.showinfo(
                        "✓ Connection OK",
                        f"Connected to:\n{url}\n\nResponse: {r.text[:200]}"
                    )
                elif r.status_code == 401:
                    messagebox.showerror(
                        "401 Unauthorized",
                        "Wrong API token.\nCheck PHP Admin → Settings → Admin API Token."
                    )
                else:
                    messagebox.showerror(
                        f"HTTP {r.status_code}",
                        f"Unexpected response from {url}\n{r.text[:300]}"
                    )
            except requests.exceptions.ConnectionError:
                messagebox.showerror(
                    "Connection Failed",
                    f"Cannot reach:\n{url}\n\nCheck the URL and internet connection."
                )
            except Exception as e:
                messagebox.showerror("Error", str(e))

        threading.Thread(target=_ping, daemon=True).start()

    # ---- Public getters (used by other tabs) ----------------------------- #

    def get(self, key: str, default="") -> str:
        return self._vars.get(key, tk.StringVar(value=default)).get().strip() or default

    def get_all(self) -> dict:
        return self._collect()

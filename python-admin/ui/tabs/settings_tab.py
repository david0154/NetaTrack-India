"""Admin Settings Tab — all keys including multi-AI + push API token."""
import tkinter as tk
from tkinter import ttk, messagebox


class SettingsTab:
    def __init__(self, parent, db, set_status):
        self.parent = parent
        self.db = db
        self.set_status = set_status
        self._vars = {}
        self._build()
        self.load()

    def _build(self):
        canvas = tk.Canvas(self.parent, bg="#0f172a", highlightthickness=0)
        vsb = ttk.Scrollbar(self.parent, orient="vertical", command=canvas.yview)
        canvas.configure(yscrollcommand=vsb.set)
        vsb.pack(side="right", fill="y")
        canvas.pack(side="left", fill="both", expand=True)
        inner = tk.Frame(canvas, bg="#0f172a", padx=20, pady=16)
        win_id = canvas.create_window((0, 0), window=inner, anchor="nw")
        inner.bind("<Configure>", lambda e: canvas.configure(scrollregion=canvas.bbox("all")))
        canvas.bind("<Configure>", lambda e: canvas.itemconfig(win_id, width=e.width))
        self.inner = inner

    def _section(self, title, color="#3b82f6"):
        tk.Label(self.inner, text=title, font=("Segoe UI", 11, "bold"),
                 bg="#0f172a", fg=color).pack(anchor="w", pady=(18, 5))
        tk.Frame(self.inner, bg="#1e293b", height=1).pack(fill="x", pady=(0, 8))

    def _field(self, label, key, secret=False, hint=""):
        row = tk.Frame(self.inner, bg="#0f172a")
        row.pack(fill="x", pady=2)
        tk.Label(row, text=label, bg="#0f172a", fg="#94a3b8",
                 font=("Segoe UI", 9), width=22, anchor="w").pack(side="left")
        var = tk.StringVar()
        self._vars[key] = var
        e = ttk.Entry(row, textvariable=var, show="*" if secret else "",
                      font=("Segoe UI", 10))
        e.pack(side="left", fill="x", expand=True)
        if hint:
            tk.Label(row, text=hint, bg="#0f172a", fg="#334155",
                     font=("Segoe UI", 8)).pack(side="left", padx=6)

    def load(self):
        for w in self.inner.winfo_children():
            w.destroy()
        self._vars.clear()
        try:
            rows = self.db.fetchall("SELECT `key`, `value` FROM settings")
            data = {r["key"]: r["value"] for r in rows}

            self._section("🌐 General")
            for lbl, key in [("Site Name","site_name"),("Tagline","site_tagline"),
                              ("Site URL","site_url"),("Logo URL","site_logo"),
                              ("Meta Description","meta_description")]:
                self._field(lbl, key)
                self._vars[key].set(data.get(key, ""))

            self._section("🤖 AI APIs — add any, engine auto-selects", "#f97316")
            tk.Label(self.inner,
                     text="Add any API keys you have. Engine tries Gemini → OpenAI → OpenRouter → Claude → Sarvam in order.",
                     bg="#0f172a", fg="#64748b", font=("Segoe UI", 9), wraplength=600).pack(anchor="w", pady=(0,8))
            for lbl, key, hint in [
                ("Gemini API Key",      "gemini_api_key",     "AIza..."),
                ("OpenAI API Key",      "openai_api_key",     "sk-..."),
                ("OpenRouter API Key",  "openrouter_api_key", "sk-or-..."),
                ("Claude API Key",      "claude_api_key",     "sk-ant-..."),
                ("Sarvam AI Key",       "sarvam_api_key",     "sarvam-..."),
            ]:
                self._field(lbl, key, secret=True, hint=hint)
                self._vars[key].set(data.get(key, ""))

            self._section("🌐 Push API (Python → Website)", "#22c55e")
            tk.Label(self.inner,
                     text="The Python admin app uses these to push data directly to your live site.",
                     bg="#0f172a", fg="#64748b", font=("Segoe UI", 9)).pack(anchor="w", pady=(0,8))
            for lbl, key in [("Site URL","site_url"),("Admin API Token","admin_api_token")]:
                self._field(lbl, key, secret=(key=="admin_api_token"), hint="auto-generated on install" if key=="admin_api_token" else "")
                self._vars[key].set(data.get(key, ""))

            self._section("📊 Analytics & Ads")
            for lbl, key in [("Google Analytics ID","google_analytics_id"),("Google AdSense Code","google_adsense_code")]:
                self._field(lbl, key)
                self._vars[key].set(data.get(key, ""))

            self._section("📣 Sponsor & Announcement")
            for lbl, key in [("Sponsor Title","sponsor_title"),("Sponsor HTML","sponsor_html"),
                              ("Announcement Text","announcement_text"),("Announcement Link","announcement_link")]:
                self._field(lbl, key)
                self._vars[key].set(data.get(key, ""))

            self._section("📧 SMTP Email")
            for lbl, key in [("SMTP Host","smtp_host"),("SMTP Port","smtp_port"),
                              ("SMTP User","smtp_user"),("SMTP Pass","smtp_pass")]:
                self._field(lbl, key, secret=(key=="smtp_pass"))
                self._vars[key].set(data.get(key, ""))

            tk.Button(self.inner, text="💾  Save All Settings",
                      bg="#3b82f6", fg="white", relief="flat",
                      font=("Segoe UI", 11, "bold"), cursor="hand2",
                      command=self._save, padx=20, pady=10).pack(pady=(20, 8), anchor="w")
            self.set_status("⚙️ Settings loaded", "#94a3b8")
        except Exception as e:
            self.set_status(f"❌ {e}", "#f87171")

    def _save(self):
        try:
            for key, var in self._vars.items():
                self.db.execute(
                    "INSERT INTO settings (`key`,`value`) VALUES (%s,%s) ON DUPLICATE KEY UPDATE `value`=%s",
                    (key, var.get(), var.get())
                )
            self.set_status("✅ Settings saved!", "#22c55e")
            messagebox.showinfo("Saved", "All settings saved.")
        except Exception as e:
            messagebox.showerror("Error", str(e))

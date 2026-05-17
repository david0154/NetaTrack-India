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

    def _section(self, title):
        tk.Label(self.inner, text=title, font=("Segoe UI", 11, "bold"),
                 bg="#0f172a", fg="#3b82f6").pack(anchor="w", pady=(16, 6))
        sep = tk.Frame(self.inner, bg="#1e293b", height=1)
        sep.pack(fill="x", pady=(0, 8))

    def _field(self, label, key, secret=False, wide=False):
        tk.Label(self.inner, text=label, bg="#0f172a", fg="#94a3b8",
                 font=("Segoe UI", 9)).pack(anchor="w", pady=(4, 1))
        var = tk.StringVar()
        self._vars[key] = var
        ttk.Entry(self.inner, textvariable=var, show="*" if secret else "",
                  font=("Segoe UI", 10), width=70 if wide else 40).pack(anchor="w", fill="x")

    def load(self):
        for w in self.inner.winfo_children():
            w.destroy()
        self._vars.clear()
        try:
            rows = self.db.fetchall("SELECT `key`, `value` FROM settings")
            data = {r["key"]: r["value"] for r in rows}

            self._section("🌐 General")
            for label, key in [("Site Name","site_name"),("Tagline","site_tagline"),("Site URL","site_url"),("Logo URL","site_logo"),("Meta Description","meta_description")]:
                self._field(label, key, wide=True)
                self._vars[key].set(data.get(key, ""))

            self._section("🤖 AI Keys")
            for label, key in [("Gemini API Key","gemini_api_key"),("Sarvam AI Key","sarvam_api_key")]:
                self._field(label, key, secret=True)
                self._vars[key].set(data.get(key, ""))

            self._section("📊 Analytics & Ads")
            for label, key in [("Google Analytics ID","google_analytics_id"),("Google AdSense Code","google_adsense_code")]:
                self._field(label, key, wide=True)
                self._vars[key].set(data.get(key, ""))

            self._section("📣 Sponsor & Announcement")
            for label, key in [("Sponsor Title","sponsor_title"),("Sponsor HTML","sponsor_html"),("Announcement Text","announcement_text"),("Announcement Link","announcement_link")]:
                self._field(label, key, wide=True)
                self._vars[key].set(data.get(key, ""))

            self._section("📧 SMTP")
            for label, key in [("SMTP Host","smtp_host"),("SMTP Port","smtp_port"),("SMTP User","smtp_user"),("SMTP Password","smtp_pass")]:
                self._field(label, key, secret=(key=="smtp_pass"))
                self._vars[key].set(data.get(key, ""))

            tk.Button(self.inner, text="💾 Save All Settings", bg="#3b82f6", fg="white",
                      relief="flat", font=("Segoe UI", 11, "bold"), cursor="hand2",
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
            messagebox.showinfo("Saved", "All settings saved successfully.")
        except Exception as e:
            messagebox.showerror("Error", str(e))

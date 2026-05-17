"""Auto-Fetch Engine UI Tab — with Multi-AI keys + Push to Website."""
import tkinter as tk
from tkinter import ttk, messagebox


class AutoTab:
    def __init__(self, parent, db, set_status):
        self.parent = parent
        self.db = db
        self.set_status = set_status
        self._engine = None
        self._build()

    def _build(self):
        # Scrollable container
        canvas = tk.Canvas(self.parent, bg="#0f172a", highlightthickness=0)
        vsb = ttk.Scrollbar(self.parent, orient="vertical", command=canvas.yview)
        canvas.configure(yscrollcommand=vsb.set)
        vsb.pack(side="right", fill="y")
        canvas.pack(side="left", fill="both", expand=True)
        outer = tk.Frame(canvas, bg="#0f172a")
        win = canvas.create_window((0, 0), window=outer, anchor="nw")
        outer.bind("<Configure>", lambda e: canvas.configure(scrollregion=canvas.bbox("all")))
        canvas.bind("<Configure>", lambda e: canvas.itemconfig(win, width=e.width))

        # Header
        tk.Label(outer, text="🤖 Auto-Fetch Engine",
                 font=("Segoe UI", 13, "bold"), bg="#0f172a", fg="#f8fafc").pack(anchor="w", padx=12, pady=(12,4))
        tk.Label(outer, text="Collect data from internet, govt APIs and RSS. Push results directly to your live website.",
                 font=("Segoe UI", 9), bg="#0f172a", fg="#64748b").pack(anchor="w", padx=12, pady=(0,8))

        # ── AI API Keys Section ────────────────────────────────────────────────────────────
        ai_frame = tk.LabelFrame(outer, text=" 🤖 AI API Keys (add any — engine auto-selects first working) ",
                                 bg="#1e293b", fg="#3b82f6",
                                 font=("Segoe UI", 9, "bold"), relief="flat")
        ai_frame.pack(fill="x", padx=12, pady=4)

        self._ai_vars = {}
        ai_providers = [
            ("gemini",     "Gemini API Key",      "AIza..."),
            ("openai",     "OpenAI API Key",       "sk-..."),
            ("openrouter", "OpenRouter API Key",   "sk-or-..."),
            ("claude",     "Claude (Anthropic) Key", "sk-ant-..."),
            ("sarvam",     "Sarvam AI Key",        "sarvam-..."),
        ]
        grid = tk.Frame(ai_frame, bg="#1e293b")
        grid.pack(fill="x", padx=12, pady=8)
        for i, (key, label, ph) in enumerate(ai_providers):
            col = i % 2
            row = i // 2
            cell = tk.Frame(grid, bg="#1e293b")
            cell.grid(row=row, column=col, padx=8, pady=4, sticky="ew")
            grid.columnconfigure(col, weight=1)
            tk.Label(cell, text=label, bg="#1e293b", fg="#94a3b8",
                     font=("Segoe UI", 9)).pack(anchor="w")
            var = tk.StringVar()
            self._ai_vars[key] = var
            ttk.Entry(cell, textvariable=var, show="*", font=("Segoe UI", 9)).pack(fill="x")

        load_btn = tk.Button(ai_frame, text="⬇ Load All Keys from Settings",
                             bg="#0f172a", fg="#64748b", relief="flat", cursor="hand2",
                             font=("Segoe UI", 9), command=self._load_keys)
        load_btn.pack(anchor="w", padx=12, pady=(0, 8))

        # ── Website Push Section ────────────────────────────────────────────────────────────
        push_frame = tk.LabelFrame(outer, text=" 🌐 Push to Website ",
                                   bg="#1e293b", fg="#22c55e",
                                   font=("Segoe UI", 9, "bold"), relief="flat")
        push_frame.pack(fill="x", padx=12, pady=4)
        prow = tk.Frame(push_frame, bg="#1e293b")
        prow.pack(fill="x", padx=12, pady=8)
        tk.Label(prow, text="Site URL:", bg="#1e293b", fg="#94a3b8",
                 font=("Segoe UI", 9), width=12, anchor="w").grid(row=0, column=0, sticky="w")
        self._site_url_var = tk.StringVar()
        ttk.Entry(prow, textvariable=self._site_url_var, width=40).grid(row=0, column=1, sticky="ew", padx=4)
        tk.Label(prow, text="API Token:", bg="#1e293b", fg="#94a3b8",
                 font=("Segoe UI", 9), width=12, anchor="w").grid(row=1, column=0, sticky="w", pady=(4,0))
        self._token_var = tk.StringVar()
        ttk.Entry(prow, textvariable=self._token_var, show="*", width=40).grid(row=1, column=1, sticky="ew", padx=4)
        prow.columnconfigure(1, weight=1)
        tk.Button(push_frame, text="⬇ Load from Settings", bg="#0f172a", fg="#64748b",
                  relief="flat", cursor="hand2", font=("Segoe UI", 9),
                  command=self._load_push_config).pack(anchor="w", padx=12)
        tk.Button(push_frame, text="🚀 Push to Website Now", bg="#22c55e", fg="white",
                  relief="flat", font=("Segoe UI", 10, "bold"), cursor="hand2",
                  command=self._push_now, padx=14, pady=8).pack(anchor="w", padx=12, pady=8)

        # ── Task Checkboxes ───────────────────────────────────────────────────────────────
        tasks_frame = tk.LabelFrame(outer, text=" ☑ Tasks ",
                                    bg="#1e293b", fg="#94a3b8",
                                    font=("Segoe UI", 9, "bold"), relief="flat")
        tasks_frame.pack(fill="x", padx=12, pady=4)
        self._task_vars = {}
        tasks = [
            ("govt_data",     "🏕️  India Govt Data (ECI, Sansad, Rajya Sabha, MyNeta, RSS)"),
            ("leaders",       "👤 Enrich Leaders (Wikipedia + Sansad API)"),
            ("announcements", "📢 Classify Announcements (News RSS + Govt feeds)"),
            ("promises",      "📜 Track Promise Fulfillment"),
            ("cases",         "⚖️  Detect Criminal / Fake Cases"),
            ("funds",         "💰 Track MPLADS Fund & Leakage"),
            ("scores",        "📊 Recalculate All Leader Scores (AI)"),
            ("push",          "🌐 Push to Website after Engine finishes"),
        ]
        tgrid = tk.Frame(tasks_frame, bg="#1e293b")
        tgrid.pack(fill="x", padx=12, pady=8)
        for i, (key, label) in enumerate(tasks):
            var = tk.BooleanVar(value=(key != "push"))
            self._task_vars[key] = var
            tk.Checkbutton(tgrid, text=label, variable=var,
                           bg="#1e293b", fg="#e2e8f0", selectcolor="#0f172a",
                           activebackground="#1e293b", font=("Segoe UI", 10),
                           cursor="hand2").grid(row=i // 2, column=i % 2, sticky="w", padx=16, pady=4)

        # Run/Stop
        btn_frame = tk.Frame(outer, bg="#0f172a")
        btn_frame.pack(fill="x", padx=12, pady=6)
        self._run_btn = tk.Button(btn_frame, text="▶  Run Auto-Fetch",
                                  bg="#3b82f6", fg="white", relief="flat",
                                  font=("Segoe UI", 11, "bold"), cursor="hand2",
                                  command=self._run, padx=20, pady=10)
        self._run_btn.pack(side="left")
        self._stop_btn = tk.Button(btn_frame, text="⏹ Stop",
                                   bg="#ef4444", fg="white", relief="flat",
                                   font=("Segoe UI", 10), cursor="hand2",
                                   command=self._stop, padx=14, pady=10, state="disabled")
        self._stop_btn.pack(side="left", padx=8)
        self._progress = ttk.Progressbar(btn_frame, mode="indeterminate", length=300)
        self._progress.pack(side="left", padx=16)

        # Log
        log_lf = tk.LabelFrame(outer, text=" Live Log ",
                                bg="#020617", fg="#64748b",
                                font=("Segoe UI", 9, "bold"), relief="flat")
        log_lf.pack(fill="both", expand=True, padx=12, pady=(4, 12))
        self._log = tk.Text(log_lf, bg="#020617", fg="#86efac", height=18,
                            font=("Cascadia Code", 9), state="disabled", wrap="word")
        lvsb = ttk.Scrollbar(log_lf, orient="vertical", command=self._log.yview)
        self._log.configure(yscrollcommand=lvsb.set)
        self._log.pack(side="left", fill="both", expand=True)
        lvsb.pack(side="right", fill="y")
        self._log.tag_configure("ok",   foreground="#86efac")
        self._log.tag_configure("err",  foreground="#f87171")
        self._log.tag_configure("warn", foreground="#fde68a")
        self._log.tag_configure("info", foreground="#93c5fd")

    def _append_log(self, text: str):
        self._log.configure(state="normal")
        tag = "ok"
        tl = text.lower()
        if "✗" in text or "error" in tl or "fail" in tl: tag = "err"
        elif "[!]" in text or "leakage" in tl or "warn" in tl: tag = "warn"
        elif "[*]" in text or "[1/" in text or "[2/" in text: tag = "info"
        self._log.insert("end", text + "\n", tag)
        self._log.see("end")
        self._log.configure(state="disabled")

    def _load_keys(self):
        mapping = {
            "gemini_api_key":     "gemini",
            "openai_api_key":     "openai",
            "openrouter_api_key": "openrouter",
            "claude_api_key":     "claude",
            "sarvam_api_key":     "sarvam",
        }
        loaded = []
        for db_key, var_key in mapping.items():
            try:
                row = self.db.fetchone(f"SELECT `value` FROM settings WHERE `key`='{db_key}'")
                if row and row["value"]:
                    self._ai_vars[var_key].set(row["value"])
                    loaded.append(var_key)
            except Exception:
                pass
        self._append_log(f"[*] Loaded keys: {', '.join(loaded) if loaded else 'none found'}")

    def _load_push_config(self):
        for key, var in [("site_url", self._site_url_var), ("admin_api_token", self._token_var)]:
            try:
                row = self.db.fetchone(f"SELECT `value` FROM settings WHERE `key`='{key}'")
                if row and row["value"]:
                    var.set(row["value"])
            except Exception:
                pass
        self._append_log("[*] Push config loaded.")

    def _run(self):
        tasks = [k for k, v in self._task_vars.items() if v.get()]
        if not tasks:
            messagebox.showwarning("No Tasks", "Select at least one task."); return
        keys = {k: v.get().strip() for k, v in self._ai_vars.items() if v.get().strip()}
        self._log.configure(state="normal")
        self._log.delete("1.0", "end")
        self._log.configure(state="disabled")
        from auto.engine import AutoEngine
        self._engine = AutoEngine(
            self.db, keys,
            log_callback=lambda t: self.parent.after(0, lambda msg=t: self._append_log(msg))
        )
        self._run_btn.config(state="disabled")
        self._stop_btn.config(state="normal")
        self._progress.start(12)
        self.set_status("🤖 Auto Engine running...", "#f97316")
        self._engine.start(tasks)
        self._poll_done()

    def _push_now(self):
        site_url = self._site_url_var.get().strip()
        token    = self._token_var.get().strip()
        if not site_url or not token:
            messagebox.showwarning("Missing", "Enter Site URL and API Token first."); return
        # Save to settings
        for k, v in [("site_url", site_url), ("admin_api_token", token)]:
            self.db.execute(
                "INSERT INTO settings (`key`,`value`) VALUES (%s,%s) ON DUPLICATE KEY UPDATE `value`=%s",
                (k, v, v)
            )
        self._append_log("[*] Pushing data to website...")
        import threading
        def _do():
            from auto.website_pusher import WebsitePusher
            p = WebsitePusher(self.db, lambda t: self.parent.after(0, lambda msg=t: self._append_log(msg)))
            p.push_all()
        threading.Thread(target=_do, daemon=True).start()

    def _poll_done(self):
        if self._engine and not self._engine._running:
            self._done()
        else:
            self.parent.after(500, self._poll_done)

    def _stop(self):
        if self._engine: self._engine.stop()
        self._done(); self._append_log("[!] Stopped by user.")

    def _done(self):
        self._progress.stop()
        self._run_btn.config(state="normal")
        self._stop_btn.config(state="disabled")
        self.set_status("✅ Done", "#22c55e")

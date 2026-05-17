"""Auto-Fetch Engine UI tab."""
import tkinter as tk
from tkinter import ttk, messagebox
import threading


class AutoTab:
    def __init__(self, parent, db, set_status):
        self.parent = parent
        self.db = db
        self.set_status = set_status
        self._engine = None
        self._build()

    def _build(self):
        # Top bar
        header = tk.Frame(self.parent, bg="#0f172a")
        header.pack(fill="x", padx=12, pady=(12, 6))
        tk.Label(header, text="🤖 Auto-Fetch Engine",
                 font=("Segoe UI", 13, "bold"), bg="#0f172a", fg="#f8fafc").pack(side="left")

        # Task checkboxes
        tasks_frame = tk.LabelFrame(self.parent, text=" Select Tasks to Run ",
                                    bg="#1e293b", fg="#94a3b8",
                                    font=("Segoe UI", 9, "bold"), relief="flat")
        tasks_frame.pack(fill="x", padx=12, pady=6)

        self._task_vars = {}
        tasks = [
            ("leaders",       "👤 Collect All Leaders Data (Lok Sabha / Wikipedia)"),
            ("announcements", "📢 Fetch Announcements from News RSS"),
            ("promises",      "📜 Track Promise Fulfillment (Pending → Fulfilled/Broken)"),
            ("cases",         "⚖️  Detect Criminal / Fake Cases"),
            ("funds",         "💰 Track MPLADS Fund Allocation & Leakage"),
            ("scores",        "📊 Recalculate All Leader Scores (AI)"),
        ]
        grid = tk.Frame(tasks_frame, bg="#1e293b")
        grid.pack(fill="x", padx=12, pady=8)
        for i, (key, label) in enumerate(tasks):
            var = tk.BooleanVar(value=True)
            self._task_vars[key] = var
            cb = tk.Checkbutton(grid, text=label, variable=var,
                                bg="#1e293b", fg="#e2e8f0", selectcolor="#0f172a",
                                activebackground="#1e293b", font=("Segoe UI", 10),
                                cursor="hand2")
            cb.grid(row=i // 2, column=i % 2, sticky="w", padx=16, pady=4)

        # Gemini key row
        key_frame = tk.Frame(self.parent, bg="#0f172a")
        key_frame.pack(fill="x", padx=12, pady=4)
        tk.Label(key_frame, text="Gemini API Key:", bg="#0f172a", fg="#94a3b8",
                 font=("Segoe UI", 9)).pack(side="left")
        self._gemini_var = tk.StringVar()
        ttk.Entry(key_frame, textvariable=self._gemini_var, show="*", width=40).pack(side="left", padx=8)
        tk.Button(key_frame, text="Load from Settings", bg="#1e293b", fg="#64748b",
                  relief="flat", cursor="hand2", command=self._load_key).pack(side="left")

        # Run / Stop buttons
        btn_frame = tk.Frame(self.parent, bg="#0f172a")
        btn_frame.pack(fill="x", padx=12, pady=6)
        self._run_btn = tk.Button(btn_frame, text="▶  Run Auto-Fetch Now",
                                  bg="#22c55e", fg="white", relief="flat",
                                  font=("Segoe UI", 11, "bold"), cursor="hand2",
                                  command=self._run, padx=20, pady=10)
        self._run_btn.pack(side="left")
        self._stop_btn = tk.Button(btn_frame, text="⏹ Stop",
                                   bg="#ef4444", fg="white", relief="flat",
                                   font=("Segoe UI", 10), cursor="hand2",
                                   command=self._stop, padx=14, pady=10,
                                   state="disabled")
        self._stop_btn.pack(side="left", padx=8)

        # Progress
        self._progress = ttk.Progressbar(self.parent, mode="indeterminate", length=400)
        self._progress.pack(padx=12, pady=(4, 0), fill="x")

        # Log
        log_frame = tk.LabelFrame(self.parent, text=" Live Log ",
                                   bg="#0f172a", fg="#64748b",
                                   font=("Segoe UI", 9, "bold"), relief="flat")
        log_frame.pack(fill="both", expand=True, padx=12, pady=8)
        self._log = tk.Text(log_frame, bg="#020617", fg="#86efac",
                            font=("Cascadia Code", 9), state="disabled",
                            wrap="word", insertbackground="white")
        vsb = ttk.Scrollbar(log_frame, orient="vertical", command=self._log.yview)
        self._log.configure(yscrollcommand=vsb.set)
        self._log.pack(side="left", fill="both", expand=True)
        vsb.pack(side="right", fill="y")
        self._tag_colors()

    def _tag_colors(self):
        self._log.tag_configure("success", foreground="#86efac")
        self._log.tag_configure("error",   foreground="#f87171")
        self._log.tag_configure("warn",    foreground="#fde68a")
        self._log.tag_configure("info",    foreground="#93c5fd")
        self._log.tag_configure("default", foreground="#86efac")

    def _append_log(self, text: str):
        self._log.configure(state="normal")
        tag = "default"
        if "✓" in text or "success" in text.lower(): tag = "success"
        elif "✗" in text or "Error" in text or "error" in text: tag = "error"
        elif "[!]" in text or "LEAKAGE" in text: tag = "warn"
        elif "[*]" in text or "[1/" in text: tag = "info"
        self._log.insert("end", text + "\n", tag)
        self._log.see("end")
        self._log.configure(state="disabled")

    def _load_key(self):
        try:
            row = self.db.fetchone("SELECT `value` FROM settings WHERE `key`='gemini_api_key'")
            if row and row["value"]:
                self._gemini_var.set(row["value"])
                self._append_log("[*] Gemini key loaded from settings.")
            else:
                self._append_log("[!] No Gemini key in settings. Enter manually.")
        except Exception as e:
            self._append_log(f"[✗] {e}")

    def _run(self):
        tasks = [k for k, v in self._task_vars.items() if v.get()]
        if not tasks:
            messagebox.showwarning("No Tasks", "Select at least one task."); return
        gemini_key = self._gemini_var.get().strip()
        if not gemini_key:
            if not messagebox.askyesno("No Gemini Key",
                                       "No Gemini API key set.\nAI classification will be skipped.\nContinue anyway?"):
                return

        self._log.configure(state="normal")
        self._log.delete("1.0", "end")
        self._log.configure(state="disabled")

        from auto.engine import AutoEngine
        self._engine = AutoEngine(self.db, gemini_key, log_callback=lambda t: self.parent.after(0, lambda: self._append_log(t)))
        self._run_btn.config(state="disabled")
        self._stop_btn.config(state="normal")
        self._progress.start(12)
        self.set_status("🤖 Auto Engine running...", "#f97316")
        self._engine.start(tasks)
        self._poll_done()

    def _poll_done(self):
        if self._engine and not self._engine._running:
            self._done()
        else:
            self.parent.after(500, self._poll_done)

    def _stop(self):
        if self._engine:
            self._engine.stop()
        self._done()
        self._append_log("[!] Stopped by user.")

    def _done(self):
        self._progress.stop()
        self._run_btn.config(state="normal")
        self._stop_btn.config(state="disabled")
        self.set_status("✅ Auto Engine finished", "#22c55e")

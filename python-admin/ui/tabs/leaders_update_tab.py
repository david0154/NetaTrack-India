"""
NetaTrack India — Leaders Update Tab
Full UI for updating all leaders from Sansad API + Wikipedia.
Shows live progress, stats, and allows single-leader refresh.
"""
import tkinter as tk
from tkinter import ttk, messagebox
import threading
from auto.leader_updater import LeaderUpdater


class LeadersUpdateTab:
    """
    Embedded inside the Leaders tab as a sub-section.
    Can also be used as a standalone tab.
    """

    def __init__(self, parent: tk.Widget, db, set_status, ai=None):
        self.db = db
        self.ai = ai or type('AI', (), {'has_any_key': lambda s: False})()
        self.set_status = set_status
        self._build(parent)

    def _build(self, parent):
        # ---- Header row -------------------------------------------------
        hdr = tk.Frame(parent, bg="#0f172a")
        hdr.pack(fill="x", padx=16, pady=(16, 0))

        tk.Label(hdr, text="🔄  Update All Leaders",
                 font=("Segoe UI", 12, "bold"),
                 bg="#0f172a", fg="#f8fafc").pack(side="left")

        # Stats bar
        self._stats_var = tk.StringVar(value="")
        tk.Label(hdr, textvariable=self._stats_var,
                 font=("Segoe UI", 9), bg="#0f172a",
                 fg="#22c55e").pack(side="right", padx=8)

        # ---- Source checkboxes ------------------------------------------
        src_frame = tk.LabelFrame(
            parent, text="  Sources  ",
            bg="#0f172a", fg="#94a3b8",
            font=("Segoe UI", 9),
            bd=1, relief="groove", labelanchor="nw"
        )
        src_frame.pack(fill="x", padx=16, pady=10)

        self._src_vars = {}
        sources = [
            ("lok_sabha",   "🏗  Lok Sabha MPs (Sansad API)"),
            ("rajya_sabha", "🏗  Rajya Sabha MPs (Sansad API)"),
            ("wikipedia",   "📖  Bio & Photos (Wikipedia)"),
            ("scores",      "📊  Recalculate Scores"),
        ]
        row_f = tk.Frame(src_frame, bg="#0f172a")
        row_f.pack(fill="x", padx=12, pady=8)
        for key, label in sources:
            var = tk.BooleanVar(value=True)
            self._src_vars[key] = var
            tk.Checkbutton(
                row_f, text=label, variable=var,
                bg="#0f172a", fg="#f8fafc",
                selectcolor="#1e293b",
                activebackground="#0f172a",
                font=("Segoe UI", 9), anchor="w",
                cursor="hand2"
            ).pack(side="left", padx=14)

        # ---- Options row ------------------------------------------------
        opt_frame = tk.Frame(parent, bg="#0f172a")
        opt_frame.pack(fill="x", padx=16, pady=(0, 8))

        self._opt_photos = tk.BooleanVar(value=True)
        self._opt_bios   = tk.BooleanVar(value=True)

        tk.Checkbutton(
            opt_frame, text="Update Photos",
            variable=self._opt_photos,
            bg="#0f172a", fg="#94a3b8",
            selectcolor="#1e293b",
            activebackground="#0f172a",
            font=("Segoe UI", 9)
        ).pack(side="left", padx=(0, 16))

        tk.Checkbutton(
            opt_frame, text="Update Bios",
            variable=self._opt_bios,
            bg="#0f172a", fg="#94a3b8",
            selectcolor="#1e293b",
            activebackground="#0f172a",
            font=("Segoe UI", 9)
        ).pack(side="left", padx=(0, 24))

        # ---- Buttons ----------------------------------------------------
        btn_frame = tk.Frame(parent, bg="#0f172a")
        btn_frame.pack(fill="x", padx=16, pady=(0, 10))

        self._run_btn = tk.Button(
            btn_frame,
            text="▶  Update All Leaders Now",
            font=("Segoe UI", 10, "bold"),
            bg="#3b82f6", fg="#fff",
            relief="flat", cursor="hand2", padx=18, pady=8,
            command=self._run_update
        )
        self._run_btn.pack(side="left", padx=(0, 10))

        tk.Button(
            btn_frame,
            text="🔄  Refresh Single Leader",
            font=("Segoe UI", 9),
            bg="#1e293b", fg="#94a3b8",
            relief="flat", cursor="hand2", padx=12, pady=8,
            command=self._refresh_single
        ).pack(side="left", padx=(0, 10))

        tk.Button(
            btn_frame,
            text="🗹  Clear Log",
            font=("Segoe UI", 9),
            bg="#1e293b", fg="#64748b",
            relief="flat", cursor="hand2", padx=12, pady=8,
            command=self._clear_log
        ).pack(side="left")

        # Leader count badge
        self._count_var = tk.StringVar(value="")
        tk.Label(
            btn_frame, textvariable=self._count_var,
            font=("Segoe UI", 9), bg="#0f172a", fg="#60a5fa"
        ).pack(side="right", padx=8)

        # ---- Progress bar -----------------------------------------------
        self._progress = ttk.Progressbar(
            parent, mode="indeterminate", length=600
        )
        self._progress.pack(fill="x", padx=16, pady=(0, 6))

        # ---- Log box ----------------------------------------------------
        log_frame = tk.Frame(parent, bg="#020617")
        log_frame.pack(fill="both", expand=True, padx=16, pady=(0, 16))

        self._log_box = tk.Text(
            log_frame,
            bg="#020617", fg="#94a3b8",
            font=("Consolas", 9),
            relief="flat", state="disabled",
            wrap="word", height=18
        )
        sb = ttk.Scrollbar(log_frame, command=self._log_box.yview)
        self._log_box.configure(yscrollcommand=sb.set)
        sb.pack(side="right", fill="y")
        self._log_box.pack(fill="both", expand=True)

        # Colour tags
        self._log_box.tag_config("green",  foreground="#22c55e")
        self._log_box.tag_config("yellow", foreground="#f59e0b")
        self._log_box.tag_config("red",    foreground="#ef4444")
        self._log_box.tag_config("blue",   foreground="#60a5fa")
        self._log_box.tag_config("dim",    foreground="#475569")

        self._refresh_count()

    # ---- UI helpers ----------------------------------------------------- #

    def _log(self, msg: str):
        """Append to log box with colour coding."""
        tag = "dim"
        m = msg.lower()
        if any(x in m for x in ["\u2713", "added", "ready", "complete", "ok"]):
            tag = "green"
        elif any(x in m for x in ["updated", "wiki", "refreshed", "↻"]):
            tag = "blue"
        elif any(x in m for x in ["!", "error", "fail", "not found"]):
            tag = "red"
        elif any(x in m for x in ["fetching", "enriching", "updating", "▶"]):
            tag = "yellow"

        def _insert():
            self._log_box.configure(state="normal")
            self._log_box.insert("end", msg + "\n", tag)
            self._log_box.see("end")
            self._log_box.configure(state="disabled")

        try:
            self._log_box.after(0, _insert)
        except Exception:
            pass

    def _clear_log(self):
        self._log_box.configure(state="normal")
        self._log_box.delete("1.0", "end")
        self._log_box.configure(state="disabled")

    def _refresh_count(self):
        try:
            row = self.db.fetchone("SELECT COUNT(*) AS c FROM leaders WHERE status='active'")
            count = row["c"] if row else 0
            self._count_var.set(f"👤 {count} active leaders in DB")
        except Exception:
            pass

    def _set_running(self, running: bool):
        state = "disabled" if running else "normal"
        self._run_btn.configure(state=state)
        if running:
            self._progress.start(10)
        else:
            self._progress.stop()

    # ---- Actions -------------------------------------------------------- #

    def _run_update(self):
        sources = [k for k, v in self._src_vars.items() if v.get()]
        if not sources:
            messagebox.showwarning("No Sources", "Select at least one source.")
            return

        self._clear_log()
        self._set_running(True)
        self._stats_var.set("Running...")
        self.set_status("🔄 Updating leaders...", "#f59e0b")

        def _run():
            updater = LeaderUpdater(self.db, self.ai, self._log)
            result = updater.run_full_update(
                update_photos=self._opt_photos.get(),
                update_bios=self._opt_bios.get(),
                sources=sources
            )

            def _done():
                self._set_running(False)
                self._refresh_count()
                stats = (f"➕ {result['added']} added  "
                         f"↻ {result['updated']} updated  "
                         f"— {result['skipped']} unchanged")
                self._stats_var.set(stats)
                self.set_status(
                    f"[✓] Leaders updated: {result['added']} added, "
                    f"{result['updated']} updated", "#22c55e"
                )

            try:
                self._run_btn.after(0, _done)
            except Exception:
                pass

        threading.Thread(target=_run, daemon=True).start()

    def _refresh_single(self):
        """Prompt for leader name or ID and refresh just that one."""
        win = tk.Toplevel()
        win.title("Refresh Single Leader")
        win.configure(bg="#0f172a")
        win.geometry("380x160")
        win.resizable(False, False)
        win.grab_set()

        tk.Label(win, text="Enter leader name or ID:",
                 font=("Segoe UI", 10), bg="#0f172a",
                 fg="#94a3b8").pack(pady=(20, 6))
        entry = tk.Entry(
            win, font=("Segoe UI", 11),
            bg="#1e293b", fg="#f8fafc",
            insertbackground="#f8fafc",
            relief="flat", width=32
        )
        entry.pack()
        entry.focus()

        def _go():
            val = entry.get().strip()
            win.destroy()
            if not val:
                return

            # Resolve name or ID
            if val.isdigit():
                row = self.db.fetchone(
                    "SELECT id FROM leaders WHERE id=%s", (int(val),)
                )
            else:
                row = self.db.fetchone(
                    "SELECT id FROM leaders WHERE name LIKE %s LIMIT 1",
                    (f"%{val}%",)
                )
            if not row:
                messagebox.showerror("Not Found", f"Leader '{val}' not found in DB.")
                return

            self._clear_log()
            self._set_running(True)

            def _run():
                updater = LeaderUpdater(self.db, self.ai, self._log)
                updater.run_single_leader(row["id"])
                try:
                    self._run_btn.after(0, lambda: self._set_running(False))
                except Exception:
                    pass

            threading.Thread(target=_run, daemon=True).start()

        tk.Button(
            win, text="🔄 Refresh",
            font=("Segoe UI", 10, "bold"),
            bg="#3b82f6", fg="#fff",
            relief="flat", cursor="hand2",
            padx=16, pady=6, command=_go
        ).pack(pady=14)
        win.bind("<Return>", lambda e: _go())

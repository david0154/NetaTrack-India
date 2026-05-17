import tkinter as tk
from tkinter import ttk, messagebox
import threading
import importlib.util
import os
import sys


class ScraperTab:
    def __init__(self, parent, db, set_status):
        self.parent = parent
        self.db = db
        self.set_status = set_status
        self._build()
        self.load()

    def _build(self):
        top = tk.Frame(self.parent, bg="#0f172a")
        top.pack(fill="x", padx=12, pady=12)
        tk.Label(top, text="🤖 AI News Scraper", font=("Segoe UI", 13, "bold"),
                 bg="#0f172a", fg="#f8fafc").pack(side="left")
        self._run_btn = tk.Button(top, text="▶ Run Scraper Now", bg="#3b82f6", fg="white",
                                  relief="flat", font=("Segoe UI", 10, "bold"), cursor="hand2",
                                  command=self._run)
        self._run_btn.pack(side="right")

        # Log box
        log_frame = tk.LabelFrame(self.parent, text=" Scraper Log ", bg="#1e293b",
                                  fg="#94a3b8", font=("Segoe UI", 9, "bold"), relief="flat")
        log_frame.pack(fill="both", expand=True, padx=12, pady=(4, 4))
        self._log = tk.Text(log_frame, bg="#0f172a", fg="#86efac", font=("Cascadia Code", 9),
                            insertbackground="white", state="disabled", wrap="word")
        vsb = ttk.Scrollbar(log_frame, orient="vertical", command=self._log.yview)
        self._log.configure(yscrollcommand=vsb.set)
        self._log.pack(side="left", fill="both", expand=True)
        vsb.pack(side="right", fill="y")

        # Jobs table
        tk.Label(self.parent, text="Recent Jobs", font=("Segoe UI", 10, "bold"),
                 bg="#0f172a", fg="#94a3b8").pack(anchor="w", padx=12, pady=(8,2))
        cols = ["Source", "Status", "Items", "Started", "Error"]
        widths = [180, 80, 60, 140, 300]
        frame = tk.Frame(self.parent, bg="#1e293b")
        frame.pack(fill="x", padx=12, pady=(0, 8))
        self.tree = ttk.Treeview(frame, columns=cols, show="headings", height=6)
        for col, w in zip(cols, widths):
            self.tree.heading(col, text=col)
            self.tree.column(col, width=w, minwidth=40)
        self.tree.pack(fill="x")

    def _append_log(self, text):
        self._log.configure(state="normal")
        self._log.insert("end", text + "\n")
        self._log.see("end")
        self._log.configure(state="disabled")

    def load(self):
        for row in self.tree.get_children():
            self.tree.delete(row)
        try:
            rows = self.db.fetchall(
                "SELECT source_name, status, items_found, started_at, error_msg "
                "FROM scraper_jobs ORDER BY id DESC LIMIT 30"
            )
            for r in rows:
                self.tree.insert("", "end", values=(
                    r["source_name"], r["status"], r["items_found"],
                    str(r["started_at"] or ""), (r["error_msg"] or "")[:80]
                ))
        except Exception as e:
            self._append_log(f"[Error loading jobs] {e}")

    def _run(self):
        self._run_btn.config(state="disabled", text="Running...")
        self._append_log("[*] Starting scraper...")
        def _do():
            try:
                # Try to call PHP scraper via HTTP if site URL is set
                row = self.db.fetchone("SELECT `value` FROM settings WHERE `key`='site_url'")
                site_url = row["value"] if row else ""
                if site_url:
                    import urllib.request
                    url = site_url.rstrip("/") + "/admin/scraper/run"
                    self.after(0, lambda: self._append_log(f"[*] Calling: {url}"))
                    req = urllib.request.Request(url, method="POST", data=b"")
                    with urllib.request.urlopen(req, timeout=60) as resp:
                        body = resp.read().decode(errors="ignore")[:400]
                    self.after(0, lambda: self._append_log(f"[✓] Response: {body}"))
                else:
                    self.after(0, lambda: self._append_log("[!] No site_url set in settings. Configure it first."))
            except Exception as e:
                self.after(0, lambda: self._append_log(f"[❌] Error: {e}"))
            finally:
                self.after(0, self._done)
        threading.Thread(target=_do, daemon=True).start()

    def _done(self):
        self._run_btn.config(state="normal", text="▶ Run Scraper Now")
        self._append_log("[*] Done.")
        self.load()
        self.set_status("🤖 Scraper finished", "#22c55e")

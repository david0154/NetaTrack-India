import tkinter as tk
from tkinter import ttk, messagebox


class ReportsTab:
    def __init__(self, parent, db, set_status):
        self.parent = parent
        self.db = db
        self.set_status = set_status
        self._build()
        self.load()

    def _build(self):
        tb = tk.Frame(self.parent, bg="#0f172a")
        tb.pack(fill="x", padx=12, pady=8)
        tk.Label(tb, text="Status:", bg="#0f172a", fg="#94a3b8").pack(side="left")
        self._status_var = tk.StringVar(value="pending")
        ttk.Combobox(tb, textvariable=self._status_var, values=["all","pending","approved","rejected"],
                     state="readonly", width=10).pack(side="left", padx=6)
        tk.Button(tb, text="Filter", bg="#1e293b", fg="#94a3b8", relief="flat",
                  cursor="hand2", command=self.load).pack(side="left")
        tk.Button(tb, text="✅ Approve", bg="#22c55e", fg="white", relief="flat",
                  font=("Segoe UI", 9, "bold"), cursor="hand2", command=self._approve).pack(side="left", padx=8)
        tk.Button(tb, text="❌ Reject", bg="#ef4444", fg="white", relief="flat",
                  font=("Segoe UI", 9, "bold"), cursor="hand2", command=self._reject).pack(side="left")
        tk.Button(tb, text="🔄 Refresh", bg="#1e293b", fg="#94a3b8", relief="flat",
                  cursor="hand2", command=self.load).pack(side="right")

        cols = ["ID", "Leader", "Title", "Type", "AI Conf", "Status", "Date"]
        widths = [40, 140, 260, 100, 60, 80, 90]
        frame = tk.Frame(self.parent, bg="#1e293b")
        frame.pack(fill="both", expand=True, padx=12, pady=(0, 8))
        self.tree = ttk.Treeview(frame, columns=cols, show="headings")
        for col, w in zip(cols, widths):
            self.tree.heading(col, text=col)
            self.tree.column(col, width=w, minwidth=30)
        vsb = ttk.Scrollbar(frame, orient="vertical", command=self.tree.yview)
        self.tree.configure(yscrollcommand=vsb.set)
        self.tree.grid(row=0, column=0, sticky="nsew")
        vsb.grid(row=0, column=1, sticky="ns")
        frame.rowconfigure(0, weight=1)
        frame.columnconfigure(0, weight=1)

    def load(self):
        st = self._status_var.get()
        for row in self.tree.get_children():
            self.tree.delete(row)
        try:
            where = "" if st == "all" else "AND r.status=%s"
            params = () if st == "all" else (st,)
            rows = self.db.fetchall(
                f"SELECT r.id, COALESCE(l.name,'Unknown') AS leader, r.title, r.type, "
                f"COALESCE(r.ai_confidence,0) AS ai_confidence, r.status, DATE(r.created_at) AS d "
                f"FROM reports r LEFT JOIN leaders l ON r.leader_id=l.id WHERE 1=1 {where} ORDER BY r.id DESC LIMIT 200",
                params
            )
            for r in rows:
                conf = r["ai_confidence"]
                self.tree.insert("", "end", iid=r["id"], values=(
                    r["id"], r["leader"], r["title"][:80], r["type"], f"{conf}%", r["status"], str(r["d"])
                ))
            self.set_status(f"📋 {len(rows)} reports", "#94a3b8")
        except Exception as e:
            self.set_status(f"❌ {e}", "#f87171")

    def _selected_id(self):
        sel = self.tree.selection()
        if not sel:
            messagebox.showwarning("Select", "Select a report first."); return None
        return sel[0]

    def _approve(self):
        rid = self._selected_id()
        if rid and messagebox.askyesno("Approve", f"Approve report #{rid}?"):
            self.db.execute("UPDATE reports SET status='approved' WHERE id=%s", (rid,))
            self.load(); self.set_status("✅ Approved", "#22c55e")

    def _reject(self):
        rid = self._selected_id()
        if rid and messagebox.askyesno("Reject", f"Reject report #{rid}?"):
            self.db.execute("UPDATE reports SET status='rejected' WHERE id=%s", (rid,))
            self.load(); self.set_status("❌ Rejected", "#f87171")

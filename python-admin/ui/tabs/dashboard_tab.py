import tkinter as tk
from tkinter import ttk


class DashboardTab:
    def __init__(self, parent, db, set_status):
        self.parent = parent
        self.db = db
        self.set_status = set_status
        self._build()
        self.load()

    def _build(self):
        self.parent.configure(style="TFrame")
        tk.Label(self.parent, text="Platform Overview", font=("Segoe UI", 14, "bold"),
                 bg="#0f172a", fg="#f8fafc").pack(anchor="w", padx=20, pady=(16, 8))

        # Stats frame
        self.stats_frame = tk.Frame(self.parent, bg="#0f172a")
        self.stats_frame.pack(fill="x", padx=20, pady=(0, 16))

        # Recent activity
        mid = tk.Frame(self.parent, bg="#0f172a")
        mid.pack(fill="both", expand=True, padx=20, pady=0)
        mid.columnconfigure(0, weight=1)
        mid.columnconfigure(1, weight=1)

        # Pending reports
        lf1 = tk.LabelFrame(mid, text=" ⏳ Pending Reports ", bg="#1e293b", fg="#94a3b8",
                             font=("Segoe UI", 10, "bold"), relief="flat", bd=1)
        lf1.grid(row=0, column=0, sticky="nsew", padx=(0, 8), pady=4)
        self.pending_tree = self._make_tree(lf1, ["ID", "Leader", "Type", "Date"], [40, 160, 100, 90])

        # Top leaders
        lf2 = tk.LabelFrame(mid, text=" 🏆 Top Leaders ", bg="#1e293b", fg="#94a3b8",
                             font=("Segoe UI", 10, "bold"), relief="flat", bd=1)
        lf2.grid(row=0, column=1, sticky="nsew", padx=(8, 0), pady=4)
        self.leaders_tree = self._make_tree(lf2, ["Name", "Party", "Score", "Rank"], [160, 80, 60, 50])

    def _make_tree(self, parent, cols, widths):
        frame = tk.Frame(parent, bg="#1e293b")
        frame.pack(fill="both", expand=True, padx=4, pady=4)
        tree = ttk.Treeview(frame, columns=cols, show="headings", height=12)
        for col, w in zip(cols, widths):
            tree.heading(col, text=col)
            tree.column(col, width=w, minwidth=30)
        vsb = ttk.Scrollbar(frame, orient="vertical", command=tree.yview)
        tree.configure(yscrollcommand=vsb.set)
        tree.pack(side="left", fill="both", expand=True)
        vsb.pack(side="right", fill="y")
        return tree

    def load(self):
        try:
            # Stat cards
            for w in self.stats_frame.winfo_children():
                w.destroy()
            queries = [
                ("Leaders",          "SELECT COUNT(*) as c FROM leaders",          "👤", "#f97316"),
                ("Promises",         "SELECT COUNT(*) as c FROM promises",         "📜", "#3b82f6"),
                ("Projects",         "SELECT COUNT(*) as c FROM projects",         "🏗️", "#22c55e"),
                ("Reports (total)",  "SELECT COUNT(*) as c FROM reports",          "📋", "#8b5cf6"),
                ("Pending",          "SELECT COUNT(*) as c FROM reports WHERE status='pending'", "⏳", "#ef4444"),
                ("Users",            "SELECT COUNT(*) as c FROM users",            "👥", "#06b6d4"),
            ]
            for label, sql, icon, color in queries:
                try:
                    row = self.db.fetchone(sql)
                    val = row["c"] if row else 0
                except Exception:
                    val = "N/A"
                card = tk.Frame(self.stats_frame, bg="#1e293b", width=140, height=88, relief="flat")
                card.pack(side="left", padx=6, pady=4)
                card.pack_propagate(False)
                tk.Frame(card, bg=color, height=3).pack(fill="x")
                tk.Label(card, text=icon, font=("Segoe UI Emoji", 18), bg="#1e293b").pack(pady=(6, 0))
                tk.Label(card, text=str(val), font=("Segoe UI", 16, "bold"), bg="#1e293b", fg="#f8fafc").pack()
                tk.Label(card, text=label, font=("Segoe UI", 8), bg="#1e293b", fg="#64748b").pack()

            # Pending reports
            for row in self.pending_tree.get_children():
                self.pending_tree.delete(row)
            rows = self.db.fetchall(
                "SELECT r.id, COALESCE(l.name,'Unknown') AS leader, r.type, DATE(r.created_at) AS d "
                "FROM reports r LEFT JOIN leaders l ON r.leader_id=l.id WHERE r.status='pending' ORDER BY r.id DESC LIMIT 30"
            )
            for r in rows:
                self.pending_tree.insert("", "end", values=(r["id"], r["leader"], r["type"], str(r["d"])))

            # Top leaders
            for row in self.leaders_tree.get_children():
                self.leaders_tree.delete(row)
            rows = self.db.fetchall(
                "SELECT l.name, COALESCE(p.abbreviation,'?') AS party, l.total_score, l.score_rank "
                "FROM leaders l LEFT JOIN parties p ON l.party_id=p.id ORDER BY l.total_score DESC LIMIT 20"
            )
            for r in rows:
                self.leaders_tree.insert("", "end", values=(r["name"], r["party"], r["total_score"], r["score_rank"]))

            self.set_status("🟢 Dashboard loaded", "#22c55e")
        except Exception as e:
            self.set_status(f"❌ {e}", "#f87171")

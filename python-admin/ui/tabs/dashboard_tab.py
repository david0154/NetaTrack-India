"""
NetaTrack India — Dashboard Tab
Shows live stats from DB.
"""
import tkinter as tk
from tkinter import ttk


class DashboardTab:
    def __init__(self, parent, db, set_status):
        self.db = db
        self.set_status = set_status
        self._build(parent)
        self._refresh()

    def _build(self, parent):
        tk.Label(parent, text="\ud83d\udcca  Dashboard",
                 font=("Segoe UI", 14, "bold"),
                 bg="#0f172a", fg="#f8fafc").pack(
            anchor="w", padx=24, pady=(20, 12)
        )

        # Stat cards row
        self._cards_frame = tk.Frame(parent, bg="#0f172a")
        self._cards_frame.pack(fill="x", padx=24)

        self._stats = {
            "Leaders":      ("\ud83d\udc64", "leaders",       "#3b82f6"),
            "Reports":      ("\ud83d\udccb", "reports",       "#f59e0b"),
            "Users":        ("\ud83d\udc65", "users",         "#22c55e"),
            "Announcements":("\ud83d\udcf0", "announcements", "#a855f7"),
            "Parties":      ("\ud83c\udff7\ufe0f",  "parties",      "#ef4444"),
            "States":       ("\ud83d\uddfa\ufe0f", "states",       "#06b6d4"),
        }
        self._card_vars = {}
        for label, (icon, table, color) in self._stats.items():
            card = tk.Frame(self._cards_frame,
                            bg="#1e293b", padx=20, pady=14,
                            relief="flat")
            card.pack(side="left", padx=(0, 12), pady=4)
            tk.Label(card, text=icon,
                     font=("Segoe UI", 20),
                     bg="#1e293b", fg=color).pack()
            var = tk.StringVar(value="...")
            self._card_vars[table] = var
            tk.Label(card, textvariable=var,
                     font=("Segoe UI", 18, "bold"),
                     bg="#1e293b", fg="#f8fafc").pack()
            tk.Label(card, text=label,
                     font=("Segoe UI", 8),
                     bg="#1e293b", fg="#64748b").pack()

        # Recent leaders
        tk.Label(parent, text="Top Leaders by Score",
                 font=("Segoe UI", 11, "bold"),
                 bg="#0f172a", fg="#94a3b8").pack(
            anchor="w", padx=24, pady=(24, 6)
        )

        tree_frame = tk.Frame(parent, bg="#0f172a")
        tree_frame.pack(fill="both", expand=True, padx=24, pady=(0, 24))

        cols = ("name", "party", "state", "role", "score")
        self._tree = ttk.Treeview(tree_frame, columns=cols,
                                  show="headings", height=12)
        heads = {"name": "Leader", "party": "Party",
                 "state": "State", "role": "Role", "score": "Score"}
        widths = {"name": 200, "party": 120, "state": 140,
                  "role": 200, "score": 70}
        for c in cols:
            self._tree.heading(c, text=heads[c])
            self._tree.column(c, width=widths[c], minwidth=60)

        sb = ttk.Scrollbar(tree_frame, command=self._tree.yview)
        self._tree.configure(yscrollcommand=sb.set)
        sb.pack(side="right", fill="y")
        self._tree.pack(fill="both", expand=True)

        # Refresh button
        tk.Button(parent, text="\ud83d\udd04  Refresh",
                  font=("Segoe UI", 9),
                  bg="#1e293b", fg="#94a3b8",
                  relief="flat", cursor="hand2",
                  padx=14, pady=6,
                  command=self._refresh).pack(anchor="w", padx=24, pady=(0, 16))

    def _refresh(self):
        # Update stat cards
        for label, (icon, table, color) in self._stats.items():
            try:
                row = self.db.fetchone(f"SELECT COUNT(*) AS c FROM `{table}`")
                self._card_vars[table].set(str(row["c"]) if row else "0")
            except Exception:
                self._card_vars[table].set("-")

        # Update top leaders tree
        try:
            rows = self.db.fetchall(
                "SELECT l.name, p.name AS party, s.name AS state, "
                "l.role, l.total_score "
                "FROM leaders l "
                "LEFT JOIN parties p ON l.party_id=p.id "
                "LEFT JOIN states  s ON l.state_id=s.id "
                "WHERE l.status='active' "
                "ORDER BY l.total_score DESC LIMIT 20"
            )
            for item in self._tree.get_children():
                self._tree.delete(item)
            for r in rows:
                self._tree.insert("", "end", values=(
                    r["name"] or "",
                    r["party"] or "",
                    r["state"] or "",
                    r["role"] or "",
                    r["total_score"] or 0
                ))
        except Exception as e:
            pass

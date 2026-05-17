import tkinter as tk
from tkinter import ttk, messagebox, simpledialog


class LeadersTab:
    def __init__(self, parent, db, set_status):
        self.parent = parent
        self.db = db
        self.set_status = set_status
        self._build()
        self.load()

    def _build(self):
        # Toolbar
        tb = tk.Frame(self.parent, bg="#0f172a")
        tb.pack(fill="x", padx=12, pady=8)
        tk.Label(tb, text="Search:", bg="#0f172a", fg="#94a3b8").pack(side="left")
        self._search_var = tk.StringVar()
        self._search_var.trace_add("write", lambda *a: self.load())
        ttk.Entry(tb, textvariable=self._search_var, width=24).pack(side="left", padx=6)

        tk.Button(tb, text="+ Add Leader", bg="#22c55e", fg="white", relief="flat",
                  font=("Segoe UI", 9, "bold"), cursor="hand2", command=self._add).pack(side="left", padx=4)
        tk.Button(tb, text="✏️ Edit", bg="#3b82f6", fg="white", relief="flat",
                  font=("Segoe UI", 9), cursor="hand2", command=self._edit).pack(side="left", padx=2)
        tk.Button(tb, text="🗑️ Delete", bg="#ef4444", fg="white", relief="flat",
                  font=("Segoe UI", 9), cursor="hand2", command=self._delete).pack(side="left", padx=2)
        tk.Button(tb, text="🔄 Refresh", bg="#1e293b", fg="#94a3b8", relief="flat",
                  font=("Segoe UI", 9), cursor="hand2", command=self.load).pack(side="right")

        # Table
        cols = ["ID", "Name", "Party", "Designation", "State", "Score", "Status", "Verified"]
        widths = [40, 180, 80, 140, 100, 60, 70, 60]
        frame = tk.Frame(self.parent, bg="#1e293b")
        frame.pack(fill="both", expand=True, padx=12, pady=(0, 8))
        self.tree = ttk.Treeview(frame, columns=cols, show="headings")
        for col, w in zip(cols, widths):
            self.tree.heading(col, text=col, command=lambda c=col: self._sort(c))
            self.tree.column(col, width=w, minwidth=30)
        vsb = ttk.Scrollbar(frame, orient="vertical", command=self.tree.yview)
        hsb = ttk.Scrollbar(frame, orient="horizontal", command=self.tree.xview)
        self.tree.configure(yscrollcommand=vsb.set, xscrollcommand=hsb.set)
        self.tree.grid(row=0, column=0, sticky="nsew")
        vsb.grid(row=0, column=1, sticky="ns")
        hsb.grid(row=1, column=0, sticky="ew")
        frame.rowconfigure(0, weight=1)
        frame.columnconfigure(0, weight=1)
        self.tree.bind("<Double-1>", lambda e: self._edit())

    def load(self):
        q = self._search_var.get()
        for row in self.tree.get_children():
            self.tree.delete(row)
        try:
            rows = self.db.fetchall(
                "SELECT l.id, l.name, COALESCE(p.abbreviation,'?') AS party, "
                "COALESCE(l.designation,'') AS designation, COALESCE(s.name,'') AS state, "
                "COALESCE(l.total_score,0) AS score, l.status, l.is_verified "
                "FROM leaders l LEFT JOIN parties p ON l.party_id=p.id "
                "LEFT JOIN states s ON l.state_id=s.id "
                "WHERE l.name LIKE %s OR l.designation LIKE %s OR p.name LIKE %s "
                "ORDER BY l.name",
                (f"%{q}%", f"%{q}%", f"%{q}%")
            )
            for r in rows:
                self.tree.insert("", "end", iid=r["id"], values=(
                    r["id"], r["name"], r["party"], r["designation"],
                    r["state"], r["score"], r["status"], "✓" if r["is_verified"] else ""
                ))
            self.set_status(f"👤 {len(rows)} leaders loaded", "#94a3b8")
        except Exception as e:
            self.set_status(f"❌ {e}", "#f87171")

    def _sort(self, col):
        data = [(self.tree.set(c, col), c) for c in self.tree.get_children("")]
        data.sort()
        for i, (_, iid) in enumerate(data):
            self.tree.move(iid, "", i)

    def _selected_id(self):
        sel = self.tree.selection()
        if not sel:
            messagebox.showwarning("Select", "Please select a leader first.")
            return None
        return sel[0]

    def _add(self):
        LeaderForm(self.parent, self.db, None, on_save=self.load)

    def _edit(self):
        lid = self._selected_id()
        if lid:
            row = self.db.fetchone("SELECT * FROM leaders WHERE id=%s", (lid,))
            LeaderForm(self.parent, self.db, row, on_save=self.load)

    def _delete(self):
        lid = self._selected_id()
        if lid and messagebox.askyesno("Delete", f"Delete leader #{lid}? This cannot be undone."):
            try:
                self.db.execute("DELETE FROM leaders WHERE id=%s", (lid,))
                self.load()
                self.set_status("✅ Leader deleted", "#22c55e")
            except Exception as e:
                self.set_status(f"❌ {e}", "#f87171")


class LeaderForm(tk.Toplevel):
    """Add / Edit leader form dialog."""
    def __init__(self, parent, db, data, on_save):
        super().__init__(parent)
        self.db = db
        self.data = data or {}
        self.on_save = on_save
        self.title("Edit Leader" if data else "Add Leader")
        self.configure(bg="#0f172a")
        self.resizable(True, True)
        self._build()
        self._center(680, 620)
        self.grab_set()

    def _center(self, w, h):
        sw = self.winfo_screenwidth()
        sh = self.winfo_screenheight()
        self.geometry(f"{w}x{h}+{(sw-w)//2}+{(sh-h)//2}")

    def _build(self):
        canvas = tk.Canvas(self, bg="#0f172a", highlightthickness=0)
        vsb = ttk.Scrollbar(self, orient="vertical", command=canvas.yview)
        canvas.configure(yscrollcommand=vsb.set)
        vsb.pack(side="right", fill="y")
        canvas.pack(side="left", fill="both", expand=True)
        inner = tk.Frame(canvas, bg="#0f172a", padx=24, pady=16)
        win_id = canvas.create_window((0, 0), window=inner, anchor="nw")
        def on_configure(e):
            canvas.configure(scrollregion=canvas.bbox("all"))
            canvas.itemconfig(win_id, width=canvas.winfo_width())
        inner.bind("<Configure>", on_configure)
        canvas.bind("<Configure>", lambda e: canvas.itemconfig(win_id, width=e.width))

        self._vars = {}
        fields = [
            ("Name *",          "name",           "entry"),
            ("Designation",     "designation",    "entry"),
            ("Constituency",    "constituency",   "entry"),
            ("Bio",             "bio",            "text"),
            ("Status",          "status",         "combo", ["active","inactive","draft"]),
        ]
        for item in fields:
            label, key, ftype = item[0], item[1], item[2]
            tk.Label(inner, text=label, bg="#0f172a", fg="#94a3b8", font=("Segoe UI", 9, "bold")).pack(anchor="w", pady=(8,1))
            if ftype == "entry":
                var = tk.StringVar(value=self.data.get(key, ""))
                self._vars[key] = var
                ttk.Entry(inner, textvariable=var, font=("Segoe UI", 10)).pack(fill="x")
            elif ftype == "text":
                t = tk.Text(inner, height=4, bg="#1e293b", fg="#f1f5f9", font=("Segoe UI", 10),
                            insertbackground="white", relief="flat")
                t.insert("1.0", self.data.get(key, "") or "")
                t.pack(fill="x")
                self._vars[key] = t
            elif ftype == "combo":
                var = tk.StringVar(value=self.data.get(key, item[3][0]))
                self._vars[key] = var
                ttk.Combobox(inner, textvariable=var, values=item[3], state="readonly").pack(fill="x")

        # Scores
        tk.Label(inner, text="Performance Scores (0–100)", bg="#0f172a", fg="#3b82f6",
                 font=("Segoe UI", 10, "bold")).pack(anchor="w", pady=(16, 4))
        score_fields = [
            ("score_promise_completion",    "Promise Completion"),
            ("score_project_delivery",      "Project Delivery"),
            ("score_transparency",          "Transparency"),
            ("score_public_satisfaction",   "Public Satisfaction"),
            ("score_attendance",            "Attendance"),
            ("score_criminal_record",       "Criminal Record"),
        ]
        grid = tk.Frame(inner, bg="#0f172a")
        grid.pack(fill="x")
        for i, (key, label) in enumerate(score_fields):
            col = i % 3
            row_num = i // 3
            cell = tk.Frame(grid, bg="#0f172a")
            cell.grid(row=row_num, column=col, padx=6, pady=4, sticky="ew")
            grid.columnconfigure(col, weight=1)
            var = tk.IntVar(value=int(self.data.get(key, 50) or 50))
            self._vars[key] = var
            lbl = tk.Label(cell, text=f"{label}: ", bg="#0f172a", fg="#94a3b8", font=("Segoe UI", 9))
            lbl.pack(anchor="w")
            val_lbl = tk.Label(cell, textvariable=var, bg="#0f172a", fg="#3b82f6", font=("Segoe UI", 9, "bold"), width=3)
            val_lbl.pack(anchor="e")
            sl = ttk.Scale(cell, from_=0, to=100, orient="horizontal", variable=var)
            sl.pack(fill="x")

        # Verified
        self._verified = tk.BooleanVar(value=bool(self.data.get("is_verified", False)))
        tk.Checkbutton(inner, text="✓ Verified Leader", variable=self._verified,
                       bg="#0f172a", fg="#22c55e", selectcolor="#0f172a",
                       activebackground="#0f172a", font=("Segoe UI", 10)).pack(anchor="w", pady=8)

        # Buttons
        btn_frame = tk.Frame(inner, bg="#0f172a")
        btn_frame.pack(fill="x", pady=12)
        tk.Button(btn_frame, text="Cancel", bg="#1e293b", fg="#94a3b8", relief="flat",
                  command=self.destroy, padx=16, pady=8).pack(side="left")
        tk.Button(btn_frame, text="💾 Save Leader", bg="#3b82f6", fg="white", relief="flat",
                  font=("Segoe UI", 10, "bold"), cursor="hand2", command=self._save,
                  padx=16, pady=8).pack(side="right")

    def _save(self):
        name = self._vars["name"].get().strip()
        if not name:
            messagebox.showerror("Error", "Name is required."); return
        vals = {k: (v.get("1.0", "end-1c") if isinstance(v, tk.Text) else v.get())
                for k, v in self._vars.items()}
        vals["is_verified"] = 1 if self._verified.get() else 0
        vals["slug"] = name.lower().replace(" ", "-")
        try:
            if self.data.get("id"):
                sets = ", ".join(f"`{k}`=%s" for k in vals)
                self.db.execute(f"UPDATE leaders SET {sets} WHERE id=%s", (*vals.values(), self.data["id"]))
            else:
                cols = ", ".join(f"`{k}`" for k in vals)
                phs  = ", ".join("%s" for _ in vals)
                self.db.execute(f"INSERT INTO leaders ({cols}) VALUES ({phs})", tuple(vals.values()))
            self.on_save()
            self.destroy()
        except Exception as e:
            messagebox.showerror("Error", str(e))

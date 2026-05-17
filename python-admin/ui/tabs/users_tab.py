import tkinter as tk
from tkinter import ttk, messagebox


class UsersTab:
    def __init__(self, parent, db, set_status):
        self.parent = parent
        self.db = db
        self.set_status = set_status
        self._build()
        self.load()

    def _build(self):
        tb = tk.Frame(self.parent, bg="#0f172a")
        tb.pack(fill="x", padx=12, pady=8)
        tk.Label(tb, text="Search:", bg="#0f172a", fg="#94a3b8").pack(side="left")
        self._search = tk.StringVar()
        self._search.trace_add("write", lambda *a: self.load())
        ttk.Entry(tb, textvariable=self._search, width=22).pack(side="left", padx=6)
        tk.Button(tb, text="🚫 Ban/Unban", bg="#ef4444", fg="white", relief="flat",
                  font=("Segoe UI", 9), cursor="hand2", command=self._toggle_ban).pack(side="left", padx=4)
        tk.Button(tb, text="🔁 Change Role", bg="#f97316", fg="white", relief="flat",
                  font=("Segoe UI", 9), cursor="hand2", command=self._change_role).pack(side="left", padx=2)
        tk.Button(tb, text="🔄 Refresh", bg="#1e293b", fg="#94a3b8", relief="flat",
                  cursor="hand2", command=self.load).pack(side="right")

        cols = ["ID", "Name", "Email", "Role", "Status", "Joined"]
        widths = [40, 160, 220, 80, 80, 90]
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
        q = self._search.get()
        for row in self.tree.get_children():
            self.tree.delete(row)
        try:
            rows = self.db.fetchall(
                "SELECT id,name,email,role,status,DATE(created_at) AS d FROM users "
                "WHERE name LIKE %s OR email LIKE %s ORDER BY id DESC LIMIT 200",
                (f"%{q}%", f"%{q}%")
            )
            for r in rows:
                self.tree.insert("", "end", iid=r["id"],
                                 values=(r["id"],r["name"],r["email"],r["role"],r["status"],str(r["d"])))
            self.set_status(f"👥 {len(rows)} users", "#94a3b8")
        except Exception as e:
            self.set_status(f"❌ {e}", "#f87171")

    def _selected(self):
        sel = self.tree.selection()
        if not sel:
            messagebox.showwarning("Select", "Select a user first."); return None
        return sel[0]

    def _toggle_ban(self):
        uid = self._selected()
        if not uid: return
        row = self.db.fetchone("SELECT role,status FROM users WHERE id=%s", (uid,))
        if row and row["role"] == "admin":
            messagebox.showwarning("Protected", "Cannot ban an admin account."); return
        new_status = "active" if row["status"] == "banned" else "banned"
        if messagebox.askyesno("Confirm", f"Set user #{uid} to '{new_status}'?"):
            self.db.execute("UPDATE users SET status=%s WHERE id=%s", (new_status, uid))
            self.load(); self.set_status(f"✅ User {new_status}", "#22c55e")

    def _change_role(self):
        uid = self._selected()
        if not uid: return
        row = self.db.fetchone("SELECT name,role FROM users WHERE id=%s", (uid,))
        dialog = RoleDialog(self.parent, row["name"], row["role"])
        self.parent.wait_window(dialog)
        if dialog.result:
            self.db.execute("UPDATE users SET role=%s WHERE id=%s", (dialog.result, uid))
            self.load(); self.set_status("✅ Role updated", "#22c55e")


class RoleDialog(tk.Toplevel):
    def __init__(self, parent, name, current_role):
        super().__init__(parent)
        self.result = None
        self.title("Change Role")
        self.configure(bg="#0f172a")
        self.resizable(False, False)
        self.grab_set()
        tk.Label(self, text=f"Change role for: {name}", bg="#0f172a", fg="#e2e8f0",
                 font=("Segoe UI", 11, "bold")).pack(pady=(20, 8), padx=24)
        self._var = tk.StringVar(value=current_role)
        for role in ["user", "moderator", "admin"]:
            tk.Radiobutton(self, text=role.capitalize(), variable=self._var, value=role,
                           bg="#0f172a", fg="#e2e8f0", selectcolor="#0f172a",
                           activebackground="#0f172a").pack(anchor="w", padx=32)
        tk.Button(self, text="Save", bg="#3b82f6", fg="white", relief="flat",
                  font=("Segoe UI", 10, "bold"), cursor="hand2", command=self._save,
                  padx=20, pady=8).pack(pady=(12, 20))
        self.geometry(f"280x220+{(self.winfo_screenwidth()-280)//2}+{(self.winfo_screenheight()-220)//2}")

    def _save(self):
        self.result = self._var.get()
        self.destroy()

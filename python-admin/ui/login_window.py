import tkinter as tk
from tkinter import ttk, messagebox
import threading
import os
from db.connection import DBConnection

DEFAULT_HOST = os.getenv("DB_HOST", "127.0.0.1")
DEFAULT_PORT = os.getenv("DB_PORT", "3306")
DEFAULT_DB   = os.getenv("DB_NAME", "netatrack")
DEFAULT_USER = os.getenv("DB_USER", "root")


class LoginWindow(tk.Toplevel):
    def __init__(self, parent, on_success):
        super().__init__(parent)
        self.parent = parent
        self.on_success = on_success
        self.title("NetaTrack India — Connect")
        self.configure(bg="#0f172a")
        self.resizable(False, False)
        self.protocol("WM_DELETE_WINDOW", self._quit)
        self._build()
        self._center(520, 440)
        self.grab_set()
        self.lift()

    def _center(self, w, h):
        sw = self.winfo_screenwidth()
        sh = self.winfo_screenheight()
        self.geometry(f"{w}x{h}+{(sw-w)//2}+{(sh-h)//2}")

    def _build(self):
        # Header
        hdr = tk.Frame(self, bg="#1e293b", pady=20)
        hdr.pack(fill="x")
        tk.Label(hdr, text="🇮🇳", font=("Segoe UI Emoji", 28), bg="#1e293b").pack()
        tk.Label(hdr, text="NetaTrack India", font=("Segoe UI", 18, "bold"), bg="#1e293b", fg="#f8fafc").pack()
        tk.Label(hdr, text="Admin Desktop App", font=("Segoe UI", 10), bg="#1e293b", fg="#64748b").pack()

        # Form
        form = tk.Frame(self, bg="#0f172a", padx=40, pady=24)
        form.pack(fill="both", expand=True)

        fields = [
            ("MySQL Host", "host", DEFAULT_HOST),
            ("Port",       "port", DEFAULT_PORT),
            ("Database",   "db",   DEFAULT_DB),
            ("Username",   "user", DEFAULT_USER),
            ("Password",   "pass", ""),
        ]
        self._vars = {}
        for label, key, default in fields:
            row = tk.Frame(form, bg="#0f172a")
            row.pack(fill="x", pady=4)
            tk.Label(row, text=label, width=12, anchor="w", bg="#0f172a", fg="#94a3b8",
                     font=("Segoe UI", 10)).pack(side="left")
            var = tk.StringVar(value=default)
            self._vars[key] = var
            show = "*" if key == "pass" else ""
            e = ttk.Entry(row, textvariable=var, show=show, font=("Segoe UI", 10))
            e.pack(side="left", fill="x", expand=True)

        self._status = tk.Label(form, text="", bg="#0f172a", fg="#f87171", font=("Segoe UI", 9))
        self._status.pack(pady=(8, 0))

        btn = tk.Button(form, text="🔗 Connect to Database", bg="#3b82f6", fg="white",
                        font=("Segoe UI", 11, "bold"), relief="flat", pady=10, cursor="hand2",
                        command=self._connect)
        btn.pack(fill="x", pady=(12, 0))
        self.bind("<Return>", lambda e: self._connect())

    def _connect(self):
        self._status.config(text="Connecting...", fg="#93c5fd")
        self.update()
        def _do():
            try:
                db = DBConnection(
                    host=self._vars["host"].get(),
                    port=self._vars["port"].get(),
                    dbname=self._vars["db"].get(),
                    user=self._vars["user"].get(),
                    password=self._vars["pass"].get(),
                )
                self.after(0, lambda: self._success(db))
            except Exception as ex:
                self.after(0, lambda: self._status.config(text=f"Error: {ex}", fg="#f87171"))
        threading.Thread(target=_do, daemon=True).start()

    def _success(self, db):
        self.destroy()
        self.on_success(db)

    def _quit(self):
        self.parent.quit()
        self.parent.destroy()

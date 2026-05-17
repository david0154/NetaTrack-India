#!/usr/bin/env python3
"""
NetaTrack India — Python Desktop Admin App
Built with Tkinter + mysql-connector-python
Run: python main.py
"""

import tkinter as tk
from tkinter import ttk, messagebox
import threading
import os
import sys

# Add project root to path
sys.path.insert(0, os.path.dirname(__file__))

from ui.login_window import LoginWindow
from ui.main_window import MainWindow
from db.connection import DBConnection

APP_NAME = "NetaTrack India Admin"
APP_VERSION = "1.0.0"


class App:
    def __init__(self):
        self.root = tk.Tk()
        self.root.withdraw()  # hide until login
        self.root.title(APP_NAME)
        self.root.configure(bg="#0f172a")

        # Apply dark theme
        self._apply_theme()

        # Show login
        self.show_login()
        self.root.mainloop()

    def _apply_theme(self):
        style = ttk.Style()
        style.theme_use("clam")
        style.configure(".", background="#0f172a", foreground="#e2e8f0", font=("Segoe UI", 10))
        style.configure("TFrame", background="#0f172a")
        style.configure("TLabel", background="#0f172a", foreground="#e2e8f0")
        style.configure("TButton", background="#3b82f6", foreground="white", padding=8, font=("Segoe UI", 10, "bold"))
        style.map("TButton", background=[("active", "#2563eb")])
        style.configure("TEntry", fieldbackground="#1e293b", foreground="#f1f5f9", insertcolor="white")
        style.configure("Treeview", background="#1e293b", foreground="#e2e8f0", rowheight=28, fieldbackground="#1e293b")
        style.configure("Treeview.Heading", background="#0f172a", foreground="#64748b", font=("Segoe UI", 9, "bold"))
        style.map("Treeview", background=[("selected", "#3b82f6")])
        style.configure("TNotebook", background="#0f172a")
        style.configure("TNotebook.Tab", background="#1e293b", foreground="#94a3b8", padding=[14, 6])
        style.map("TNotebook.Tab", background=[("selected", "#3b82f6")], foreground=[("selected", "white")])
        style.configure("TCombobox", fieldbackground="#1e293b", foreground="#f1f5f9")
        style.configure("Vertical.TScrollbar", background="#1e293b", troughcolor="#0f172a")

    def show_login(self):
        LoginWindow(self.root, on_success=self.open_main)

    def open_main(self, db: DBConnection):
        MainWindow(self.root, db)


if __name__ == "__main__":
    App()

"""
NetaTrack India — Python Admin App
Requires: pip install -r requirements.txt
"""
import tkinter as tk
from tkinter import messagebox
import os
import sys

sys.path.insert(0, os.path.dirname(__file__))

from db.connection import DBConnection
from ui.connect_dialog import ConnectDialog
from ui.main_window import MainWindow

LOGO_URL = "https://raw.githubusercontent.com/david0154/NetaTrack-India/main/logo.png"


def load_logo(root: tk.Tk):
    local_paths = [
        os.path.join(os.path.dirname(__file__), '..', 'logo.png'),
        os.path.join(os.path.dirname(__file__), 'logo.png'),
    ]
    for path in local_paths:
        if os.path.exists(path):
            try:
                from PIL import Image, ImageTk
                img = Image.open(path).resize((32, 32), Image.LANCZOS)
                return ImageTk.PhotoImage(img)
            except ImportError:
                try:
                    return tk.PhotoImage(file=path).subsample(10, 10)
                except Exception:
                    pass
    try:
        import urllib.request, tempfile
        tmp = tempfile.NamedTemporaryFile(suffix='.png', delete=False)
        urllib.request.urlretrieve(LOGO_URL, tmp.name)
        try:
            from PIL import Image, ImageTk
            img = Image.open(tmp.name).resize((32, 32), Image.LANCZOS)
            return ImageTk.PhotoImage(img)
        except ImportError:
            return tk.PhotoImage(file=tmp.name).subsample(10, 10)
    except Exception:
        return None


def check_deps() -> bool:
    """Check required packages. mysql-connector is NOT needed."""
    missing = []
    try:
        import requests  # noqa
    except ImportError:
        missing.append("requests")
    try:
        import dotenv  # noqa  (python-dotenv)
    except ImportError:
        missing.append("python-dotenv")

    if missing:
        messagebox.showerror(
            "Missing packages",
            "Run this first:\n\n"
            f"pip install {' '.join(missing)}\n\n"
            "Then restart the app."
        )
        return False
    return True


def try_load_ai(root: tk.Tk, app: MainWindow):
    try:
        from auto.model_downloader import ensure_model_with_ui
        from auto.local_ai import set_sentiment_pipe

        def _on_done(pipe):
            if pipe:
                set_sentiment_pipe(pipe)
                app.set_status("\U0001f916 AI Ready", "#22c55e")
            else:
                app.set_status("\u26a0\ufe0f AI unavailable — rule-based only", "#f59e0b")

        ensure_model_with_ui(root, on_done=_on_done)
    except Exception:
        pass


def main():
    root = tk.Tk()
    root.withdraw()
    root.title("NetaTrack India")
    root.configure(bg="#0f172a")

    if not check_deps():
        root.destroy()
        return

    logo_img = load_logo(root)
    if logo_img:
        try:
            root.iconphoto(True, logo_img)
        except Exception:
            pass

    # Show API connect dialog (NOT MySQL)
    db = DBConnection()
    dialog = ConnectDialog(root, db)
    root.wait_window(dialog.window)

    if not db.is_connected():
        root.destroy()
        return

    app = MainWindow(root, db, logo_img=logo_img)
    root.after(800, lambda: try_load_ai(root, app))
    root.mainloop()


if __name__ == '__main__':
    main()

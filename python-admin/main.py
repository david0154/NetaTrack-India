"""
NetaTrack India — Python Admin App
AI model required (~60MB, CPU only, no C++, auto-downloaded on first run).
"""
import tkinter as tk
from tkinter import messagebox
import os
import sys
from db.connection import DBConnection
from ui.connect_dialog import ConnectDialog
from ui.main_window import MainWindow
from auto.model_downloader import (
    ensure_model_with_ui, _backend, install_backend_guide
)
from auto.local_ai import set_sentiment_pipe

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
        from PIL import Image, ImageTk
        img = Image.open(tmp.name).resize((32, 32), Image.LANCZOS)
        return ImageTk.PhotoImage(img)
    except Exception:
        return None


def check_backend(root: tk.Tk) -> bool:
    """Check torch/onnx installed. Show error if missing. Returns True if OK."""
    try:
        import transformers  # noqa
    except ImportError:
        messagebox.showerror(
            "Missing: transformers",
            "Run: pip install transformers\nThen restart the app."
        )
        return False

    if _backend() == 'none':
        messagebox.showerror(
            "Missing: PyTorch or ONNX Runtime",
            install_backend_guide()
        )
        return False
    return True


def main():
    root = tk.Tk()
    root.withdraw()
    root.title("NetaTrack India — Admin")
    root.configure(bg="#0f172a")

    logo_img = load_logo(root)
    if logo_img:
        try:
            root.iconphoto(True, logo_img)
        except Exception:
            pass

    # --- Step 1: Check backend installed ---------------------------------
    if not check_backend(root):
        root.destroy()
        sys.exit(1)

    # --- Step 2: DB connect ----------------------------------------------
    db = DBConnection()
    dialog = ConnectDialog(root, db)
    root.wait_window(dialog.window)
    if not db.is_connected():
        root.destroy()
        return

    # --- Step 3: Show main window (immediately usable) -------------------
    app = MainWindow(root, db, logo_img=logo_img)

    # --- Step 4: Download/load AI model (shows progress if first time) ---
    def _on_model_ready(pipe):
        if pipe is None:
            messagebox.showwarning(
                "AI Model Failed",
                "Could not load sentiment model.\n"
                "Analysis will use rule-based fallback.\n"
                "Check internet connection and try again."
            )
        else:
            set_sentiment_pipe(pipe)
            app.set_status("🤖 AI Ready — sentiment model loaded", "#22c55e")

    ensure_model_with_ui(root, on_done=_on_model_ready)

    root.mainloop()


if __name__ == '__main__':
    main()

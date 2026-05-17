"""
NetaTrack India — Python Desktop Admin App
No C++ required. Works on any Python 3.8+ environment.
"""
import tkinter as tk
from tkinter import messagebox
import os
from db.connection import DBConnection
from ui.connect_dialog import ConnectDialog
from ui.main_window import MainWindow
from auto.model_downloader import ensure_models, install_guide, _check_transformers, _check_torch_or_onnx

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


def check_ai_setup(root: tk.Tk):
    """Show a simple dialog if AI is not set up. Non-blocking."""
    if not _check_transformers() or _check_torch_or_onnx() == 'none':
        if messagebox.askyesno(
            "AI Setup (Optional)",
            "Transformers/PyTorch not found.\n\n"
            "The app works fine without it (rule-based AI).\n\n"
            "Install for better accuracy?\n\n"
            + install_guide(),
            icon="info"
        ):
            # User wants to install — show instructions
            messagebox.showinfo(
                "Install AI",
                "Run in terminal:\n\n"
                "pip install torch --index-url https://download.pytorch.org/whl/cpu\n"
                "pip install transformers\n\n"
                "Then restart the app."
            )
    else:
        # AI available — download model in background silently
        ensure_models(background=True)


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

    # DB connect
    db = DBConnection()
    dialog = ConnectDialog(root, db)
    root.wait_window(dialog.window)

    if not db.is_connected():
        root.destroy()
        return

    # Check AI setup in background (non-blocking)
    root.after(1500, lambda: check_ai_setup(root))

    app = MainWindow(root, db, logo_img=logo_img)
    root.mainloop()


if __name__ == '__main__':
    main()

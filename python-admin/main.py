"""NetaTrack India — Python Desktop Admin App."""
import tkinter as tk
from tkinter import ttk, messagebox
import os
from db.connection import DBConnection
from ui.connect_dialog import ConnectDialog
from ui.main_window import MainWindow

LOGO_URL = "https://raw.githubusercontent.com/david0154/NetaTrack-India/main/logo.png"


def load_logo(root: tk.Tk):
    """Load logo from local file or download from GitHub."""
    # Check local path (repo root, one level up)
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

    # Download from GitHub if not found locally
    try:
        import urllib.request, tempfile
        tmp = tempfile.NamedTemporaryFile(suffix='.png', delete=False)
        urllib.request.urlretrieve(LOGO_URL, tmp.name)
        from PIL import Image, ImageTk
        img = Image.open(tmp.name).resize((32, 32), Image.LANCZOS)
        return ImageTk.PhotoImage(img)
    except Exception:
        return None


def main():
    root = tk.Tk()
    root.withdraw()
    root.title("NetaTrack India — Admin")
    root.configure(bg="#0f172a")

    # Set window icon
    logo_img = load_logo(root)
    if logo_img:
        try:
            root.iconphoto(True, logo_img)
        except Exception:
            pass

    # Show DB connect dialog
    db = DBConnection()
    dialog = ConnectDialog(root, db)
    root.wait_window(dialog.window)

    if not db.is_connected():
        root.destroy()
        return

    # Open main window with logo
    app = MainWindow(root, db, logo_img=logo_img)
    root.mainloop()


if __name__ == '__main__':
    main()

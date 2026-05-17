"""
NetaTrack India — Required AI Model Downloader

Model used: cardiffnlp/twitter-roberta-base-sentiment-latest
Size      : ~60MB (CPU, no GPU, no C++)
Backend   : torch (CPU) OR onnxruntime — whichever is installed

Called automatically on first app launch.
Shows a Tkinter progress window while downloading.
Once cached in ~/.cache/huggingface/ — never downloads again.
"""
import os
import threading
import tkinter as tk
from tkinter import ttk

PRIMARY_MODEL  = "cardiffnlp/twitter-roberta-base-sentiment-latest"
FALLBACK_MODEL = "distilbert-base-uncased-finetuned-sst-2-english"


def _is_cached(model_name: str) -> bool:
    """Check if model already downloaded in HuggingFace cache."""
    cache = os.path.join(os.path.expanduser('~'), '.cache', 'huggingface', 'hub')
    if not os.path.exists(cache):
        return False
    safe = model_name.replace('/', '--')
    return any(safe in d for d in os.listdir(cache))


def _backend() -> str:
    """Returns 'torch', 'onnx', or 'none'."""
    try:
        import torch; return 'torch'  # noqa
    except ImportError:
        pass
    try:
        import onnxruntime; return 'onnx'  # noqa
    except ImportError:
        return 'none'


def _download_model(log_fn):
    """
    Actually load/download the model pipeline.
    Returns the pipeline object or None on failure.
    """
    from transformers import pipeline
    for model in (PRIMARY_MODEL, FALLBACK_MODEL):
        try:
            log_fn(f"Loading: {model}")
            pipe = pipeline(
                "text-classification",
                model=model,
                truncation=True,
                max_length=128,
                device=-1,          # CPU always
            )
            log_fn(f"✓ Ready: {model}")
            return pipe
        except Exception as e:
            log_fn(f"Failed ({model}): {e}")
    return None


def ensure_model_with_ui(parent: tk.Tk, on_done):
    """
    Shows a progress window, downloads model in background thread.
    Calls on_done(pipe) when complete (pipe=None if failed).
    Safe to call every launch — skips if already cached.
    """
    # Already cached — load silently in background
    if _is_cached(PRIMARY_MODEL) or _is_cached(FALLBACK_MODEL):
        def _silent():
            try:
                from transformers import pipeline
                pipe = pipeline("text-classification",
                                model=PRIMARY_MODEL if _is_cached(PRIMARY_MODEL) else FALLBACK_MODEL,
                                truncation=True, max_length=128, device=-1)
                parent.after(0, lambda: on_done(pipe))
            except Exception:
                parent.after(0, lambda: on_done(None))
        threading.Thread(target=_silent, daemon=True).start()
        return

    # Not cached — show download progress window
    win = tk.Toplevel(parent)
    win.title("NetaTrack AI — Downloading Model")
    win.configure(bg="#0f172a")
    win.geometry("460x220")
    win.resizable(False, False)
    win.grab_set()
    # Center
    win.update_idletasks()
    x = parent.winfo_rootx() + (parent.winfo_width() - 460) // 2
    y = parent.winfo_rooty() + (parent.winfo_height() - 220) // 2
    win.geometry(f"+{max(0,x)}+{max(0,y)}")

    tk.Label(win, text="🤖  Downloading AI Model",
             font=("Segoe UI", 13, "bold"),
             bg="#0f172a", fg="#f8fafc").pack(pady=(22, 4))
    tk.Label(win, text="cardiffnlp sentiment model  (~60 MB)  —  one-time download",
             font=("Segoe UI", 9), bg="#0f172a", fg="#94a3b8").pack()

    bar = ttk.Progressbar(win, mode="indeterminate", length=380)
    bar.pack(pady=14)
    bar.start(12)

    status_var = tk.StringVar(value="Connecting to HuggingFace...")
    tk.Label(win, textvariable=status_var,
             font=("Segoe UI", 9), bg="#0f172a", fg="#60a5fa",
             wraplength=420).pack()

    tk.Label(win,
             text="No C++ required • CPU only • Cached after first download",
             font=("Segoe UI", 8), bg="#0f172a", fg="#475569").pack(pady=(10, 0))

    def _run():
        def _log(msg):
            try:
                win.after(0, lambda: status_var.set(msg))
            except Exception:
                pass

        pipe = _download_model(_log)

        def _finish():
            try:
                bar.stop()
                win.destroy()
            except Exception:
                pass
            on_done(pipe)

        try:
            win.after(0, _finish)
        except Exception:
            on_done(pipe)

    threading.Thread(target=_run, daemon=True).start()


def install_backend_guide() -> str:
    """Instructions shown if torch/onnx not installed."""
    return (
        "PyTorch (CPU) or ONNX Runtime is required for AI.\n\n"
        "Run ONE of these commands, then restart:\n\n"
        "Option A — PyTorch CPU (recommended):\n"
        "  pip install torch --index-url https://download.pytorch.org/whl/cpu\n\n"
        "Option B — ONNX Runtime (lighter, ~15MB):\n"
        "  pip install onnxruntime\n\n"
        "No C++ / No Visual Studio needed for either option."
    )

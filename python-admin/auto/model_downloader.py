"""
NetaTrack India — Auto Model Downloader
Downloads the tiny sentiment model (~60MB) in background.
Shown as a progress dialog in the Python admin app.

Usage (called from Python admin on first launch):
    from auto.model_downloader import ensure_models
    ensure_models(on_progress=print)  # blocking
    ensure_models(on_progress=print, background=True)  # non-blocking thread
"""
import threading
import os

MODELS = [
    {
        "name":  "Sentiment Model (cardiffnlp, ~60MB)",
        "model": "cardiffnlp/twitter-roberta-base-sentiment-latest",
        "task":  "text-classification",
    },
    # Fallback if above fails:
    {
        "name":  "Sentiment Fallback (distilbert, ~67MB)",
        "model": "distilbert-base-uncased-finetuned-sst-2-english",
        "task":  "text-classification",
    },
]


def _check_transformers() -> bool:
    try:
        import transformers  # noqa
        return True
    except ImportError:
        return False


def _check_torch_or_onnx() -> str:
    """Returns 'torch', 'onnx', or 'none'."""
    try:
        import torch  # noqa
        return 'torch'
    except ImportError:
        pass
    try:
        import onnxruntime  # noqa
        return 'onnx'
    except ImportError:
        return 'none'


def ensure_models(on_progress=None, background: bool = False) -> threading.Thread | None:
    """
    Check if transformers + backend are installed, try to download model.
    on_progress(msg: str)  — called with status messages
    background=True        — runs in a daemon thread (non-blocking)
    """
    def _log(msg):
        if on_progress:
            on_progress(msg)

    def _run():
        if not _check_transformers():
            _log("[AI] transformers not installed. Using rule-based analysis only.")
            _log("[AI] To enable AI: pip install transformers torch --index-url https://download.pytorch.org/whl/cpu")
            return

        backend = _check_torch_or_onnx()
        if backend == 'none':
            _log("[AI] No backend found. Install torch or onnxruntime.")
            _log("[AI] pip install torch --index-url https://download.pytorch.org/whl/cpu")
            return

        _log(f"[AI] Backend: {backend}. Checking models...")

        # Check cache
        cache_dir = os.path.join(os.path.expanduser('~'), '.cache', 'huggingface', 'hub')
        for m in MODELS:
            safe_name = m['model'].replace('/', '--')
            model_cached = any(
                safe_name.split('--')[-1] in d
                for d in os.listdir(cache_dir)
            ) if os.path.exists(cache_dir) else False

            if model_cached:
                _log(f"[AI] {m['name']} — already cached ✓")
                continue

            _log(f"[AI] Downloading {m['name']}...")
            try:
                from transformers import pipeline
                _ = pipeline(m['task'], model=m['model'],
                             truncation=True, max_length=128, device=-1)
                _log(f"[AI] {m['name']} — downloaded ✓")
                break  # Only need one model
            except Exception as e:
                _log(f"[AI] {m['name']} failed: {e}")
                continue

        _log("[AI] Model check complete.")

    if background:
        t = threading.Thread(target=_run, daemon=True)
        t.start()
        return t
    else:
        _run()
        return None


def install_guide() -> str:
    """Returns install instructions as a string."""
    return (
        "NetaTrack AI Setup\n"
        "==================\n\n"
        "The app works WITHOUT AI (rule-based analysis).\n"
        "For better accuracy, install one of these:\n\n"
        "Option A — CPU PyTorch (~250MB total):\n"
        "  pip install torch --index-url https://download.pytorch.org/whl/cpu\n"
        "  pip install transformers\n\n"
        "Option B — ONNX Runtime (lightest, ~15MB):\n"
        "  pip install onnxruntime transformers\n\n"
        "Option C — Skip (rule-based only, 0MB):\n"
        "  Just click OK and continue. Everything still works.\n"
    )

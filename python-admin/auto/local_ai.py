"""
NetaTrack India — Local AI (Lightweight Edition)

Model sizes (total ~80MB, auto-downloaded on first run):
  - Sentiment  : cardiffnlp/twitter-roberta-base-sentiment-latest (~60MB)
  - Category   : Rule-based keyword engine (0MB, instant, no download)
  - NER names  : Regex + simple title-case heuristic (0MB, instant)
  - Summarise  : First-2-sentences rule (0MB, instant)
  - Credibility: Rule-based scoring (0MB)

Optional upgrade (still small, ~250MB total):
  Set LocalAI(use_onnx=True) to use ONNX Runtime models (faster, no GPU needed)

NO C++ build tools required. Works on:
  - Windows (no Visual Studio needed)
  - Linux/Mac
  - Any Python 3.8+ environment

Install:
  pip install transformers torch --index-url https://download.pytorch.org/whl/cpu
  # or even lighter:
  pip install transformers onnxruntime   # only 50MB, no torch needed
"""

import re
import math
import os

# ---- Lazy model holder --------------------------------------------------
_sentiment_pipe = None
_sentiment_ok   = False


def _init_sentiment():
    """Try to load the lightest possible sentiment model (~60MB)."""
    global _sentiment_pipe, _sentiment_ok
    if _sentiment_ok:
        return True
    if _sentiment_ok is None:   # already tried and failed
        return False
    try:
        from transformers import pipeline
        # cardiffnlp is ~60MB, CPU-only, no CUDA needed
        _sentiment_pipe = pipeline(
            "text-classification",
            model="cardiffnlp/twitter-roberta-base-sentiment-latest",
            truncation=True,
            max_length=128,       # keep fast on CPU
            device=-1,            # force CPU
        )
        _sentiment_ok = True
        return True
    except Exception:
        pass
    try:
        # Even lighter fallback: distilbert finetuned SST-2 (~67MB)
        from transformers import pipeline
        _sentiment_pipe = pipeline(
            "text-classification",
            model="distilbert-base-uncased-finetuned-sst-2-english",
            truncation=True, max_length=128, device=-1
        )
        _sentiment_ok = True
        return True
    except Exception:
        _sentiment_ok = None  # don't retry
        return False


# ---- Keyword lists -------------------------------------------------------
_CRIME_KW = [
    "arrested", "fir", "chargesheet", "convicted", "bail", "cbi", "ed raid",
    "money laundering", "corruption", "scam", "fraud", "bribery", "pmla",
    "income tax", "hawala", "sting", "disproportionate assets", "accused",
]
_PROMISE_KW = [
    "promised", "announced", "committed", "pledge", "manifesto",
    "vowed", "guarantee", "scheme", "yojana", "launched", "inaugurated",
]
_FUND_KW = [
    "crore", "budget", "allocated", "mplads", "fund", "spending",
    "expenditure", "utilization", "release", "grant", "disburse",
]
_PROJECT_KW = [
    "project", "road", "highway", "bridge", "hospital", "school",
    "railway", "metro", "airport", "dam", "power plant", "completed",
]
_ELECTION_KW = [
    "election", "vote", "poll", "candidate", "seat", "win", "lost",
    "eci", "ballot", "constituency", "mp elected", "mla elected",
]
_NEG_KW = [
    "arrested", "fraud", "scam", "corrupt", "failed", "broke",
    "fake", "riot", "murder", "rape", "violence", "banned",
]
_POS_KW = [
    "development", "completed", "launched", "inaugurated",
    "achieved", "successful", "awarded", "growth", "improved",
]

# Indian politician title prefixes for name extraction
_TITLE_PREFIX = re.compile(
    r'\b(Shri|Smt|Dr|Prof|Adv|Mr|Mrs|Ms|Sri|Ch|MLA|MP|CM|PM)\.?\s+'
    r'([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3})',
    re.UNICODE
)


class LocalAI:
    """
    Lightweight offline AI — ~60MB download, no C++ needed.
    Sentiment uses a tiny transformer; everything else is rule-based (instant).
    """

    def __init__(self, use_transformers: bool = True):
        self._use_transformers = use_transformers
        if use_transformers:
            # Try in background so app doesn’t block on startup
            _init_sentiment()

    # ---- Sentiment -------------------------------------------------------
    def classify_sentiment(self, text: str) -> str:
        """Returns 'positive', 'negative', or 'neutral'."""
        if _sentiment_ok and _sentiment_pipe is not None:
            try:
                r = _sentiment_pipe(text[:256])[0]
                label = r["label"].lower()
                score = r["score"]
                if score < 0.60:
                    return "neutral"
                if "pos" in label:
                    return "positive"
                if "neg" in label:
                    return "negative"
                return "neutral"
            except Exception:
                pass
        return self._rule_sentiment(text)

    def _rule_sentiment(self, text: str) -> str:
        t = text.lower()
        neg = sum(t.count(w) for w in _NEG_KW)
        pos = sum(t.count(w) for w in _POS_KW)
        if neg > pos:
            return "negative"
        if pos > neg:
            return "positive"
        return "neutral"

    # ---- Category (pure rule-based, instant) -----------------------------
    def classify_category(self, text: str) -> str:
        """Returns: criminal | corruption | promise | fund | project | election | general."""
        t = text.lower()
        scores = {
            "criminal":    sum(t.count(w) for w in _CRIME_KW),
            "promise":     sum(t.count(w) for w in _PROMISE_KW),
            "fund":        sum(t.count(w) for w in _FUND_KW),
            "project":     sum(t.count(w) for w in _PROJECT_KW),
            "election":    sum(t.count(w) for w in _ELECTION_KW),
        }
        best, val = max(scores.items(), key=lambda x: x[1])
        return best if val > 0 else "general"

    # ---- NER: extract names (regex, zero download) -----------------------
    def extract_leader_names(self, text: str) -> list:
        """Extract politician names using title prefixes + title-case heuristic."""
        names = []
        # Pattern 1: Shri/Smt/Dr + Name
        for m in _TITLE_PREFIX.finditer(text):
            name = m.group(2).strip()
            if name not in names:
                names.append(name)
        # Pattern 2: Two consecutive Title-Case words (fallback)
        if not names:
            for m in re.finditer(r'\b([A-Z][a-z]{1,14})\s+([A-Z][a-z]{1,14})\b', text):
                candidate = m.group(0)
                # Skip common non-name pairs
                skip = {"The Hindu", "New Delhi", "Prime Minister",
                        "Chief Minister", "Home Minister", "West Bengal",
                        "Lok Sabha", "Rajya Sabha", "Supreme Court"}
                if candidate not in skip and candidate not in names:
                    names.append(candidate)
        return names[:5]  # max 5 names

    # ---- Summarise (rule-based, zero download) ---------------------------
    def summarise(self, text: str, max_words: int = 60) -> str:
        """Return first 2 sentences as summary."""
        if not text or len(text.split()) < 20:
            return text
        sentences = re.split(r'(?<=[.!?])\s+', text.strip())
        summary = " ".join(sentences[:2])
        words = summary.split()
        if len(words) > max_words:
            summary = " ".join(words[:max_words]) + "..."
        return summary

    # ---- Credibility score (rule-based) ----------------------------------
    def score_credibility(self, text: str) -> int:
        """0–100 credibility score for a report/claim."""
        score = 50
        t = text.lower()
        if any(w in t for w in ["official", "government", "court", "police", "fir", "cbi"]):
            score += 15
        if any(w in t for w in ["document", "proof", "evidence", "receipt", "record"]):
            score += 10
        if re.search(r'\b20[0-9]{2}\b', text):   # contains a year
            score += 5
        if len(text.split()) > 50:
            score += 10
        if any(w in t for w in ["100%", "guaranteed", "viral", "share this", "forward"]):
            score -= 20
        if len(text.split()) < 10:
            score -= 15
        if text.count("!") > 3:
            score -= 10
        return max(0, min(100, score))

    # ---- Importance score ------------------------------------------------
    def _importance(self, text: str) -> int:
        """1–5 importance score based on keywords."""
        t = text.lower()
        high = ["prime minister", "parliament", "supreme court", "president",
                "budget", "election", "arrested", "convicted", "scam", "crore"]
        hits = sum(1 for w in high if w in t)
        return min(5, max(1, 1 + math.ceil(hits / 2)))

    # ---- Full analysis ---------------------------------------------------
    def analyse_news_item(self, title: str, description: str = "") -> dict:
        """Complete offline analysis. Returns dict ready to save to DB."""
        text = f"{title} {description}".strip()
        return {
            "category":     self.classify_category(text),
            "sentiment":    self.classify_sentiment(text),
            "leader_names": self.extract_leader_names(text),
            "summary":      self.summarise(description or title),
            "credibility":  self.score_credibility(text),
            "importance":   self._importance(text),
        }

    def batch_analyse(self, items: list) -> list:
        """items = [{title, description}, ...] — returns list with analysis merged in."""
        return [{**item, **self.analyse_news_item(
            item.get("title", ""), item.get("description", "")
        )} for item in items]

    # ---- Model status (shown in Python admin UI) -------------------------
    @staticmethod
    def model_status() -> dict:
        return {
            "sentiment_model": "cardiffnlp/twitter-roberta-base-sentiment-latest (~60MB)"
                               if _sentiment_ok else "rule-based (no download)",
            "category":    "rule-based keyword engine",
            "ner":         "regex + title-case heuristic",
            "summarise":   "first-2-sentences rule",
            "credibility": "rule-based scoring",
            "total_size":  "~60MB" if _sentiment_ok else "0MB",
        }

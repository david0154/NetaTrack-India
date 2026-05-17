"""
NetaTrack India — Local Pretrained AI Analyser
Runs OFFLINE using small HuggingFace models:
  - Classification : distilbert-base-uncased-finetuned-sst-2-english  (~67MB)
  - Summarisation  : sshleifer/distilbart-cnn-6-6                     (~300MB)
  - NER (names)    : dbmdz/bert-large-cased-finetuned-conll03-english (~400MB)
  - Zero-shot      : facebook/bart-large-mnli                         (~1.6GB) [optional]

All models are auto-downloaded on first run and cached locally.
No internet required after first download.

Usage:
    local = LocalAI()
    local.classify_sentiment(text)     # 'positive' / 'negative'
    local.classify_category(text)      # 'criminal'/'promise'/'fund'/...
    local.extract_leader_names(text)   # ['Narendra Modi', 'Rahul Gandhi']
    local.summarise(text)              # short summary string
    local.score_credibility(text)      # 0-100 credibility score
"""

import re
import math

# Lazy imports — only load when first used
_sentiment_pipe = None
_ner_pipe = None
_summarise_pipe = None
_zero_shot_pipe = None


def _get_sentiment():
    global _sentiment_pipe
    if _sentiment_pipe is None:
        from transformers import pipeline
        _sentiment_pipe = pipeline(
            "sentiment-analysis",
            model="distilbert-base-uncased-finetuned-sst-2-english",
            truncation=True, max_length=512
        )
    return _sentiment_pipe


def _get_ner():
    global _ner_pipe
    if _ner_pipe is None:
        from transformers import pipeline
        _ner_pipe = pipeline(
            "ner",
            model="dbmdz/bert-large-cased-finetuned-conll03-english",
            aggregation_strategy="simple",
            truncation=True, max_length=512
        )
    return _ner_pipe


def _get_summariser():
    global _summarise_pipe
    if _summarise_pipe is None:
        from transformers import pipeline
        _summarise_pipe = pipeline(
            "summarization",
            model="sshleifer/distilbart-cnn-6-6",
            truncation=True
        )
    return _summarise_pipe


def _get_zero_shot():
    global _zero_shot_pipe
    if _zero_shot_pipe is None:
        from transformers import pipeline
        _zero_shot_pipe = pipeline(
            "zero-shot-classification",
            model="typeform/distilbart-mnli-12-3",  # lighter: ~250MB
            truncation=True
        )
    return _zero_shot_pipe


class LocalAI:
    """
    Offline AI analyser — uses small pretrained models.
    Falls back to rule-based analysis if transformers not installed.
    """

    CATEGORIES = [
        "criminal case", "corruption", "promise fulfillment",
        "fund allocation", "project completion", "election", "general news"
    ]

    CRIME_WORDS = [
        "arrested", "fir", "chargesheet", "convicted", "bail", "cbi", "ed raid",
        "money laundering", "corruption", "scam", "fraud", "bribery", "pmla",
        "income tax", "hawala", "sting", "disproportionate assets"
    ]
    PROMISE_WORDS = ["promised", "announced", "committed", "pledge", "manifesto",
                     "vowed", "guarantee", "scheme", "yojana", "launched"]
    FUND_WORDS    = ["crore", "budget", "allocated", "mplads", "fund", "spending",
                     "expenditure", "utilization", "release", "grant"]

    def __init__(self, use_transformers: bool = True):
        self._use_transformers = use_transformers
        self._transformers_ok = False
        if use_transformers:
            try:
                import transformers
                self._transformers_ok = True
            except ImportError:
                pass

    # ── Sentiment ─────────────────────────────────────────────────────────────
    def classify_sentiment(self, text: str) -> str:
        """Returns 'positive', 'negative', or 'neutral'."""
        if self._transformers_ok:
            try:
                result = _get_sentiment()(text[:512])
                label = result[0]["label"].lower()
                score = result[0]["score"]
                if score < 0.65:
                    return "neutral"
                return "positive" if "positive" in label else "negative"
            except Exception:
                pass
        return self._rule_sentiment(text)

    def _rule_sentiment(self, text: str) -> str:
        t = text.lower()
        neg = sum(t.count(w) for w in ["arrested", "fraud", "scam", "corrupt",
                                         "failed", "broke", "fake", "riot", "murder"])
        pos = sum(t.count(w) for w in ["development", "completed", "launched",
                                         "inaugurated", "achieved", "successful"])
        if neg > pos: return "negative"
        if pos > neg: return "positive"
        return "neutral"

    # ── Category classification ────────────────────────────────────────────────
    def classify_category(self, text: str) -> str:
        """Returns one of: criminal, corruption, promise, fund, project, election, general."""
        if self._transformers_ok:
            try:
                result = _get_zero_shot()(
                    text[:512],
                    candidate_labels=self.CATEGORIES
                )
                top = result["labels"][0]
                score = result["scores"][0]
                if score < 0.4:
                    return self._rule_category(text)
                mapping = {
                    "criminal case": "criminal",
                    "corruption": "corruption",
                    "promise fulfillment": "promise",
                    "fund allocation": "fund",
                    "project completion": "project",
                    "election": "election",
                    "general news": "general",
                }
                return mapping.get(top, "general")
            except Exception:
                pass
        return self._rule_category(text)

    def _rule_category(self, text: str) -> str:
        t = text.lower()
        crime  = sum(t.count(w) for w in self.CRIME_WORDS)
        promise = sum(t.count(w) for w in self.PROMISE_WORDS)
        fund   = sum(t.count(w) for w in self.FUND_WORDS)
        scores = {"criminal": crime, "promise": promise, "fund": fund}
        best = max(scores, key=scores.get)
        return best if scores[best] > 0 else "general"

    # ── Named Entity Recognition (extract politician names) ───────────────────
    def extract_leader_names(self, text: str) -> list:
        """Extract person names from text using NER."""
        if self._transformers_ok:
            try:
                entities = _get_ner()(text[:512])
                names = []
                for e in entities:
                    if e.get("entity_group") == "PER" and e.get("score", 0) > 0.80:
                        name = e["word"].strip()
                        if len(name) > 3 and name not in names:
                            names.append(name)
                return names
            except Exception:
                pass
        return self._rule_extract_names(text)

    def _rule_extract_names(self, text: str) -> list:
        """Regex: find Title-Case word pairs like 'Narendra Modi'."""
        return re.findall(r'\b[A-Z][a-z]+ [A-Z][a-z]+\b', text)

    # ── Summarisation ──────────────────────────────────────────────────────────
    def summarise(self, text: str, max_words: int = 60) -> str:
        """Return a short summary of the text."""
        if len(text.split()) < 30:
            return text  # already short
        if self._transformers_ok:
            try:
                result = _get_summariser()(
                    text[:1024],
                    max_length=max_words * 2,
                    min_length=20,
                    do_sample=False
                )
                return result[0]["summary_text"].strip()
            except Exception:
                pass
        # Rule-based: return first 2 sentences
        sentences = re.split(r'(?<=[.!?])\s+', text.strip())
        return " ".join(sentences[:2])

    # ── Credibility Score (rule-based + sentiment) ────────────────────────────
    def score_credibility(self, text: str) -> int:
        """
        0–100 credibility score for a public report/claim.
        Higher = more credible.
        """
        score = 50
        t = text.lower()
        # Positive signals
        if any(w in t for w in ["official", "government", "court", "police", "fir", "cbi"]):
            score += 15
        if any(w in t for w in ["document", "proof", "evidence", "receipt", "record"]):
            score += 10
        if re.search(r'\d{4}', text):  # has year
            score += 5
        if len(text.split()) > 50:  # detailed
            score += 10
        # Negative signals (spam/fake)
        if any(w in t for w in ["100%", "guaranteed", "viral", "share this", "forward"]):
            score -= 20
        if len(text.split()) < 10:  # too short
            score -= 15
        exclamations = text.count("!")
        if exclamations > 3:
            score -= 10
        return max(0, min(100, score))

    # ── Full analysis of a news item ──────────────────────────────────────────
    def analyse_news_item(self, title: str, description: str = "") -> dict:
        """
        Complete offline analysis of a news headline + description.
        Returns dict ready to save to DB.
        """
        text = f"{title} {description}".strip()
        return {
            "category":    self.classify_category(text),
            "sentiment":   self.classify_sentiment(text),
            "leader_names": self.extract_leader_names(text),
            "summary":     self.summarise(description or title),
            "credibility": self.score_credibility(text),
            "importance":  self._importance(text),
        }

    def _importance(self, text: str) -> int:
        """1–5 importance score."""
        t = text.lower()
        high = ["prime minister", "parliament", "supreme court", "president", "budget",
                "election", "arrested", "convicted", "scam", "crore"]
        hits = sum(1 for w in high if w in t)
        return min(5, max(1, 1 + math.ceil(hits / 2)))

    # ── Batch analyse a list of items ──────────────────────────────────────────
    def batch_analyse(self, items: list) -> list:
        """
        items = [{"title": ..., "description": ...}, ...]
        Returns list of analysis dicts.
        """
        results = []
        for item in items:
            analysis = self.analyse_news_item(
                item.get("title", ""),
                item.get("description", "")
            )
            results.append({**item, **analysis})
        return results

"""
NetaTrack India — Local AI

Requires the sentiment pipeline loaded via model_downloader.ensure_model_with_ui().
Everything else (category, NER, summarise, credibility) is rule-based — instant, 0MB.

Sentiment model: cardiffnlp/twitter-roberta-base-sentiment-latest (~60MB)
Backend        : torch CPU  OR  onnxruntime  (no C++, no GPU)
"""
import re
import math

# Global pipeline set by main.py after download completes
_sentiment_pipe = None


def set_sentiment_pipe(pipe):
    """Called from main.py once model is loaded."""
    global _sentiment_pipe
    _sentiment_pipe = pipe


# ---- Keyword lists -------------------------------------------------------
_CRIME_KW   = ["arrested","fir","chargesheet","convicted","bail","cbi","ed raid",
               "money laundering","corruption","scam","fraud","bribery","pmla",
               "income tax raid","hawala","disproportionate assets","accused"]
_PROMISE_KW = ["promised","announced","committed","pledge","manifesto",
               "vowed","guarantee","scheme","yojana","launched","inaugurated"]
_FUND_KW    = ["crore","budget","allocated","mplads","fund","spending",
               "expenditure","utilization","release","grant","disburse"]
_PROJECT_KW = ["project","road","highway","bridge","hospital","school",
               "railway","metro","airport","dam","power plant","completed"]
_ELECTION_KW= ["election","vote","poll","candidate","seat","win","lost",
               "eci","ballot","constituency","mp elected","mla elected"]
_NEG_KW     = ["arrested","fraud","scam","corrupt","failed","riot",
               "murder","rape","violence","banned","fake","broke"]
_POS_KW     = ["development","completed","launched","inaugurated",
               "achieved","successful","awarded","growth","improved"]

_TITLE_RE = re.compile(
    r'\b(?:Shri|Smt|Dr|Prof|Adv|Mr|Mrs|Sri|CM|PM|MP|MLA)\.?\s+'
    r'([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3})',
    re.UNICODE
)


class LocalAI:
    """
    AI analyser — sentiment uses the loaded ~60MB model,
    everything else is instant rule-based (0MB).
    """

    # ---- Sentiment -------------------------------------------------------
    def classify_sentiment(self, text: str) -> str:
        if _sentiment_pipe is not None:
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
        # Fallback rule-based
        t = text.lower()
        neg = sum(t.count(w) for w in _NEG_KW)
        pos = sum(t.count(w) for w in _POS_KW)
        return "negative" if neg > pos else ("positive" if pos > neg else "neutral")

    # ---- Category (rule-based, instant) ----------------------------------
    def classify_category(self, text: str) -> str:
        t = text.lower()
        scores = {
            "criminal": sum(t.count(w) for w in _CRIME_KW),
            "promise":  sum(t.count(w) for w in _PROMISE_KW),
            "fund":     sum(t.count(w) for w in _FUND_KW),
            "project":  sum(t.count(w) for w in _PROJECT_KW),
            "election": sum(t.count(w) for w in _ELECTION_KW),
        }
        best, val = max(scores.items(), key=lambda x: x[1])
        return best if val > 0 else "general"

    # ---- NER: politician names (regex, instant) --------------------------
    def extract_leader_names(self, text: str) -> list:
        names = []
        for m in _TITLE_RE.finditer(text):
            name = m.group(1).strip()
            if name not in names:
                names.append(name)
        if not names:
            skip = {"The Hindu","New Delhi","Prime Minister","Chief Minister",
                    "Home Minister","West Bengal","Lok Sabha","Rajya Sabha"}
            for m in re.finditer(r'\b([A-Z][a-z]{1,14})\s+([A-Z][a-z]{1,14})\b', text):
                c = m.group(0)
                if c not in skip and c not in names:
                    names.append(c)
        return names[:5]

    # ---- Summarise (rule-based, instant) ---------------------------------
    def summarise(self, text: str, max_words: int = 60) -> str:
        if not text or len(text.split()) < 20:
            return text
        sentences = re.split(r'(?<=[.!?])\s+', text.strip())
        summary = " ".join(sentences[:2])
        words = summary.split()
        return " ".join(words[:max_words]) + ("..." if len(words) > max_words else "")

    # ---- Credibility (rule-based) ----------------------------------------
    def score_credibility(self, text: str) -> int:
        score = 50
        t = text.lower()
        if any(w in t for w in ["official","government","court","police","fir","cbi"]):
            score += 15
        if any(w in t for w in ["document","proof","evidence","receipt","record"]):
            score += 10
        if re.search(r'\b20[0-9]{2}\b', text): score += 5
        if len(text.split()) > 50: score += 10
        if any(w in t for w in ["100%","guaranteed","viral","share this","forward"]):
            score -= 20
        if len(text.split()) < 10: score -= 15
        if text.count("!") > 3: score -= 10
        return max(0, min(100, score))

    # ---- Importance (rule-based) -----------------------------------------
    def _importance(self, text: str) -> int:
        t = text.lower()
        hits = sum(1 for w in ["prime minister","parliament","supreme court",
                               "president","budget","election","arrested",
                               "convicted","scam","crore"] if w in t)
        return min(5, max(1, 1 + math.ceil(hits / 2)))

    # ---- Full analysis ---------------------------------------------------
    def analyse_news_item(self, title: str, description: str = "") -> dict:
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
        return [{**item, **self.analyse_news_item(
            item.get("title",""), item.get("description","")
        )} for item in items]

    @staticmethod
    def is_ready() -> bool:
        """True if sentiment model is loaded."""
        return _sentiment_pipe is not None

# 🇮🇳 NetaTrack India

> **India's political accountability platform** — track politicians, monitor promises, detect corruption, analyse funds, and rate leaders using AI.

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php)](https://php.net)
[![Python](https://img.shields.io/badge/Python-3.10+-3776AB?logo=python)](https://python.org)
[![Next.js](https://img.shields.io/badge/Next.js-14-black?logo=next.js)](https://nextjs.org)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 📋 Table of Contents

- [What is NetaTrack?](#what-is-netatrack)
- [Project Structure](#project-structure)
- [Quick Start](#quick-start)
- [PHP Website Setup](#php-website-setup)
- [Python Admin App Setup](#python-admin-app-setup)
- [AI System — How It Works](#ai-system--how-it-works)
- [Auto-Fetch Engine](#auto-fetch-engine)
- [India Government Data Sources](#india-government-data-sources)
- [Python → Website Push API](#python--website-push-api)
- [PHP AI Endpoints](#php-ai-endpoints)
- [Admin Panel Features](#admin-panel-features)
- [Cron Jobs](#cron-jobs)
- [Database Tables](#database-tables)
- [Testing Checklist](#testing-checklist)
- [Troubleshooting](#troubleshooting)

---

## What is NetaTrack?

NetaTrack India is a full-stack political accountability system with three parts:

| Part | Technology | Purpose |
|------|-----------|---------|
| **Website** | PHP 8.2 + MySQL | Public-facing site, leader profiles, news, reports |
| **Frontend** | Next.js 14 + Tailwind | Modern UI layer (optional — PHP can run standalone) |
| **Python Admin** | Python 3.10 + Tkinter | Desktop admin app — data collection, AI analysis, push to site |

---

## Project Structure

```
NetaTrack-India/
├── php/                        # PHP website backend
│   ├── src/
│   │   └── AI/
│   │       ├── AIRouter.php    # Multi-AI router (5 providers)
│   │       ├── AIHelper.php    # Global ai() helper function
│   │       └── AICron.php      # AI cron job tasks
│   ├── routes/
│   │   ├── api_ai.php          # Public AI REST endpoints
│   │   └── api_push.php        # Python→site push API
│   ├── views/admin/settings/   # Settings page (all AI keys)
│   ├── database/               # SQL migrations
│   └── bootstrap.php
│
├── python-admin/               # Python desktop admin app
│   ├── main.py                 # Entry point
│   ├── db/                     # MySQL connection
│   ├── ui/tabs/
│   │   ├── auto_tab.py         # Auto-Fetch Engine tab
│   │   ├── settings_tab.py     # Settings (all keys)
│   │   ├── dashboard_tab.py    # Stats dashboard
│   │   ├── leaders_tab.py      # Leader management
│   │   ├── reports_tab.py      # Report moderation
│   │   ├── users_tab.py        # User management
│   │   └── scraper_tab.py      # Scraper trigger
│   └── auto/
│       ├── engine.py           # Orchestrator (8 tasks)
│       ├── ai_router.py        # Multi-AI router (5 providers)
│       ├── local_ai.py         # Offline pretrained AI (no key needed)
│       ├── base.py             # HTTP + AI helpers
│       ├── govt_data_fetcher.py  # ECI, Sansad, RS, MyNeta, RSS
│       ├── leader_fetcher.py   # Wikipedia + Sansad API enrichment
│       ├── announcement_fetcher.py # 13 RSS feeds + AI classify
│       ├── promise_tracker.py  # Track promise fulfillment
│       ├── case_detector.py    # Criminal/fake case detection
│       ├── fund_tracker.py     # MPLADS fund tracking
│       ├── score_calculator.py # AI score calculation
│       └── website_pusher.py   # Push data to live site
│
├── src/                        # Next.js frontend
├── installer/                  # Web installer (visit /installer)
├── database/                   # SQL schema files
├── database_extra.sql          # Extra tables for auto-fetch
└── python-admin/database_extra.sql
```

---

## Quick Start

### Requirements

| Requirement | Minimum version |
|-------------|----------------|
| PHP | 8.2+ with PDO, cURL, json |
| MySQL / MariaDB | 8.0+ |
| Python | 3.10+ |
| Node.js (optional) | 18+ (for Next.js frontend) |

### 1. Clone the repo

```bash
git clone https://github.com/david0154/NetaTrack-India.git
cd NetaTrack-India
```

### 2. Run the Web Installer

Upload the `php/` folder to your web server, then visit:

```
https://yoursite.com/installer
```

The installer will:
- Check PHP requirements
- Create database and all tables
- Set up admin account
- Generate `admin_api_token` for Python push API
- Write `.env` file automatically

---

## PHP Website Setup

### Manual setup (without installer)

```bash
cd php
cp ../.env.php.example .env
# Edit .env with your DB credentials
composer install
php artisan migrate
```

### Configure AI keys in Admin Panel

Go to **Admin → Settings → AI APIs** and add any keys you have:

```
Gemini API Key    →  https://aistudio.google.com (free)
OpenAI API Key    →  https://platform.openai.com
OpenRouter Key    →  https://openrouter.ai (100+ models, free tier)
Claude API Key    →  https://console.anthropic.com
Sarvam AI Key     →  https://sarvam.ai (best for Hindi/regional)
```

> **You only need ONE key to start.** The AI router auto-selects the first working provider.

### Use AI anywhere in PHP

```php
// In any controller, view, or route:
$ai = ai();  // loads keys from DB settings automatically

// Generate leader bio
$bio = $ai->summariseLeader($leader);

// Analyse a public report
$result = $ai->analyseReport($report_text, $leader_name);
// Returns: category, sentiment, credibility 0-100, is_spam, suggested_action

// Fact check a claim
$check = $ai->factCheck("PM built 5000 km highway", "Narendra Modi");
// Returns: verdict, confidence, explanation, sources_hint

// Classify a news headline
$info = $ai->classifyNews($title, $description);
// Returns: category, leader_name, sentiment, importance 1-5, tags

// Translate to Hindi (uses Sarvam AI first, then any AI)
$hindi = $ai->translateToHindi("Promise fulfilled in Bihar");
```

---

## Python Admin App Setup

### Install

```bash
cd python-admin
pip install -r requirements.txt
```

> **First run downloads ~500MB of AI models** (DistilBERT, DistilBART, BERT NER).
> After that, everything works offline.

### Run

```bash
python main.py
```

A **DB Connect dialog** appears. Enter your MySQL details and click **Connect**.

> **Tip:** Set env vars to skip the dialog:
> ```bash
> export DB_HOST=127.0.0.1
> export DB_NAME=netatrack
> export DB_USER=root
> python main.py
> ```

### Add AI Keys in Python Admin

Go to **Auto-Fetch tab → AI API Keys section** → enter any keys → click **Load from Settings** to load saved keys automatically.

---

## AI System — How It Works

NetaTrack uses a **2-step AI pipeline**:

```
Step 1: Local AI (Offline, always runs)
   ↓ DistilBERT   → sentiment (positive/negative/neutral)
   ↓ DistilBART   → category (criminal/fund/promise/...)
   ↓ BERT NER     → extract politician names from text
   ↓ DistilBART   → summarise long text
   ↓ Rule-based   → credibility score 0–100

Step 2: Cloud AI Router (runs if any key is configured)
   Tries in order: Gemini → OpenAI → OpenRouter → Claude → Sarvam
   ↓ Confirms/improves category
   ↓ Identifies specific leader
   ↓ Adds tags, importance, deeper analysis
```

### AI Provider Priority

| # | Provider | Model | Cost |
|---|----------|-------|------|
| 1 | **Gemini** | gemini-1.5-flash | Free tier available |
| 2 | **OpenAI** | gpt-4o-mini | ~$0.15/1M tokens |
| 3 | **OpenRouter** | llama-3-8b (free) | Free |
| 4 | **Claude** | claude-3-haiku | Very cheap |
| 5 | **Sarvam AI** | sarvam-2b | Indian AI, Hindi support |

### Local Pretrained Models (Python)

| Model | Task | Download Size |
|-------|------|--------------|
| `distilbert-base-uncased-finetuned-sst-2` | Sentiment analysis | ~67MB |
| `typeform/distilbart-mnli-12-3` | Zero-shot category classification | ~250MB |
| `dbmdz/bert-large-cased-finetuned-conll03` | Named entity recognition | ~400MB |
| `sshleifer/distilbart-cnn-6-6` | Text summarisation | ~300MB |

---

## Auto-Fetch Engine

Open the **🤖 Auto-Fetch tab** in the Python admin app. Select tasks, enter/load AI keys, and click **▶ Run Auto-Fetch**.

### 8 Tasks

| # | Task | What it does |
|---|------|-------------|
| 1 | **India Govt Data** | ECI affidavits, Sansad LS API, Rajya Sabha members, MyNeta criminal data, 15 RSS feeds |
| 2 | **Enrich Leaders** | Fill missing bios/photos from Wikipedia, update party/state/constituency |
| 3 | **Classify Announcements** | Fetch 13 news/govt RSS feeds, classify with LocalAI + Cloud AI |
| 4 | **Track Promises** | Search news for each pending promise, AI decides fulfilled/broken/in_progress |
| 5 | **Detect Cases** | Scan news for criminal/corruption/fake cases per leader, LocalAI pre-screens |
| 6 | **Track Funds** | MPLADS fund utilization per MP, detect leakage suspected |
| 7 | **Recalculate Scores** | Rule-based + AI holistic score, update rankings |
| 8 | **Push to Website** | Push all data to live site via REST API |

### Score Formula

```
Total Score = Promise% × 0.20
            + Project% × 0.15
            + Fund%    × 0.15
            + Criminal × 0.25
            + Transp   × 0.10
            + AI Score × 0.15
```

---

## India Government Data Sources

The engine fetches from these official sources automatically:

### Government Portals
| Source | Data |
|--------|------|
| **Sansad.in** (Lok Sabha API) | All 543 MP names, party, constituency, state |
| **Sansad.in** (Rajya Sabha API) | All 245 RS MP names |
| **ECI / MyNeta API** | Candidate affidavits: assets, liabilities, declared criminal cases |
| **MyNeta Criminal** | MPs with pending criminal cases at election time |
| **PMO India RSS** | Prime Minister's press releases |
| **PIB India RSS** | Press Information Bureau — central government news |
| **MyGov RSS** | Government scheme announcements |
| **Lok Sabha RSS** | Parliamentary debates and notices |
| **Rajya Sabha RSS** | Upper house news |

### News & Accountability
| Source | Type |
|--------|------|
| NDTV, The Hindu, Indian Express | National politics news |
| Hindustan Times, LiveMint, ANI, PTI | Breaking news |
| ADR India | Election/criminal affidavit analysis |
| DuckDuckGo Instant API | General news search (no key) |

---

## Python → Website Push API

After collecting and analysing data, push it directly to your live website.

### Setup

1. Go to **PHP Admin → Settings → Push API** → copy **Admin API Token** (or click Generate)
2. In **Python Admin → Auto-Fetch tab → Push to Website section** → enter Site URL + Token
3. Click **🚀 Push to Website Now** OR tick **🌐 Push to Website after Engine finishes**

### Push Endpoints (PHP receives these)

```
POST /api/v1/push/leaders        → upsert all leader records
POST /api/v1/push/scores         → update all score columns
POST /api/v1/push/cases          → insert new criminal cases
POST /api/v1/push/announcements  → insert approved announcements
POST /api/v1/push/funds          → upsert MPLADS fund records
```

All endpoints require `Authorization: Bearer <admin_api_token>` header.

---

## PHP AI Endpoints

Public REST endpoints available on the website for frontend/AJAX use:

```
POST /api/ai/summarise      body: { leader_id: 5 }
POST /api/ai/factcheck      body: { claim: "...", leader_name: "..." }
POST /api/ai/translate      body: { text: "Promise fulfilled" }
POST /api/ai/chat           body: { question: "...", leader_name: "..." }
POST /api/ai/classify_news  body: { title: "...", description: "..." }  [admin only]
```

All endpoints are **rate-limited per session** to prevent abuse.

---

## Admin Panel Features

### PHP Web Admin (`/admin`)
- **Dashboard** — stats, recent reports, pending approvals
- **Leaders** — add/edit/delete, score sliders, verify badge
- **Reports** — approve/reject citizen reports, AI analysis shown
- **Users** — ban/unban, change roles (user/moderator/admin)
- **Announcements** — review AI-classified news items
- **Settings** — all 5 AI keys, push API token, SMTP, Analytics, Ads

### Python Desktop Admin
| Tab | Features |
|-----|---------|
| 📊 Dashboard | 6 live stat cards, pending reports table, top leaders |
| 🤖 Auto-Fetch | 8 task checkboxes, 5 AI key fields, push config, live log |
| 👤 Leaders | Search, add, edit (score sliders), delete |
| 📋 Reports | Filter by status, approve/reject one-click |
| 👥 Users | Search, ban/unban, change role |
| 📰 Scraper | Trigger HTTP scraper, live log, recent jobs |
| ⚙️ Settings | All keys + SMTP + site config |

---

## Cron Jobs

Add these to your server crontab (`crontab -e`):

```bash
# AI auto-classify announcements, fill bios, analyse reports (every 5 min)
*/5 * * * * php /var/www/netatrack/php/artisan cron:ai >> /var/log/netatrack_ai.log 2>&1

# Full auto-fetch engine (daily at 2 AM)
0 2 * * * cd /var/www/netatrack/python-admin && python -c "
from db.connection import DBConnection
from auto.engine import AutoEngine
db = DBConnection()
db.connect_from_env()
engine = AutoEngine(db, {}, print)
engine.start(['govt_data','leaders','announcements','promises','cases','funds','scores','push'])
import time; time.sleep(3600)
" >> /var/log/netatrack_engine.log 2>&1
```

---

## Database Tables

### Core Tables
| Table | Description |
|-------|-------------|
| `leaders` | Politicians with scores, bio, photo, ECI data |
| `parties` | Political parties |
| `states` | Indian states |
| `promises` | Leader promises with fulfillment status |
| `projects` | Development projects |
| `reports` | Citizen-filed reports |
| `users` | Registered users |
| `settings` | All site/AI/push settings |

### Auto-Fetch Tables (run `database_extra.sql` once)
| Table | Description |
|-------|-------------|
| `announcements` | News items from RSS + govt feeds |
| `criminal_cases` | Criminal/corruption/fake cases per leader |
| `fund_records` | MPLADS fund allocation + utilization per MP |

### Run extra migrations

```bash
mysql -u root -p netatrack < python-admin/database_extra.sql
```

---

## Testing Checklist

### ✅ PHP Website

```bash
# 1. Test DB connection
php -r "require 'bootstrap.php'; echo db()->fetchOne('SELECT 1')['1'];"

# 2. Test AI router
php -r "require 'bootstrap.php'; echo ai()->ask('Say hello in 5 words');"

# 3. Test push API (replace with your token)
curl -X POST https://yoursite.com/api/v1/push/leaders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"leaders":[]}'
# Expected: {"message":"0 leaders upserted","ok":0}

# 4. Test AI endpoint
curl -X POST https://yoursite.com/api/ai/chat \
  -H "Content-Type: application/json" \
  -d '{"question":"Who is the PM of India?","leader_name":""}'
```

### ✅ Python Admin App

```bash
cd python-admin

# 1. Test DB connection
python -c "from db.connection import DBConnection; db=DBConnection(); db.connect('localhost','netatrack','root',''); print('DB OK')"

# 2. Test LocalAI (no internet needed)
python -c "
from auto.local_ai import LocalAI
ai = LocalAI()
r = ai.analyse_news_item('PM Modi arrested by CBI for corruption case', 'Court orders arrest')
print(r)
"
# Expected output: category=criminal, sentiment=negative, names=[Modi], importance=4+

# 3. Test AI Router
python -c "
from auto.ai_router import AIRouter
ai = AIRouter(keys={'gemini': 'YOUR_KEY'})
print(ai.ask('Say: test OK'))
"

# 4. Test RSS fetch
python -c "
from auto.base import AutoBase
class T(AutoBase): pass
t = T(None, '', print)
text = t.http_get('https://feeds.feedburner.com/ndtvnews-india-news')
print('RSS OK, length:', len(text))
"

# 5. Test website push
python -c "
from auto.website_pusher import WebsitePusher
# Set site_url and admin_api_token in settings first
"
```

### ✅ Full Engine Test (safe — read-only run without push)

```python
# test_engine.py
from db.connection import DBConnection
from auto.engine import AutoEngine

db = DBConnection()
db.connect('localhost', 'netatrack', 'root', 'yourpassword')

engine = AutoEngine(db, {'gemini': 'YOUR_GEMINI_KEY'}, print)
engine.start(['govt_data', 'leaders', 'scores'])  # no 'push' = safe test
```

---

## Troubleshooting

### Python: `ModuleNotFoundError: transformers`
```bash
pip install transformers torch
# CPU-only (smaller):
pip install torch --index-url https://download.pytorch.org/whl/cpu
```

### Python: Models download slowly
Models are cached in `~/.cache/huggingface/` after first download. If slow, use a VPN or download manually.

### PHP: `ai()` returns empty string
- Check Admin → Settings → AI APIs — at least one key must be saved
- Check PHP error log: `tail -f /var/log/apache2/error.log`
- Test: `curl https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=YOUR_KEY -d '{"contents":[{"parts":[{"text":"hello"}]}]}' -H 'Content-Type: application/json'`

### Push API: `401 Unauthorized`
- Check `admin_api_token` matches in both Python admin settings and PHP settings table
- Token is compared with `hash_equals()` — must be exact match

### Engine: RSS feeds return empty
Some feeds block bots. The engine uses `User-Agent: Mozilla/5.0 NetaTrackBot/1.0`. If still blocked, add your own RSS proxies in `GOVT_RSS_SOURCES` in `govt_data_fetcher.py`.

### MySQL: `Column not found` errors
Run the extra migration:
```bash
mysql -u root -p netatrack < python-admin/database_extra.sql
```

---

## License

MIT License — free to use, modify, and deploy.

---

## Built by

**David** — [github.com/david0154](https://github.com/david0154)

> NetaTrack India — Making Indian politics transparent, one data point at a time. 🇮🇳

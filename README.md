# <img src="logo.png" width="48" style="vertical-align:middle"> NetaTrack India

> **India's political accountability platform** — track politicians, monitor promises, detect corruption, analyse funds, and rate leaders using AI.

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?logo=php)](https://php.net)
[![Python](https://img.shields.io/badge/Python-3.10+-3776AB?logo=python)](https://python.org)
[![Next.js](https://img.shields.io/badge/Next.js-14-black?logo=next.js)](https://nextjs.org)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

<p align="center">
  <img src="logo.png" width="180" alt="NetaTrack India Logo"/>
  <br/>
  <b>Making Indian politics transparent, one data point at a time 🇮🇳</b>
</p>

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
├── logo.png                    # NetaTrack India logo
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
$ai = ai();  // loads keys from DB settings automatically

$bio    = $ai->summariseLeader($leader);
$result = $ai->analyseReport($report_text, $leader_name);
$check  = $ai->factCheck("PM built 5000 km highway", "Narendra Modi");
$info   = $ai->classifyNews($title, $description);
$hindi  = $ai->translateToHindi("Promise fulfilled in Bihar");
```

---

## Python Admin App Setup

### Install

```bash
cd python-admin
pip install -r requirements.txt
```

> **First run downloads ~500MB of AI models** (DistilBERT, DistilBART, BERT NER).
> After that, everything works **offline**.

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

---

## AI System — How It Works

NetaTrack uses a **2-step AI pipeline**:

```
Step 1: Local AI (Offline, always runs, no key needed)
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

| Model | Task | Size |
|-------|------|------|
| `distilbert-base-uncased-finetuned-sst-2` | Sentiment analysis | ~67MB |
| `typeform/distilbart-mnli-12-3` | Zero-shot category classification | ~250MB |
| `dbmdz/bert-large-cased-finetuned-conll03` | Named entity recognition | ~400MB |
| `sshleifer/distilbart-cnn-6-6` | Text summarisation | ~300MB |

---

## Auto-Fetch Engine

Open the **🤖 Auto-Fetch tab** in the Python admin app. Select tasks and click **▶ Run Auto-Fetch**.

### 8 Tasks

| # | Task | What it does |
|---|------|-------------|
| 1 | **India Govt Data** | ECI affidavits, Sansad LS API, Rajya Sabha members, MyNeta criminal data, 15 RSS feeds |
| 2 | **Enrich Leaders** | Fill missing bios/photos from Wikipedia |
| 3 | **Classify Announcements** | 13 news/govt RSS feeds, LocalAI + Cloud AI classify |
| 4 | **Track Promises** | Search news per promise, AI decides fulfilled/broken/in_progress |
| 5 | **Detect Cases** | Scan news for criminal/fake cases, LocalAI pre-screens |
| 6 | **Track Funds** | MPLADS fund utilization per MP, detect leakage |
| 7 | **Recalculate Scores** | Rule-based + AI holistic score + ranking |
| 8 | **Push to Website** | Push all data to live site via REST API |

### Score Formula

```
Total Score = Promise% × 0.20 + Project% × 0.15 + Fund% × 0.15
            + Criminal × 0.25 + Transparency × 0.10 + AI Score × 0.15
```

---

## India Government Data Sources

| Source | Data |
|--------|------|
| **Sansad.in** (Lok Sabha) | All 543 MP names, party, constituency |
| **Sansad.in** (Rajya Sabha) | All 245 RS MP names |
| **ECI / MyNeta** | Candidate affidavits: assets, liabilities, criminal cases |
| **MyNeta Criminal** | MPs with pending criminal cases |
| **PMO India RSS** | Prime Minister press releases |
| **PIB India RSS** | Central government news |
| **MyGov RSS** | Government scheme announcements |
| **Lok Sabha RSS** | Parliamentary debates |
| **Rajya Sabha RSS** | Upper house news |
| NDTV, The Hindu, Indian Express | National politics news |
| Hindustan Times, ANI, PTI | Breaking news |
| ADR India | Election/criminal affidavit analysis |

---

## Python → Website Push API

1. PHP Admin → Settings → **Admin API Token** → copy or click Generate
2. Python Admin → Auto-Fetch tab → **Push to Website** → enter Site URL + Token
3. Click **🚀 Push to Website Now** or tick **Push after Engine finishes**

```
POST /api/v1/push/leaders        → upsert all leaders
POST /api/v1/push/scores         → update all scores
POST /api/v1/push/cases          → insert criminal cases
POST /api/v1/push/announcements  → insert announcements
POST /api/v1/push/funds          → upsert fund records
```

---

## PHP AI Endpoints

```
POST /api/ai/summarise    { leader_id: 5 }
POST /api/ai/factcheck    { claim: "...", leader_name: "..." }
POST /api/ai/translate    { text: "..." }
POST /api/ai/chat         { question: "...", leader_name: "..." }
```

---

## Admin Panel Features

### PHP Web Admin
- Dashboard, Leaders, Reports, Users, Announcements, Settings
- Settings has all 5 AI keys + Push API token + SMTP + Analytics

### Python Desktop Admin
| Tab | Features |
|-----|---------|
| 📊 Dashboard | Stats, pending reports, top leaders |
| 🤖 Auto-Fetch | 8 tasks, 5 AI keys, push config, live log |
| 👤 Leaders | Search, add, edit, delete |
| 📋 Reports | Filter, approve/reject |
| 👥 Users | Ban/unban, change role |
| 📰 Scraper | Trigger, live log |
| ⚙️ Settings | All keys + config |

---

## Cron Jobs

```bash
# AI auto-tasks every 5 minutes
*/5 * * * * php /var/www/netatrack/php/artisan cron:ai

# Full engine daily at 2 AM
0 2 * * * cd /var/www/netatrack/python-admin && python -c "
from db.connection import DBConnection; from auto.engine import AutoEngine
db=DBConnection(); db.connect_from_env()
AutoEngine(db,{},print).start(['govt_data','leaders','announcements','promises','cases','funds','scores','push'])
import time; time.sleep(3600)
"
```

---

## Database Tables

| Table | Description |
|-------|-------------|
| `leaders` | Politicians with scores, bio, ECI data |
| `parties` | Political parties |
| `states` | Indian states |
| `promises` | Leader promises + fulfillment status |
| `projects` | Development projects |
| `reports` | Citizen-filed reports |
| `users` | Registered users |
| `settings` | All site/AI/push settings |
| `announcements` | News from RSS + govt feeds |
| `criminal_cases` | Cases per leader (real + fake flagged) |
| `fund_records` | MPLADS fund allocation per MP |

```bash
# Run extra migration once
mysql -u root -p netatrack < python-admin/database_extra.sql
```

---

## Testing Checklist

```bash
# PHP — test AI
php -r "require 'bootstrap.php'; echo ai()->ask('Say hello in 5 words');"

# Python — test LocalAI (offline, no key)
python -c "
from auto.local_ai import LocalAI
print(LocalAI().analyse_news_item('PM Modi arrested by CBI','Court orders arrest'))
"

# Python — test push API
curl -X POST https://yoursite.com/api/v1/push/leaders \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" -d '{"leaders":[]}'

# Python — full engine test (safe, no push)
python -c "
from db.connection import DBConnection; from auto.engine import AutoEngine
db=DBConnection(); db.connect('localhost','netatrack','root','')
AutoEngine(db,{'gemini':'YOUR_KEY'},print).start(['govt_data','leaders','scores'])
"
```

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| `ModuleNotFoundError: transformers` | `pip install transformers torch` |
| `ai()` returns empty string | Add at least one API key in Admin → Settings → AI APIs |
| Push API `401 Unauthorized` | Check `admin_api_token` matches in both Python and PHP settings |
| RSS feeds return empty | Some block bots — try adding a delay or different user-agent |
| `Column not found` MySQL error | Run `mysql -u root -p netatrack < python-admin/database_extra.sql` |

---

## License

MIT License — free to use, modify, and deploy.

---

<p align="center">
  <img src="logo.png" width="80" alt="NetaTrack India"/><br/>
  <b>Built by <a href="https://github.com/david0154">David</a></b><br/>
  NetaTrack India — Making Indian politics transparent 🇮🇳
</p>

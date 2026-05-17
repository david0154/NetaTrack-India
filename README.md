# <img src="logo.png" width="48" style="vertical-align:middle"> NetaTrack India

> **India’s political accountability platform** — track politicians, monitor promises, detect corruption, analyse funds, and rate leaders using AI.

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
- [Python Settings Tab](#python-settings-tab)
- [AI System — How It Works](#ai-system--how-it-works)
- [Auto-Fetch Engine](#auto-fetch-engine)
- [Leader Update System](#leader-update-system)
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
├── logo.png                          # NetaTrack India logo
├── php/                              # PHP website backend
│   ├── src/
│   │   ├── AI/
│   │   │   ├── AIRouter.php            # Multi-AI router (5 providers)
│   │   │   ├── AIHelper.php            # Global ai() helper
│   │   │   └── AICron.php              # AI cron tasks
│   │   └── Seed/
│   │       └── SeedRunner.php          # States/leaders seed runner
│   ├── routes/
│   │   ├── api_ai.php                # Public AI REST endpoints
│   │   └── api_push.php              # Python→site push API
│   ├── views/admin/
│   │   ├── settings/                 # PHP admin settings (AI keys etc.)
│   │   └── leaders/add.php           # Add/edit leader form
│   └── bootstrap.php
│
├── database/
│   ├── schema.sql                    # Full DB schema
│   └── seed_states_leaders.sql       # All 28 states + 8 UTs + 45 leaders
│
├── python-admin/                     # Python desktop admin app
│   ├── main.py                         # Entry point
│   ├── requirements.txt               # Dependencies (pure Python, no C++)
│   ├── config/
│   │   └── settings.json               # All settings (auto-created on save)
│   ├── db/                             # MySQL connection
│   ├── ui/tabs/
│   │   ├── settings_tab.py             # ⭐ Settings (URL, token, AI keys, DB, SMTP)
│   │   ├── leaders_update_tab.py       # ⭐ Update all leaders from Sansad + Wikipedia
│   │   ├── auto_tab.py                 # Auto-Fetch Engine tab
│   │   ├── dashboard_tab.py            # Stats dashboard
│   │   ├── leaders_tab.py              # Leader management
│   │   ├── reports_tab.py              # Report moderation
│   │   ├── users_tab.py                # User management
│   │   └── scraper_tab.py              # Scraper trigger
│   └── auto/
│       ├── engine.py                   # Orchestrator (8 tasks)
│       ├── ai_router.py                # Multi-AI router (5 providers)
│       ├── local_ai.py                 # Lightweight offline AI (~60MB, no C++)
│       ├── model_downloader.py         # Auto-downloads AI model on first run
│       ├── base.py                     # HTTP + AI helpers
│       ├── leader_fetcher.py           # Wikipedia + Sansad API
│       ├── leader_updater.py           # Smart diff updater (all leaders)
│       ├── announcement_fetcher.py     # 13 RSS feeds + AI classify
│       ├── promise_tracker.py          # Promise fulfillment tracking
│       ├── case_detector.py            # Criminal/fake case detection
│       ├── fund_tracker.py             # MPLADS fund tracking
│       ├── score_calculator.py         # Score calculation
│       └── website_pusher.py           # Push data to live site via API
│
├── src/                              # Next.js frontend (optional)
├── installer/                        # Web installer (visit /installer)
└── python-admin/database_extra.sql   # Extra DB columns migration
```

---

## Quick Start

### Requirements

| Requirement | Minimum |
|-------------|--------|
| PHP | 8.2+ with PDO, cURL, json |
| MySQL / MariaDB | 8.0+ |
| Python | 3.10+ |
| Node.js (optional) | 18+ (Next.js frontend) |

### 1. Clone

```bash
git clone https://github.com/david0154/NetaTrack-India.git
cd NetaTrack-India
```

### 2. Web Installer

Upload `php/` to your web server, then visit:

```
https://yoursite.com/installer
```

The installer:
- Checks PHP requirements
- Creates DB + all tables
- Seeds all 28 states + 8 UTs + 45 current leaders
- Creates admin account
- Generates `admin_api_token` for Python push API
- Writes `.env` automatically

### 3. Seed Leaders (manual)

```bash
mysql -u root -p netatrack < database/seed_states_leaders.sql
# Includes: all states, all UTs, 45 current Indian leaders + Wikipedia photos
```

---

## PHP Website Setup

```bash
cd php
cp ../.env.php.example .env
# Edit .env with DB credentials
composer install
php artisan migrate
```

Go to **Admin → Settings → AI APIs** and add any keys:

```
Gemini API Key    →  https://aistudio.google.com  (free)
OpenAI API Key    →  https://platform.openai.com
OpenRouter Key    →  https://openrouter.ai  (free tier)
Claude API Key    →  https://console.anthropic.com
Sarvam AI Key     →  https://sarvam.ai  (Hindi/Indian languages)
```

> You only need **ONE key** to start.

---

## Python Admin App Setup

### 1. Install dependencies

```bash
cd python-admin
pip install -r requirements.txt

# Install PyTorch CPU (no C++, no GPU, no Visual Studio needed):
pip install torch --index-url https://download.pytorch.org/whl/cpu

# OR lighter ONNX Runtime instead of torch:
pip install onnxruntime
```

### 2. Run

```bash
python main.py
```

- **DB Connect dialog** appears — enter MySQL details, click Connect
- **AI model downloads** (~60MB, first run only, cached forever after)
- Main window opens with all tabs

> **Skip the DB dialog using env vars:**
> ```bash
> export DB_HOST=127.0.0.1 DB_NAME=netatrack DB_USER=root
> python main.py
> ```

---

## Python Settings Tab

Open the **⚙️ Settings** tab in the Python admin app to configure everything:

### 🌐 Website Connection
| Field | Description |
|-------|-------------|
| **Website URL** | Your live site URL e.g. `https://netatrack.in` |
| **Push API Token** | From PHP Admin → Settings → Admin API Token |
| **Test Connection** | Button — pings `/api/v1/ping` to verify URL + token |

### 🤖 AI API Keys
| Key | Where to get | Cost |
|-----|-------------|------|
| Gemini | [aistudio.google.com](https://aistudio.google.com) | Free tier |
| OpenAI | [platform.openai.com](https://platform.openai.com) | Paid |
| OpenRouter | [openrouter.ai](https://openrouter.ai) | Free tier |
| Claude | [console.anthropic.com](https://console.anthropic.com) | Paid |
| Sarvam AI | [sarvam.ai](https://sarvam.ai) | Indian AI |

> Each key has a 👁️ show/hide button. App tries keys in order: Gemini → OpenAI → OpenRouter → Claude → Sarvam.

### 🗄️ Database
Host, port, database name, username, password — inline fields.

### 📧 SMTP / Email
For sending report notifications, alerts.

### ⏰ Auto-Fetch
| Setting | Default | Description |
|---------|---------|-------------|
| Delay between requests | 1.0s | Avoid rate limiting |
| Max parallel workers | 2 | Parallel fetch threads |
| Wikipedia enrich limit | 200 | Leaders to enrich per run |
| Auto-push after engine | no | Push to site after each run |

**Settings are saved to:**
- `python-admin/config/settings.json` — human-readable JSON
- `python-admin/.env` — for environment variable access

---

## AI System — How It Works

```
Step 1: Local AI (offline, ~60MB, no key needed, no C++)
   ↓ cardiffnlp sentiment model  → positive / negative / neutral
   ↓ Rule-based keyword engine   → category (criminal/fund/promise/...)
   ↓ Regex + title-case heuristic → extract politician names
   ↓ First-2-sentences rule      → summarise text
   ↓ Rule-based scoring          → credibility 0–100

Step 2: Cloud AI Router (runs if any key configured)
   Tries: Gemini → OpenAI → OpenRouter → Claude → Sarvam
   ↓ Confirms category + leader identity
   ↓ Adds tags, importance, deeper analysis
   ↓ Hindi/regional translation (Sarvam)
```

### AI Provider Priority

| # | Provider | Model | Cost |
|---|----------|-------|------|
| 1 | **Gemini** | gemini-1.5-flash | Free tier |
| 2 | **OpenAI** | gpt-4o-mini | ~$0.15/1M tokens |
| 3 | **OpenRouter** | llama-3-8b (free) | Free |
| 4 | **Claude** | claude-3-haiku | Very cheap |
| 5 | **Sarvam AI** | sarvam-2b | Indian AI, Hindi |

### Local Model (Python)

| Model | Task | Size | C++ needed? |
|-------|------|------|-------------|
| `cardiffnlp/twitter-roberta-base-sentiment-latest` | Sentiment | ~60MB | ✖ No |
| Rule-based keyword engine | Category | 0MB | ✖ No |
| Regex heuristic | Name extraction | 0MB | ✖ No |
| First-2-sentences | Summarise | 0MB | ✖ No |

---

## Auto-Fetch Engine

Open **🤖 Auto-Fetch tab** → select tasks → click **▶ Run Auto-Fetch**.

| # | Task | What it does |
|---|------|--------------|
| 1 | **India Govt Data** | ECI affidavits, Sansad LS/RS APIs, MyNeta criminal data, 15 RSS feeds |
| 2 | **Enrich Leaders** | Fill missing bio/photo from Wikipedia |
| 3 | **Classify Announcements** | 13 news/govt RSS feeds, AI classify |
| 4 | **Track Promises** | Search news per promise, AI decides fulfilled/broken/in_progress |
| 5 | **Detect Cases** | Scan news for criminal/fake cases |
| 6 | **Track Funds** | MPLADS fund utilization per MP |
| 7 | **Recalculate Scores** | Formula-based + AI holistic score |
| 8 | **Push to Website** | Push all data to live site via REST API |

### Score Formula

```
Total Score = Promise(20%) + Fund(15%) + Criminal(25%) + Attendance(20%) + Transparency(20%)
```

---

## Leader Update System

Open **🔄 Update Leaders tab** to update all leaders anytime:

```
Sources (tick to include):
  ☑ Lok Sabha MPs   — all 543 MPs from Sansad.in API
  ☑ Rajya Sabha MPs  — all 245 MPs from Sansad.in API
  ☑ Bio & Photos     — Wikipedia REST API
  ☑ Recalc Scores    — recompute all total scores

  ☑ Update Photos    ☑ Update Bios

  [▶ Update All Leaders Now]  [🔄 Refresh Single Leader]
```

- **Smart diff** — only updates fields that actually changed
- **Refresh Single** — type a leader name/ID to refresh just one
- Safe to run daily — never creates duplicates
- Pre-seeded with **45 current leaders** including all state CMs, PM, key ministers

---

## India Government Data Sources

| Source | Data |
|--------|------|
| **Sansad.in** (LS) | All 543 Lok Sabha MP names, party, constituency |
| **Sansad.in** (RS) | All 245 Rajya Sabha MP names |
| **ECI / MyNeta** | Candidate affidavits: assets, liabilities, criminal cases |
| **Wikipedia REST API** | Bios, photos, DOB for all leaders |
| **PMO India RSS** | Prime Minister press releases |
| **PIB India RSS** | Central government news |
| **MyGov RSS** | Scheme announcements |
| **Lok Sabha RSS** | Parliamentary debates |
| **Rajya Sabha RSS** | Upper house news |
| NDTV, The Hindu, IE | National politics news |
| Hindustan Times, ANI | Breaking news |
| ADR India | Election/criminal affidavit analysis |

---

## Python → Website Push API

### Setup (once)
1. PHP Admin → Settings → copy **Admin API Token**
2. Python Admin → **⚙️ Settings tab** → paste **Website URL** + **Push API Token** → Save
3. Click **Test Connection** — should show ✓ Connected

### Push endpoints
```
POST /api/v1/ping                → test connection
POST /api/v1/push/leaders        → upsert all leaders
POST /api/v1/push/scores         → update all scores
POST /api/v1/push/cases          → insert criminal cases
POST /api/v1/push/announcements  → insert announcements
POST /api/v1/push/funds          → upsert fund records
```

All endpoints require header:
```
Authorization: Bearer YOUR_PUSH_API_TOKEN
```

---

## PHP AI Endpoints

```http
POST /api/ai/summarise    { "leader_id": 5 }
POST /api/ai/factcheck    { "claim": "...", "leader_name": "..." }
POST /api/ai/translate    { "text": "..." }
POST /api/ai/chat         { "question": "...", "leader_name": "..." }
```

---

## Admin Panel Features

### PHP Web Admin
- Dashboard, Leaders (with state/party dropdowns pre-seeded), Reports, Users, Settings
- Settings: 5 AI keys + Push API token + SMTP + Analytics

### Python Desktop Admin

| Tab | Features |
|-----|---------|
| 📊 Dashboard | Stats, pending reports, top leaders |
| 🤖 Auto-Fetch | 8 tasks, live log, push to website |
| 🔄 Update Leaders | All 543 LS + 245 RS MPs, Wikipedia enrich, single refresh |
| 👤 Leaders | Search, add, edit, delete |
| 📋 Reports | Filter, approve/reject |
| 👥 Users | Ban/unban, change role |
| 📰 Scraper | Trigger, live log |
| ⚙️ Settings | Website URL, Push API token, AI keys, DB, SMTP, Auto-fetch options |

---

## Cron Jobs

```bash
# PHP AI tasks every 5 minutes
*/5 * * * * php /var/www/netatrack/php/artisan cron:ai

# Python full engine daily at 2 AM
0 2 * * * cd /var/www/netatrack/python-admin && python -c "
from db.connection import DBConnection
from auto.engine import AutoEngine
db = DBConnection()
db.connect_from_env()
AutoEngine(db, {}, print).start([
    'govt_data','leaders','announcements',
    'promises','cases','funds','scores','push'
])
"
```

---

## Database Tables

| Table | Description |
|-------|-------------|
| `leaders` | Politicians with scores, bio, photo, constituency |
| `parties` | 25 political parties with colour + ideology |
| `states` | 28 states + 8 UTs with code, capital, region, seats |
| `promises` | Leader promises + fulfillment status |
| `projects` | Development projects |
| `reports` | Citizen-filed reports |
| `users` | Registered users |
| `settings` | All site / AI / push settings |
| `announcements` | News from RSS + govt feeds |
| `criminal_cases` | Cases per leader (real + fake flagged) |
| `fund_records` | MPLADS fund allocation per MP |

```bash
# Run schema + seed:
mysql -u root -p netatrack < database/schema.sql
mysql -u root -p netatrack < database/seed_states_leaders.sql
mysql -u root -p netatrack < python-admin/database_extra.sql
```

---

## Testing Checklist

```bash
# PHP — test AI
php -r "require 'bootstrap.php'; echo ai()->ask('Say hello in 5 words');"

# Python — test local AI
python -c "
from auto.local_ai import LocalAI
print(LocalAI().analyse_news_item('PM Modi arrested by CBI','Court orders arrest'))
"

# Python — test push API
curl -X GET https://yoursite.com/api/v1/ping \
  -H "Authorization: Bearer YOUR_TOKEN"

# Python — test full engine (no push)
python -c "
from db.connection import DBConnection
from auto.engine import AutoEngine
db = DBConnection()
db.connect('localhost','netatrack','root','')
AutoEngine(db,{'gemini':'YOUR_KEY'},print).start(['govt_data','leaders','scores'])
"
```

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| `ModuleNotFoundError: transformers` | `pip install transformers` |
| `No module named torch` | `pip install torch --index-url https://download.pytorch.org/whl/cpu` |
| AI model download fails | Check internet. Model cached at `~/.cache/huggingface/` after first download |
| `ai()` returns empty | Add at least one API key in **⚙️ Settings → AI API Keys** |
| Push API `401 Unauthorized` | Check token matches in Settings ↔ PHP Admin → Settings |
| Push API `Connection Failed` | Check Website URL in Settings, no trailing slash |
| RSS feeds empty | Some block bots — increase fetch delay in **⚙️ Settings → Auto-Fetch** |
| `Column not found` MySQL | Run `mysql -u root -p netatrack < python-admin/database_extra.sql` |
| DB connect dialog skipped | Set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` env vars |

---

## License

MIT License — free to use, modify, and deploy.

---

<p align="center">
  <img src="logo.png" width="80" alt="NetaTrack India"/><br/>
  <b>Built by <a href="https://github.com/david0154">David</a></b><br/>
  NetaTrack India — Making Indian politics transparent 🇮🇳
</p>

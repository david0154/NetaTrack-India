# NetaTrack India — Deployment Guide

## Prerequisites

| Requirement | Version |
|------------|--------|
| PHP | 8.1+ |
| MySQL / MariaDB | 8.0+ / 10.6+ |
| Nginx or Apache | Latest |
| Composer (optional) | 2.x |

---

## Quick Start (Local)

```bash
# 1. Clone
git clone https://github.com/david0154/NetaTrack-India.git
cd NetaTrack-India/php

# 2. Environment
cp .env.example .env
# Edit .env with your DB credentials, APP_URL, Gemini API key

# 3. Database setup
mysql -u root -p -e "CREATE DATABASE netatrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php database/migrate.php
# Optional demo data:
php database/migrate.php --seed

# 4. Serve
php -S localhost:8000 -t public
```

Visit: `http://localhost:8000`  
Admin: `http://localhost:8000/admin/auth/login`  
Default credentials: `admin@netatrack.in` / `Admin@123`

---

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name netatrack.in www.netatrack.in;
    root /var/www/netatrack/php/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. { deny all; }
}
```

---

## Environment Variables

```env
APP_NAME="NetaTrack India"
APP_URL=https://netatrack.in
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Kolkata

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=netatrack
DB_USER=netatrack_user
DB_PASS=your_secure_password

GEMINI_API_KEY=AIza...
SARVAM_API_KEY=your_sarvam_key

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your@gmail.com
SMTP_PASS=your_app_password
```

---

## AI Scraper (Cron Job)

To run the RSS scraper automatically every 6 hours:

```cron
0 */6 * * * php /var/www/netatrack/php/database/migrate.php --check >> /var/log/netatrack-cron.log 2>&1
0 */6 * * * curl -s http://localhost/admin/scraper/run -X POST -H "X-Cron: true" >> /var/log/scraper.log 2>&1
```

---

## Project Structure

```
NetaTrack-India/
├── php/
│   ├── public/              # Web root (index.php + assets)
│   ├── src/
│   │   ├── Controllers/     # PublicController, AdminController, AuthController, ReportController
│   │   ├── Core/            # Database, Router, Request, Response, Auth, helpers
│   │   ├── Models/          # Leader, Promise, Project, Report, User, Setting
│   │   └── Services/        # AIService (Gemini), ScraperService (RSS)
│   ├── views/
│   │   ├── layouts/         # public.php, admin.php, auth
│   │   ├── public/          # home, leaders, leader-profile, promises, projects, corruption, report
│   │   ├── admin/           # dashboard, leaders, reports, users, projects, analytics, scraper, settings
│   │   ├── auth/            # login, register, admin-login
│   │   └── errors/          # 404, 500
│   ├── routes/              # web.php
│   ├── config/              # app.php, database.php
│   ├── database/
│   │   ├── migrations/      # 001_create_core_tables.sql
│   │   └── migrate.php      # CLI runner (--fresh, --seed)
│   ├── bootstrap.php
│   ├── .env.example
│   └── composer.json
└── DEPLOYMENT.md
```

---

## Default Admin

| Field | Value |
|-------|-------|
| Email | admin@netatrack.in |
| Password | Admin@123 |

> ⚠️ **Change the default password immediately after first login!**

---

## Tech Stack

| Layer | Technology |
|-------|----------|
| Backend | PHP 8.1+ (custom MVC framework) |
| Database | MySQL 8 / MariaDB 10.6 |
| Frontend | Glassmorphism dark UI (vanilla CSS + JS) |
| AI | Google Gemini 1.5 Flash |
| AI (Indian lang) | Sarvam AI |
| Scraper | RSS feeds — NDTV, India Today, The Hindu, TOI, HT, News18 |
| Maps | SVG India heatmap (28 states + 8 UTs, clickable) |

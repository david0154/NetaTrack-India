# NetaTrack India (PHP Edition)

This is now a **PHP website starter** (not Next.js) with:
- Public site
- Admin moderation panel
- One-click installer
- AWS AI API configuration support for collection pipeline
- Basic collector script and database schema

## What you asked and what is now added
1. **PHP website** ✅
2. **AWS AI API settings** for data collection ✅
3. **One-click install option** ✅

## Project structure
- `public/index.php` - public homepage
- `admin/index.php` - admin moderation dashboard
- `installer/index.php` - one-click web installer
- `installer/schema.sql` - DB schema created during install
- `src/Database.php` - PDO database connection
- `src/AwsAiClient.php` - AWS AI config-aware client placeholder
- `src/Collector.php` - collector pipeline writes to `ai_collected_data`
- `scripts/collect.php` - CLI collector trigger (cron-ready)
- `config/*.php` - app/database/AI configuration

## One-click install
1. Point web server doc root to repository root.
2. Open: `http://your-domain/installer/index.php`
3. Fill:
   - App URL
   - DB credentials
   - AWS region/access key/secret/endpoint/model id
   - Admin user details
4. Click **Install Now**

Installer will:
- Create `.env`
- Create required tables
- Insert super admin user

## AWS AI setup notes
Set these in installer (or `.env`):
- `AWS_REGION`
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_AI_ENDPOINT`
- `AWS_AI_MODEL_ID`
- `AWS_AI_ENABLED=true`

`src/AwsAiClient.php` is wired for configuration checks and returns extraction placeholders.
You can connect this to AWS Bedrock or SageMaker Runtime in the `extract()` method.

## Run locally (PHP built-in)
```bash
php -S localhost:8000
```
Then open:
- `http://localhost:8000/public/index.php`
- `http://localhost:8000/admin/index.php`
- `http://localhost:8000/installer/index.php`

## Run collector
```bash
php scripts/collect.php
```
Use cron every 15 minutes:
```bash
*/15 * * * * /usr/bin/php /path/to/project/scripts/collect.php >> /path/to/project/storage/logs/collector.log 2>&1
```

# NetaTrack India (PHP Edition)

This repository is now a **single-stack PHP implementation** with working core modules.

## Implemented Modules
1. **One-click installer** (`installer/index.php`)
   - writes `.env`
   - creates schema from `installer/schema.sql`
   - seeds super admin user
2. **Public website** (`public/index.php`)
   - shows latest promises
   - validates required env keys
3. **Public submission system** (`public/submit.php`)
   - saves reports to `public_submissions`
4. **Admin panel** (`admin/index.php`)
   - login/logout
   - dashboard metrics
   - manual promise upload form
   - submission moderation (approve/reject)
   - AI queue view
5. **JSON APIs** (`public/api.php`)
   - `GET ?path=stats`
   - `GET ?path=promises`
   - `GET ?path=submissions`
   - `POST ?path=submissions`
6. **AI collection pipeline**
   - `scripts/collect.php` cron runner
   - `src/Collector.php` writes to AI queue
   - `src/AwsAiClient.php` AWS AI integration placeholder

## Core Files
- `src/Auth.php` session auth
- `src/Repository.php` data layer for stats/promises/submissions
- `src/Database.php` PDO connection
- `src/EnvValidator.php` startup env validation
- `src/bootstrap.php` `.env` loader

## Installation
1. Open `/installer/index.php`
2. Fill app + DB + AWS + admin details
3. Click **Install Now**

## Local Run
```bash
php -S localhost:8000
```

Open:
- `http://localhost:8000/public/index.php`
- `http://localhost:8000/public/submit.php`
- `http://localhost:8000/admin/index.php`
- `http://localhost:8000/public/api.php?path=stats`

## Cron Collection
```bash
*/15 * * * * /usr/bin/php /path/to/project/scripts/collect.php >> /path/to/project/storage/logs/collector.log 2>&1
```

## AWS AI Notes
Set in installer or `.env`:
- `AWS_REGION`
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_AI_ENDPOINT`
- `AWS_AI_MODEL_ID`

`src/AwsAiClient.php` currently validates configuration and returns placeholder extraction output. Replace `extract()` with AWS Bedrock/SageMaker Runtime invocation.

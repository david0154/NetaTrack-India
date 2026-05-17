# NetaTrack India — Python Desktop Admin App

A lightweight **Tkinter** desktop admin panel that connects directly to your NetaTrack India MySQL database. Manage leaders, approve reports, manage users, edit settings, and trigger the AI scraper — all from your PC.

## Requirements

- Python 3.10+
- MySQL/MariaDB running (local or remote)

## Install & Run

```bash
cd python-admin
pip install -r requirements.txt
python main.py
```

## Tabs

| Tab | Features |
|-----|----------|
| 📊 Dashboard | 6 live stat cards, pending reports list, top leaders table |
| 👤 Leaders | Search, add, edit (with score sliders), delete leaders |
| 📋 Reports | Filter by status, approve/reject with one click |
| 👥 Users | Search, ban/unban, change role (user/moderator/admin) |
| 🤖 Scraper | Trigger scraper via HTTP, live log view, recent jobs table |
| ⚙️ Settings | Edit all site settings (name, logo, analytics, ads, SMTP, AI keys) |

## Connection

On launch, enter your MySQL credentials. Or set env vars to pre-fill:

```bash
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=netatrack
export DB_USER=root
python main.py
```

## Notes

- Works on Windows, Linux, macOS
- No internet required — connects directly to MySQL
- Scraper tab triggers the PHP scraper via HTTP POST (requires site to be running)

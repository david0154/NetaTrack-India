# NetaTrack India — Python Admin

Desktop automation tool for managing NetaTrack India data.
**No MySQL required** — communicates via the PHP REST API.

## Setup

```bash
cd python-admin
pip install -r requirements.txt
cp .env.example .env
# Edit .env with your site URL and API key
```

## Configuration

Set these in `python-admin/.env`:

| Variable | Description |
|---|---|
| `API_BASE_URL` | Your site URL e.g. `https://netatrack.in` |
| `API_KEY` | Generate in Admin Panel → Settings → API Keys |
| `GEMINI_API_KEY` | For AI auto-scoring |
| `SARVAM_API_KEY` | For Hindi language processing |

## Usage

```python
from db.api_client import NetaTrackAPI

api = NetaTrackAPI()  # reads from .env automatically

# List all leaders
leaders = api.leaders.list()

# Create a leader
api.leaders.create({
    'name': 'Narendra Modi',
    'party_id': 1,
    'state_id': 7,
    'role': 'Prime Minister of India'
})

# Update a leader score
api.leaders.update(1, {'total_score': 75})

# Get pending submissions
submissions = api.submissions.list(status='pending')

# Approve a submission
api.submissions.update(42, {'status': 'approved'})
```

## Why API instead of direct MySQL?

- Works on **any hosting** — shared hosting, VPS, Vercel, Render, etc.
- No need to open MySQL TCP port (security risk)
- No `mysql-connector-python` install issues
- PHP handles all DB logic; Python is just an API consumer
- Can run the Python admin from **any machine** — your laptop, GitHub Actions, etc.

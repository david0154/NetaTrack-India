"""
NetaTrack India — Standalone API Client
Use this if you want a clean import without the DBConnection wrapper.

Usage:
    from db.api_client import NetaTrackAPI
    api = NetaTrackAPI(base_url='https://yoursite.com', api_key='YOUR_KEY')
    leaders = api.leaders.list()
"""
import os
import requests
from typing import Optional


class _Resource:
    def __init__(self, session: requests.Session, base: str, endpoint: str):
        self._s   = session
        self._url = f'{base}{endpoint}'

    def list(self, **params) -> list:
        r = self._s.get(self._url, params=params, timeout=10)
        r.raise_for_status()
        return r.json().get('data', [])

    def get(self, id: int) -> Optional[dict]:
        r = self._s.get(self._url, params={'id': id}, timeout=10)
        r.raise_for_status()
        return r.json().get('data')

    def create(self, data: dict) -> dict:
        r = self._s.post(self._url, json=data, timeout=10)
        r.raise_for_status()
        return r.json()

    def update(self, id: int, data: dict) -> dict:
        data['id'] = id
        r = self._s.put(self._url, json=data, timeout=10)
        r.raise_for_status()
        return r.json()

    def delete(self, id: int) -> dict:
        r = self._s.delete(f'{self._url}?id={id}', timeout=10)
        r.raise_for_status()
        return r.json()


class NetaTrackAPI:
    def __init__(self, base_url: str = None, api_key: str = None):
        self._base = (base_url or os.getenv('API_BASE_URL', 'http://localhost')).rstrip('/')
        key        = api_key or os.getenv('API_KEY', '')

        self._session = requests.Session()
        self._session.headers.update({
            'X-API-Key':    key,
            'Content-Type': 'application/json',
            'Accept':       'application/json',
        })

        self.leaders     = _Resource(self._session, self._base, '/api/leaders.php')
        self.parties     = _Resource(self._session, self._base, '/api/parties.php')
        self.states      = _Resource(self._session, self._base, '/api/states.php')
        self.promises    = _Resource(self._session, self._base, '/api/promises.php')
        self.projects    = _Resource(self._session, self._base, '/api/projects.php')
        self.submissions = _Resource(self._session, self._base, '/api/submissions.php')
        self.settings    = _Resource(self._session, self._base, '/api/settings.php')

    def ping(self) -> bool:
        try:
            r = self._session.get(f'{self._base}/api/ping.php', timeout=5)
            return r.status_code == 200
        except Exception:
            return False

    def stats(self) -> dict:
        r = self._session.get(f'{self._base}/api/stats.php', timeout=10)
        r.raise_for_status()
        return r.json().get('data', {})

    def close(self):
        self._session.close()

"""
NetaTrack India — API Client (replaces direct MySQL connection)
All data goes through the PHP REST API so no MySQL driver needed.
Works on any hosting: shared, VPS, Vercel, Render, etc.
"""
import os
import requests
from typing import Optional


class DBConnection:
    """
    Drop-in replacement for the old mysql.connector wrapper.
    All methods now call the PHP API instead of MySQL directly.
    The PHP API must have a valid API key set in settings table.
    """

    def __init__(self):
        self._base_url: str = ''
        self._api_key: str  = ''
        self._connected: bool = False
        self._session = requests.Session()

    # ------------------------------------------------------------------
    # Connection (just sets base URL + key, no socket opened)
    # ------------------------------------------------------------------
    def connect(self, base_url: str = '', api_key: str = '', **kwargs):
        """
        'Connect' = store the API base URL and key.
        Ignores host/user/password/port kwargs so old call sites don't break.
        """
        self._base_url = base_url.rstrip('/')
        self._api_key  = api_key
        self._session.headers.update({
            'X-API-Key':    self._api_key,
            'Content-Type': 'application/json',
            'Accept':       'application/json',
        })
        # Verify connectivity with a lightweight ping
        try:
            r = self._session.get(f'{self._base_url}/api/ping.php', timeout=5)
            self._connected = r.status_code == 200
        except Exception:
            self._connected = False

    def connect_from_env(self):
        self.connect(
            base_url = os.getenv('API_BASE_URL', 'http://localhost'),
            api_key  = os.getenv('API_KEY', ''),
        )

    def is_connected(self) -> bool:
        return self._connected

    # ------------------------------------------------------------------
    # Core request helpers
    # ------------------------------------------------------------------
    def _get(self, endpoint: str, params: dict = None) -> dict:
        url = f'{self._base_url}{endpoint}'
        r = self._session.get(url, params=params, timeout=10)
        r.raise_for_status()
        return r.json()

    def _post(self, endpoint: str, data: dict = None) -> dict:
        url = f'{self._base_url}{endpoint}'
        r = self._session.post(url, json=data or {}, timeout=10)
        r.raise_for_status()
        return r.json()

    def _put(self, endpoint: str, data: dict = None) -> dict:
        url = f'{self._base_url}{endpoint}'
        r = self._session.put(url, json=data or {}, timeout=10)
        r.raise_for_status()
        return r.json()

    def _delete(self, endpoint: str) -> dict:
        url = f'{self._base_url}{endpoint}'
        r = self._session.delete(url, timeout=10)
        r.raise_for_status()
        return r.json()

    # ------------------------------------------------------------------
    # Leaders
    # ------------------------------------------------------------------
    def get_leaders(self, state_id: int = None, party_id: int = None,
                    page: int = 1, per_page: int = 50) -> list:
        params = {'page': page, 'per_page': per_page}
        if state_id: params['state_id'] = state_id
        if party_id: params['party_id'] = party_id
        return self._get('/api/leaders.php', params).get('data', [])

    def get_leader(self, leader_id: int) -> Optional[dict]:
        return self._get(f'/api/leaders.php', {'id': leader_id}).get('data')

    def create_leader(self, data: dict) -> dict:
        return self._post('/api/leaders.php', data)

    def update_leader(self, leader_id: int, data: dict) -> dict:
        data['id'] = leader_id
        return self._put('/api/leaders.php', data)

    def delete_leader(self, leader_id: int) -> dict:
        return self._delete(f'/api/leaders.php?id={leader_id}')

    # ------------------------------------------------------------------
    # Parties
    # ------------------------------------------------------------------
    def get_parties(self) -> list:
        return self._get('/api/parties.php').get('data', [])

    def create_party(self, data: dict) -> dict:
        return self._post('/api/parties.php', data)

    def update_party(self, party_id: int, data: dict) -> dict:
        data['id'] = party_id
        return self._put('/api/parties.php', data)

    # ------------------------------------------------------------------
    # States
    # ------------------------------------------------------------------
    def get_states(self) -> list:
        return self._get('/api/states.php').get('data', [])

    # ------------------------------------------------------------------
    # Promises
    # ------------------------------------------------------------------
    def get_promises(self, leader_id: int = None, status: str = None,
                     page: int = 1, per_page: int = 50) -> list:
        params = {'page': page, 'per_page': per_page}
        if leader_id: params['leader_id'] = leader_id
        if status:    params['status']    = status
        return self._get('/api/promises.php', params).get('data', [])

    def create_promise(self, data: dict) -> dict:
        return self._post('/api/promises.php', data)

    def update_promise(self, promise_id: int, data: dict) -> dict:
        data['id'] = promise_id
        return self._put('/api/promises.php', data)

    # ------------------------------------------------------------------
    # Projects
    # ------------------------------------------------------------------
    def get_projects(self, leader_id: int = None, state_id: int = None,
                     page: int = 1, per_page: int = 50) -> list:
        params = {'page': page, 'per_page': per_page}
        if leader_id: params['leader_id'] = leader_id
        if state_id:  params['state_id']  = state_id
        return self._get('/api/projects.php', params).get('data', [])

    def create_project(self, data: dict) -> dict:
        return self._post('/api/projects.php', data)

    def update_project(self, project_id: int, data: dict) -> dict:
        data['id'] = project_id
        return self._put('/api/projects.php', data)

    # ------------------------------------------------------------------
    # Submissions
    # ------------------------------------------------------------------
    def get_submissions(self, status: str = 'pending',
                        page: int = 1, per_page: int = 50) -> list:
        return self._get('/api/submissions.php',
                         {'status': status, 'page': page,
                          'per_page': per_page}).get('data', [])

    def approve_submission(self, sub_id: int) -> dict:
        return self._put('/api/submissions.php', {'id': sub_id, 'status': 'approved'})

    def reject_submission(self, sub_id: int, reason: str = '') -> dict:
        return self._put('/api/submissions.php',
                         {'id': sub_id, 'status': 'rejected', 'reason': reason})

    # ------------------------------------------------------------------
    # Analytics / Stats
    # ------------------------------------------------------------------
    def get_stats(self) -> dict:
        return self._get('/api/stats.php').get('data', {})

    # ------------------------------------------------------------------
    # Settings
    # ------------------------------------------------------------------
    def get_settings(self) -> dict:
        return self._get('/api/settings.php').get('data', {})

    def update_setting(self, key: str, value: str) -> dict:
        return self._put('/api/settings.php', {'key': key, 'value': value})

    # ------------------------------------------------------------------
    # Legacy compatibility: raw SQL methods now raise clear error
    # ------------------------------------------------------------------
    def execute(self, sql, params=None):
        raise NotImplementedError(
            'Direct SQL removed. Use API methods like get_leaders(), '
            'create_leader() etc. or call self._post(endpoint, data).'
        )

    def fetchone(self, sql, params=None):
        raise NotImplementedError('Direct SQL removed. Use API methods.')

    def fetchall(self, sql, params=None):
        raise NotImplementedError('Direct SQL removed. Use API methods.')

    def close(self):
        self._session.close()
        self._connected = False

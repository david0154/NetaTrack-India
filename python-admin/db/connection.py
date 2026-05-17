import mysql.connector
from mysql.connector import Error


class DBConnection:
    def __init__(self, host, port, dbname, user, password):
        self.config = dict(host=host, port=int(port), database=dbname, user=user, password=password,
                           charset="utf8mb4", autocommit=True, connection_timeout=10)
        self._conn = None
        self.connect()

    def connect(self):
        self._conn = mysql.connector.connect(**self.config)

    def _ensure(self):
        try:
            self._conn.ping(reconnect=True, attempts=3, delay=1)
        except Exception:
            self.connect()

    def fetchall(self, sql, params=()):
        self._ensure()
        cur = self._conn.cursor(dictionary=True)
        cur.execute(sql, params)
        return cur.fetchall()

    def fetchone(self, sql, params=()):
        self._ensure()
        cur = self._conn.cursor(dictionary=True)
        cur.execute(sql, params)
        return cur.fetchone()

    def execute(self, sql, params=()):
        self._ensure()
        cur = self._conn.cursor()
        cur.execute(sql, params)
        return cur.lastrowid

    def close(self):
        if self._conn and self._conn.is_connected():
            self._conn.close()

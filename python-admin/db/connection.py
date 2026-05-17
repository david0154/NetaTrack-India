"""
NetaTrack India — DB Connection wrapper
"""
import os


class DBConnection:
    def __init__(self):
        self._conn   = None
        self._connected = False

    def connect(self, host='127.0.0.1', database='netatrack',
                user='root', password='', port=3306):
        import mysql.connector
        self._conn = mysql.connector.connect(
            host=host, database=database,
            user=user, password=password,
            port=int(port),
            charset='utf8mb4',
            collation='utf8mb4_unicode_ci',
            autocommit=True
        )
        self._connected = True

    def connect_from_env(self):
        self.connect(
            host=os.getenv('DB_HOST', '127.0.0.1'),
            database=os.getenv('DB_NAME', 'netatrack'),
            user=os.getenv('DB_USER', 'root'),
            password=os.getenv('DB_PASS', ''),
            port=int(os.getenv('DB_PORT', 3306))
        )

    def is_connected(self) -> bool:
        return self._connected and self._conn is not None

    def execute(self, sql: str, params=None):
        cur = self._conn.cursor(dictionary=True)
        cur.execute(sql, params or ())
        cur.close()

    def fetchone(self, sql: str, params=None) -> dict | None:
        cur = self._conn.cursor(dictionary=True)
        cur.execute(sql, params or ())
        row = cur.fetchone()
        cur.close()
        return row

    def fetchall(self, sql: str, params=None) -> list:
        cur = self._conn.cursor(dictionary=True)
        cur.execute(sql, params or ())
        rows = cur.fetchall()
        cur.close()
        return rows

    def close(self):
        if self._conn:
            self._conn.close()
            self._connected = False

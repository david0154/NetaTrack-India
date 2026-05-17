"""
NetaTrack India — Database Seeder
Runs the SQL seed file: all states, parties, and current leaders.

Usage:
    python seed_db.py
    python seed_db.py --host 127.0.0.1 --db netatrack --user root --password secret
"""
import argparse
import os
import sys


def run_seed(host='127.0.0.1', db='netatrack', user='root', password='', port=3306):
    try:
        import mysql.connector
    except ImportError:
        print("[!] Run: pip install mysql-connector-python")
        sys.exit(1)

    sql_file = os.path.join(
        os.path.dirname(__file__), '..', 'database', 'seed_states_leaders.sql'
    )
    if not os.path.exists(sql_file):
        print(f"[!] SQL file not found: {sql_file}")
        sys.exit(1)

    with open(sql_file, 'r', encoding='utf-8') as f:
        raw = f.read()

    # Split into individual statements
    statements = [s.strip() for s in raw.split(';') if s.strip() and not s.strip().startswith('--')]

    conn = mysql.connector.connect(
        host=host, database=db, user=user, password=password, port=port,
        charset='utf8mb4', collation='utf8mb4_unicode_ci'
    )
    cursor = conn.cursor()
    ok = 0
    errors = 0
    for stmt in statements:
        if not stmt: continue
        try:
            cursor.execute(stmt)
            if cursor.with_rows:
                rows = cursor.fetchall()
                for row in rows:
                    print(f"   {row[0]}")
            ok += 1
        except Exception as e:
            err = str(e)
            if 'Duplicate entry' in err or '1062' in err:
                continue  # already seeded, skip
            print(f"[!] {err[:120]}")
            errors += 1
    conn.commit()
    cursor.close()
    conn.close()
    print(f"\n[✓] Seed complete. {ok} statements OK, {errors} errors.")


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='NetaTrack India DB Seeder')
    parser.add_argument('--host',     default=os.getenv('DB_HOST', '127.0.0.1'))
    parser.add_argument('--db',       default=os.getenv('DB_NAME', 'netatrack'))
    parser.add_argument('--user',     default=os.getenv('DB_USER', 'root'))
    parser.add_argument('--password', default=os.getenv('DB_PASS', ''))
    parser.add_argument('--port',     default=int(os.getenv('DB_PORT', 3306)), type=int)
    args = parser.parse_args()
    print(f"Seeding {args.db}@{args.host}:{args.port}...")
    run_seed(args.host, args.db, args.user, args.password, args.port)

/**
 * NetaTrack India - Database helper for Next.js API routes
 * Uses mysql2 with connection pooling
 */
import mysql from 'mysql2/promise'

declare global {
  // eslint-disable-next-line no-var
  var _mysqlPool: mysql.Pool | undefined
}

function createPool(): mysql.Pool {
  return mysql.createPool({
    host:               process.env.DB_HOST     ?? '127.0.0.1',
    port:               parseInt(process.env.DB_PORT ?? '3306'),
    database:           process.env.DB_NAME     ?? 'netatrack_india',
    user:               process.env.DB_USER     ?? 'root',
    password:           process.env.DB_PASS     ?? '',
    charset:            'utf8mb4',
    waitForConnections: true,
    connectionLimit:    10,
    queueLimit:         0,
    timezone:           '+05:30',
  })
}

const pool: mysql.Pool = global._mysqlPool ?? createPool()
if (process.env.NODE_ENV !== 'production') global._mysqlPool = pool

export default pool

export async function query<T = Record<string, unknown>>(sql: string, params?: unknown[]): Promise<T[]> {
  const [rows] = await pool.execute(sql, params)
  return rows as T[]
}

export async function queryOne<T = Record<string, unknown>>(sql: string, params?: unknown[]): Promise<T | null> {
  const rows = await query<T>(sql, params)
  return rows[0] ?? null
}

// Optimised paginate: strips ORDER BY from COUNT query to avoid expensive subquery
export async function paginate<T = Record<string, unknown>>(
  baseSql: string,
  params: unknown[] = [],
  page = 1,
  perPage = 20
): Promise<{ data: T[]; total: number; page: number; perPage: number; totalPages: number }> {
  // Strip ORDER BY for count to improve performance
  const countSql = baseSql.replace(/\s+ORDER\s+BY\s+.*/is, '')
  const countRow = await queryOne<{ total: number }>(
    `SELECT COUNT(*) as total FROM (${countSql}) as _count_sub`, params
  )
  const total = countRow?.total ?? 0
  const offset = (page - 1) * perPage
  const data = await query<T>(`${baseSql} LIMIT ? OFFSET ?`, [...params, perPage, offset])
  return { data, total, page, perPage, totalPages: Math.ceil(total / perPage) }
}

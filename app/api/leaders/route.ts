import { NextRequest } from 'next/server'
import { paginate, query } from '@/lib/db'
import { ok, serverError } from '@/lib/apiResponse'

export const dynamic = 'force-dynamic'

export async function GET(request: NextRequest) {
  try {
    const sp      = request.nextUrl.searchParams
    const page    = parseInt(sp.get('page')    ?? '1')
    const perPage = parseInt(sp.get('per_page')?? '20')
    const stateId = sp.get('state_id')
    const partyId = sp.get('party_id')
    const rank    = sp.get('rank')
    const search  = sp.get('search')
    const sortBy  = sp.get('sort') ?? 'final_score'
    const order   = sp.get('order') === 'asc' ? 'ASC' : 'DESC'

    const allowedSort = ['final_score', 'corruption_score', 'name', 'created_at']
    const safeSort    = allowedSort.includes(sortBy) ? sortBy : 'final_score'

    let sql = `
      SELECT
        l.id, l.name, l.slug, l.photo, l.designation, l.position,
        l.final_score, l.rank, l.corruption_score, l.corruption_level,
        l.criminal_cases, l.is_verified,
        p.name AS party_name, p.abbreviation AS party_abbr, p.color_code,
        s.name AS state_name
      FROM leaders l
      LEFT JOIN parties p ON p.id = l.party_id
      LEFT JOIN states  s ON s.id = l.state_id
      WHERE l.is_active = 1
    `
    const params: unknown[] = []

    if (stateId) { sql += ' AND l.state_id = ?'; params.push(stateId) }
    if (partyId) { sql += ' AND l.party_id = ?'; params.push(partyId) }
    if (rank)    { sql += ' AND l.rank = ?';     params.push(rank) }
    if (search)  { sql += ' AND (l.name LIKE ? OR l.constituency LIKE ?)'; params.push(`%${search}%`, `%${search}%`) }

    sql += ` ORDER BY l.${safeSort} ${order}`

    const result = await paginate(sql, params, page, perPage)

    // Get all parties & states for filters
    const [parties, states] = await Promise.all([
      query('SELECT id, name, abbreviation, color_code FROM parties WHERE is_active=1 ORDER BY name'),
      query('SELECT id, name, slug FROM states WHERE is_active=1 ORDER BY name'),
    ])

    return ok(result.data, { pagination: { total: result.total, page, perPage, totalPages: result.totalPages }, filters: { parties, states } })
  } catch (e) {
    return serverError(e)
  }
}

import { NextRequest } from 'next/server'
import { paginate } from '@/lib/db'
import { ok, serverError } from '@/lib/apiResponse'

export const dynamic = 'force-dynamic'

export async function GET(request: NextRequest) {
  try {
    const sp       = request.nextUrl.searchParams
    const page     = parseInt(sp.get('page')    ?? '1')
    const perPage  = parseInt(sp.get('per_page')?? '20')
    const status   = sp.get('status')
    const category = sp.get('category')
    const stateId  = sp.get('state_id')
    const leaderId = sp.get('leader_id')
    const search   = sp.get('search')

    let sql = `
      SELECT
        p.id, p.title, p.slug, p.status, p.category, p.promised_on, p.deadline,
        p.completion_percentage, p.ai_confidence, p.ai_summary, p.source_type, p.is_verified,
        l.name AS leader_name, l.slug AS leader_slug, l.photo AS leader_photo,
        pa.abbreviation AS party_abbr, pa.color_code,
        s.name AS state_name
      FROM promises p
      LEFT JOIN leaders l  ON l.id  = p.leader_id
      LEFT JOIN parties pa ON pa.id = p.party_id
      LEFT JOIN states  s  ON s.id  = p.state_id
      WHERE 1=1
    `
    const params: unknown[] = []

    if (status)   { sql += ' AND p.status = ?';      params.push(status) }
    if (category) { sql += ' AND p.category = ?';    params.push(category) }
    if (stateId)  { sql += ' AND p.state_id = ?';    params.push(stateId) }
    if (leaderId) { sql += ' AND p.leader_id = ?';   params.push(leaderId) }
    if (search)   { sql += ' AND MATCH(p.title, p.description) AGAINST(? IN BOOLEAN MODE)'; params.push(`${search}*`) }

    sql += ' ORDER BY p.promised_on DESC'

    const result = await paginate(sql, params, page, perPage)
    return ok(result.data, { pagination: { total: result.total, page, perPage, totalPages: result.totalPages } })
  } catch (e) {
    return serverError(e)
  }
}

import { NextRequest } from 'next/server'
import { paginate } from '@/lib/db'
import { ok, serverError } from '@/lib/apiResponse'

export const dynamic = 'force-dynamic'

export async function GET(request: NextRequest) {
  try {
    const sp      = request.nextUrl.searchParams
    const page    = parseInt(sp.get('page')    ?? '1')
    const perPage = parseInt(sp.get('per_page')?? '20')
    const type    = sp.get('type')
    const status  = sp.get('status')

    let sql = `
      SELECT
        cc.id, cc.case_title, cc.type, cc.agency, cc.status,
        cc.amount_involved, cc.started_at, cc.ai_severity_score, cc.is_verified,
        l.name AS leader_name, l.slug AS leader_slug, l.photo AS leader_photo,
        p.name AS party_name, p.abbreviation AS party_abbr, p.color_code,
        s.name AS state_name
      FROM corruption_cases cc
      LEFT JOIN leaders l ON l.id = cc.leader_id
      LEFT JOIN parties p ON p.id = l.party_id
      LEFT JOIN states  s ON s.id = l.state_id
      WHERE 1=1
    `
    const params: unknown[] = []
    if (type)   { sql += ' AND cc.type = ?';   params.push(type) }
    if (status) { sql += ' AND cc.status = ?'; params.push(status) }
    sql += ' ORDER BY cc.ai_severity_score DESC, cc.started_at DESC'

    const result = await paginate(sql, params, page, perPage)
    return ok(result.data, { pagination: { total: result.total, page, perPage, totalPages: result.totalPages } })
  } catch (e) {
    return serverError(e)
  }
}

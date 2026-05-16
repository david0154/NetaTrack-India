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

    let sql = `
      SELECT
        pr.id, pr.title, pr.slug, pr.status, pr.category, pr.progress_percentage,
        pr.budget_allocated, pr.budget_spent, pr.budget_efficiency,
        pr.start_date, pr.expected_completion, pr.delay_days, pr.is_verified,
        l.name AS leader_name, l.slug AS leader_slug,
        s.name AS state_name, s.code AS state_code
      FROM projects pr
      LEFT JOIN leaders l ON l.id = pr.leader_id
      LEFT JOIN states  s ON s.id = pr.state_id
      WHERE 1=1
    `
    const params: unknown[] = []

    if (status)   { sql += ' AND pr.status = ?';    params.push(status) }
    if (category) { sql += ' AND pr.category = ?';  params.push(category) }
    if (stateId)  { sql += ' AND pr.state_id = ?';  params.push(stateId) }

    sql += ' ORDER BY pr.start_date DESC'

    const result = await paginate(sql, params, page, perPage)
    return ok(result.data, { pagination: { total: result.total, page, perPage, totalPages: result.totalPages } })
  } catch (e) {
    return serverError(e)
  }
}

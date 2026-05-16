import { NextRequest } from 'next/server'
import { query } from '@/lib/db'
import { ok, badRequest, serverError } from '@/lib/apiResponse'

export const dynamic = 'force-dynamic'

export async function GET(request: NextRequest) {
  try {
    const q = request.nextUrl.searchParams.get('q')?.trim()
    if (!q || q.length < 2) return badRequest('Query must be at least 2 characters')

    const term = `%${q}%`
    const boolTerm = `${q}*`

    const [leaders, promises, projects] = await Promise.all([
      query(
        `SELECT id, name, slug, photo, designation, final_score, rank, 'leader' AS type
         FROM leaders WHERE is_active=1 AND name LIKE ? ORDER BY final_score DESC LIMIT 5`,
        [term]
      ),
      query(
        `SELECT id, title, slug, status, category, 'promise' AS type
         FROM promises WHERE MATCH(title, description) AGAINST(? IN BOOLEAN MODE) LIMIT 5`,
        [boolTerm]
      ),
      query(
        `SELECT id, title, slug, status, category, 'project' AS type
         FROM projects WHERE MATCH(title, description) AGAINST(? IN BOOLEAN MODE) LIMIT 5`,
        [boolTerm]
      ),
    ])

    return ok({ leaders, promises, projects, query: q })
  } catch (e) {
    return serverError(e)
  }
}

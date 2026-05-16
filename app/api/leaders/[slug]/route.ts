import { NextRequest } from 'next/server'
import { query, queryOne } from '@/lib/db'
import { ok, notFound, serverError } from '@/lib/apiResponse'

export const dynamic = 'force-dynamic'

export async function GET(_req: NextRequest, { params }: { params: { slug: string } }) {
  try {
    const leader = await queryOne(`
      SELECT
        l.*,
        p.name AS party_name, p.abbreviation AS party_abbr, p.color_code, p.logo AS party_logo,
        s.name AS state_name, s.code AS state_code
      FROM leaders l
      LEFT JOIN parties p ON p.id = l.party_id
      LEFT JOIN states  s ON s.id = l.state_id
      WHERE (l.slug = ? OR l.id = ?) AND l.is_active = 1
    `, [params.slug, params.slug])

    if (!leader) return notFound('Leader not found')

    // Increment view count (best effort)
    queryOne('UPDATE leaders SET views = views + 1 WHERE slug = ?', [params.slug]).catch(() => {})

    const leaderId = (leader as Record<string, unknown>).id

    const [promises, projects, corruptionCases] = await Promise.all([
      query(`
        SELECT id, title, slug, status, category, promised_on, deadline, completion_percentage, ai_summary
        FROM promises WHERE leader_id = ? ORDER BY promised_on DESC LIMIT 20
      `, [leaderId]),
      query(`
        SELECT id, title, slug, status, category, progress_percentage, budget_allocated, budget_spent, start_date, expected_completion
        FROM projects WHERE leader_id = ? ORDER BY start_date DESC LIMIT 20
      `, [leaderId]),
      query(`
        SELECT id, case_title, type, agency, status, amount_involved, started_at, ai_severity_score
        FROM corruption_cases WHERE leader_id = ? ORDER BY started_at DESC
      `, [leaderId]),
    ])

    return ok({ ...leader, promises, projects, corruption_cases: corruptionCases })
  } catch (e) {
    return serverError(e)
  }
}

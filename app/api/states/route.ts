import { query } from '@/lib/db'
import { ok, serverError } from '@/lib/apiResponse'

export const dynamic = 'force-dynamic'

export async function GET() {
  try {
    const states = await query(`
      SELECT
        s.id, s.name, s.slug, s.code, s.region, s.capital,
        s.current_ruling_party, s.cm_name, s.corruption_index,
        s.infrastructure_score, s.latitude, s.longitude,
        COUNT(DISTINCT l.id)  AS total_leaders,
        COUNT(DISTINCT p.id)  AS total_promises,
        COUNT(DISTINCT pr.id) AS total_projects,
        COUNT(DISTINCT cc.id) AS total_corruption_cases
      FROM states s
      LEFT JOIN leaders          l  ON l.state_id  = s.id AND l.is_active = 1
      LEFT JOIN promises         p  ON p.state_id  = s.id
      LEFT JOIN projects         pr ON pr.state_id = s.id
      LEFT JOIN corruption_cases cc ON cc.leader_id IN (SELECT id FROM leaders WHERE state_id = s.id)
      WHERE s.is_active = 1
      GROUP BY s.id
      ORDER BY s.name
    `)
    return ok(states)
  } catch (e) {
    return serverError(e)
  }
}

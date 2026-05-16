import { NextResponse } from 'next/server'
import { query } from '@/lib/db'
import { serverError } from '@/lib/apiResponse'

export const dynamic = 'force-dynamic'

export async function GET() {
  try {
    const [promises, projects, reports, leaders, corruption] = await Promise.all([
      query('SELECT COUNT(*) as total, SUM(status="Completed") as completed, SUM(status="Broken") as broken FROM promises'),
      query('SELECT COUNT(*) as total, SUM(status="Delayed") as delayed, SUM(status="Stalled") as stalled FROM projects'),
      query('SELECT COUNT(*) as total, SUM(status="Approved") as approved FROM public_reports'),
      query('SELECT COUNT(*) as total FROM leaders WHERE is_active=1'),
      query('SELECT COUNT(*) as total FROM corruption_cases'),
    ])

    const p  = (promises[0]  as Record<string, number>)
    const pr = (projects[0]  as Record<string, number>)
    const r  = (reports[0]   as Record<string, number>)
    const l  = (leaders[0]   as Record<string, number>)
    const c  = (corruption[0] as Record<string, number>)

    return NextResponse.json({
      success: true,
      data: {
        promises_tracked:       Number(p.total)       || 0,
        promises_completed:     Number(p.completed)   || 0,
        promises_broken:        Number(p.broken)      || 0,
        projects_monitored:     Number(pr.total)      || 0,
        delayed_projects:       Number(pr.delayed) + Number(pr.stalled) || 0,
        corruption_allegations: Number(c.total)       || 0,
        fake_claims_detected:   0,  // populated by AI scraper
        verified_reports:       Number(r.approved)    || 0,
        public_submissions:     Number(r.total)       || 0,
        leaders_tracked:        Number(l.total)       || 0,
      },
    })
  } catch (e) {
    return serverError(e)
  }
}

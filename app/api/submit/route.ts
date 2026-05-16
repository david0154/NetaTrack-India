import { NextRequest } from 'next/server'
import { ok, badRequest, serverError } from '@/lib/apiResponse'
import pool from '@/lib/db'

export const dynamic = 'force-dynamic'

const REPORT_TYPES = ['Corruption', 'Fake Claim', 'Infrastructure Damage', 'Project Update', 'Public Complaint', 'RTI Document', 'Other']

export async function POST(request: NextRequest) {
  try {
    const body = await request.json()
    const { report_type, title, description, state_id, district, leader_id, submitter_name, submitter_email, is_anonymous } = body

    // Validation
    if (!report_type || !REPORT_TYPES.includes(report_type)) return badRequest('Invalid report type')
    if (!title || title.trim().length < 10)                  return badRequest('Title must be at least 10 characters')
    if (!description || description.trim().length < 30)      return badRequest('Description must be at least 30 characters')

    // Basic spam detection
    const spamWords = ['test', 'asdf', 'lorem ipsum', 'xxxxx']
    if (spamWords.some(w => title.toLowerCase().includes(w))) return badRequest('Submission detected as spam')

    const ip = request.headers.get('x-forwarded-for')?.split(',')[0] ?? request.headers.get('x-real-ip') ?? 'unknown'

    const [result] = await pool.execute(
      `INSERT INTO public_reports
        (report_type, title, description, state_id, district, leader_id, submitter_name, submitter_email, is_anonymous, ip_address, status)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')`,
      [
        report_type,
        title.trim().substring(0, 500),
        description.trim().substring(0, 5000),
        state_id   ?? null,
        district   ?? null,
        leader_id  ?? null,
        is_anonymous ? null : (submitter_name  ?? null),
        is_anonymous ? null : (submitter_email ?? null),
        is_anonymous ? 1 : 0,
        ip,
      ]
    )
    const insertId = (result as { insertId: number }).insertId

    return ok({ id: insertId, message: 'Report submitted successfully. It will be reviewed by our team.' })
  } catch (e) {
    return serverError(e)
  }
}

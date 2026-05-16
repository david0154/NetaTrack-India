import { NextResponse } from 'next/server';
import { queryOne } from '@/lib/db';

export const dynamic = 'force-dynamic';

export async function GET() {
  try {
    const stats = await queryOne<any>(`
      SELECT
        (SELECT COUNT(*) FROM promises WHERE is_active = 1) AS promises_tracked,
        (SELECT COUNT(*) FROM projects WHERE is_active = 1) AS projects_monitored,
        (SELECT COUNT(*) FROM projects WHERE status = 'delayed' AND is_active = 1) AS delayed_projects,
        (SELECT COUNT(*) FROM corruption_cases) AS corruption_cases,
        (SELECT COUNT(*) FROM public_reports WHERE ai_verified = 1 AND status = 'approved') AS verified_reports,
        (SELECT COUNT(*) FROM public_reports WHERE ai_fake_score > 70) AS fake_claims_detected,
        (SELECT COUNT(*) FROM public_reports) AS public_submissions,
        (SELECT COUNT(*) FROM leaders WHERE is_active = 1) AS leaders_tracked
    `);
    return NextResponse.json({ success: true, data: stats });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

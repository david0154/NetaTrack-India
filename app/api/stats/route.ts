import { NextResponse } from 'next/server';
import { query } from '@/lib/db';

export async function GET() {
  try {
    const rows = await query<{k:string,v:number}>(`
      SELECT 'promises_tracked'   AS k, COUNT(*) AS v FROM promises
      UNION ALL
      SELECT 'projects_monitored',  COUNT(*) FROM projects
      UNION ALL
      SELECT 'delayed_projects',    COUNT(*) FROM projects WHERE status='delayed'
      UNION ALL
      SELECT 'corruption_cases',    COUNT(*) FROM corruption_cases
      UNION ALL
      SELECT 'fake_claims_detected',COUNT(*) FROM promises WHERE status='fake'
      UNION ALL
      SELECT 'verified_reports',    COUNT(*) FROM public_submissions WHERE ai_verified=1 AND status='approved'
      UNION ALL
      SELECT 'public_submissions',  COUNT(*) FROM public_submissions
      UNION ALL
      SELECT 'leaders_tracked',     COUNT(*) FROM leaders WHERE is_active=1
    `);
    const data: Record<string,number> = {};
    rows.forEach(r => { data[r.k] = Number(r.v); });
    return NextResponse.json({ success: true, data });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

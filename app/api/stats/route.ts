import { NextResponse } from 'next/server';
import { queryOne } from '@/lib/db';

export async function GET() {
  try {
    const [promises, projects, delayed, corruption, subs, leaders] = await Promise.all([
      queryOne<any>('SELECT COUNT(*) as c FROM promises'),
      queryOne<any>('SELECT COUNT(*) as c FROM projects'),
      queryOne<any>("SELECT COUNT(*) as c FROM projects WHERE status='delayed'"),
      queryOne<any>('SELECT COUNT(*) as c FROM corruption_cases'),
      queryOne<any>("SELECT COUNT(*) as c FROM public_submissions WHERE status='approved'"),
      queryOne<any>('SELECT COUNT(*) as c FROM leaders WHERE is_active=1'),
    ]);
    return NextResponse.json({
      success: true,
      data: {
        promises_tracked: promises?.c ?? 0,
        projects_monitored: projects?.c ?? 0,
        delayed_projects: delayed?.c ?? 0,
        corruption_cases: corruption?.c ?? 0,
        verified_reports: subs?.c ?? 0,
        leaders_tracked: leaders?.c ?? 0,
      },
    });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

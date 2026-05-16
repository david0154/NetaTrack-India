import { NextRequest, NextResponse } from 'next/server';
import { query } from '@/lib/db';

export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  try {
    const q = req.nextUrl.searchParams.get('q')?.trim();
    if (!q || q.length < 2) {
      return NextResponse.json({ success: false, error: 'Query too short' }, { status: 400 });
    }
    const like = `%${q}%`;
    const [leaders, promises, projects, corruptions] = await Promise.all([
      query<any>(`SELECT id, slug, name, designation, final_score, rank FROM leaders WHERE name LIKE ? AND is_active=1 LIMIT 5`, [like]),
      query<any>(`SELECT id, slug, title, status, category FROM promises WHERE title LIKE ? AND is_active=1 LIMIT 5`, [like]),
      query<any>(`SELECT id, slug, title, status, progress_percent FROM projects WHERE title LIKE ? AND is_active=1 LIMIT 5`, [like]),
      query<any>(`SELECT id, title, agency, severity, status FROM corruption_cases WHERE title LIKE ? LIMIT 5`, [like]),
    ]);
    return NextResponse.json({ success: true, data: { leaders, promises, projects, corruptions } });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

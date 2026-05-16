import { NextRequest, NextResponse } from 'next/server';
import { query } from '@/lib/db';

export async function GET(req: NextRequest) {
  const q = req.nextUrl.searchParams.get('q')?.trim();
  if (!q || q.length < 2)
    return NextResponse.json({ success: false, error: 'Query too short' }, { status: 400 });
  const like = `%${q}%`;

  const [leaders, promises, projects, corruption] = await Promise.all([
    query<any>(`SELECT l.id, l.slug, l.name, l.position, l.final_score, l.rank_label, l.photo_url, p.name as party_name, s.name as state_name
      FROM leaders l LEFT JOIN parties p ON l.party_id=p.id LEFT JOIN states s ON l.state_id=s.id
      WHERE l.name LIKE ? AND l.is_active=1 LIMIT 5`, [like]),
    query<any>(`SELECT id, slug, title, status, category FROM promises WHERE title LIKE ? LIMIT 5`, [like]),
    query<any>(`SELECT id, slug, title, status, progress_percent FROM projects WHERE title LIKE ? LIMIT 5`, [like]),
    query<any>(`SELECT cc.id, cc.title, cc.agency, cc.severity, l.name as leader_name FROM corruption_cases cc LEFT JOIN leaders l ON cc.leader_id=l.id WHERE cc.title LIKE ? LIMIT 5`, [like]),
  ]);

  return NextResponse.json({
    success: true,
    data: { leaders, promises, projects, corruption },
  });
}

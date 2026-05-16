import { NextRequest, NextResponse } from 'next/server';
import { query } from '@/lib/db';

export async function GET(req: NextRequest) {
  try {
    const q = new URL(req.url).searchParams.get('q') || '';
    if (q.length < 2) return NextResponse.json({ success: true, data: [] });

    const like = `%${q}%`;

    const leaders = await query(
      `SELECT id, slug, name, 'leader' AS type, position AS subtitle, final_score AS score
       FROM leaders WHERE name LIKE ? AND is_active=1 LIMIT 5`, [like]
    );
    const promises = await query(
      `SELECT id, slug, title AS name, 'promise' AS type, status AS subtitle, ai_confidence AS score
       FROM promises WHERE title LIKE ? LIMIT 5`, [like]
    );
    const projects = await query(
      `SELECT id, slug, title AS name, 'project' AS type, status AS subtitle, progress_percent AS score
       FROM projects WHERE title LIKE ? LIMIT 5`, [like]
    );
    const cases = await query(
      `SELECT id, id AS slug, title AS name, 'corruption' AS type, severity AS subtitle, 0 AS score
       FROM corruption_cases WHERE title LIKE ? LIMIT 5`, [like]
    );

    return NextResponse.json({
      success: true,
      data: [...leaders, ...promises, ...projects, ...cases]
    });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

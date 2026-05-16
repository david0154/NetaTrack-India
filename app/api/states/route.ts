import { NextResponse } from 'next/server';
import { query } from '@/lib/db';

export async function GET() {
  try {
    const states = await query(
      `SELECT s.*, COUNT(DISTINCT l.id) AS leaders_count,
              COUNT(DISTINCT pj.id) AS projects_count,
              COUNT(DISTINCT pr.id) AS promises_count
       FROM states s
       LEFT JOIN leaders l  ON l.state_id  = s.id AND l.is_active=1
       LEFT JOIN projects pj ON pj.state_id = s.id
       LEFT JOIN promises pr ON pr.state_id = s.id
       GROUP BY s.id
       ORDER BY s.name ASC`
    );
    return NextResponse.json({ success: true, data: states });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

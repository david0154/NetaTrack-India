import { NextResponse } from 'next/server';
import { query } from '@/lib/db';

export const dynamic = 'force-dynamic';

export async function GET() {
  try {
    const states = await query<any>(
      `SELECT s.id, s.name, s.code, s.capital, s.region, s.population,
        COUNT(DISTINCT l.id) AS leaders_count,
        COUNT(DISTINCT p.id) AS promises_count,
        COUNT(DISTINCT pr.id) AS projects_count
       FROM states s
       LEFT JOIN leaders l ON l.state_id = s.id AND l.is_active = 1
       LEFT JOIN promises p ON p.state_id = s.id AND p.is_active = 1
       LEFT JOIN projects pr ON pr.state_id = s.id AND pr.is_active = 1
       WHERE s.is_active = 1
       GROUP BY s.id ORDER BY s.name`
    );
    return NextResponse.json({ success: true, data: states });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

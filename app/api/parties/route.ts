import { NextResponse } from 'next/server';
import { query } from '@/lib/db';

export const dynamic = 'force-dynamic';

export async function GET() {
  try {
    const parties = await query<any>(
      `SELECT p.id, p.name, p.abbreviation, p.color_code, p.symbol_url, p.ideology,
        COUNT(DISTINCT l.id) AS leaders_count
       FROM parties p
       LEFT JOIN leaders l ON l.party_id = p.id AND l.is_active = 1
       WHERE p.is_active = 1
       GROUP BY p.id ORDER BY p.name`
    );
    return NextResponse.json({ success: true, data: parties });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

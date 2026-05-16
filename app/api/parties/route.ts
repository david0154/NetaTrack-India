import { NextResponse } from 'next/server';
import { query } from '@/lib/db';

export async function GET() {
  try {
    const parties = await query(
      `SELECT p.*, COUNT(l.id) AS leaders_count
       FROM parties p
       LEFT JOIN leaders l ON l.party_id = p.id AND l.is_active = 1
       GROUP BY p.id
       ORDER BY leaders_count DESC`
    );
    return NextResponse.json({ success: true, data: parties });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

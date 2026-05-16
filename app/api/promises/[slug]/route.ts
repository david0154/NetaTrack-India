import { NextRequest, NextResponse } from 'next/server';
import { queryOne } from '@/lib/db';

export async function GET(_: NextRequest, { params }: { params: { slug: string } }) {
  try {
    const row = await queryOne(
      `SELECT pr.*, l.name AS leader_name, l.slug AS leader_slug,
              s.name AS state_name, p.name AS party_name
       FROM promises pr
       LEFT JOIN leaders l ON l.id = pr.leader_id
       LEFT JOIN states  s ON s.id = pr.state_id
       LEFT JOIN parties p ON p.id = l.party_id
       WHERE pr.slug = ?`,
      [params.slug]
    );
    if (!row) return NextResponse.json({ success: false, error: 'Not found' }, { status: 404 });
    return NextResponse.json({ success: true, data: row });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

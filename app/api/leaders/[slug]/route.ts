import { NextRequest, NextResponse } from 'next/server';
import { queryOne, query } from '@/lib/db';

export async function GET(_: NextRequest, { params }: { params: { slug: string } }) {
  try {
    const leader = await queryOne(
      `SELECT l.*, p.name AS party_name, p.abbreviation AS party_abbr,
              s.name AS state_name, s.capital AS state_capital
       FROM leaders l
       LEFT JOIN parties p ON p.id = l.party_id
       LEFT JOIN states  s ON s.id = l.state_id
       WHERE l.slug = ? AND l.is_active = 1`,
      [params.slug]
    );
    if (!leader) return NextResponse.json({ success: false, error: 'Not found' }, { status: 404 });

    const promises = await query(
      `SELECT id, slug, title, status, category, deadline, ai_confidence FROM promises WHERE leader_id = ? ORDER BY created_at DESC LIMIT 10`,
      [(leader as any).id]
    );
    const projects = await query(
      `SELECT id, slug, title, status, progress_percent, budget FROM projects WHERE leader_id = ? ORDER BY created_at DESC LIMIT 10`,
      [(leader as any).id]
    );
    const corruption = await query(
      `SELECT id, title, agency, severity, status, amount_crore FROM corruption_cases WHERE leader_id = ? ORDER BY created_at DESC LIMIT 5`,
      [(leader as any).id]
    );

    return NextResponse.json({ success: true, data: { ...leader, promises, projects, corruption } });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

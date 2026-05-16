import { NextRequest, NextResponse } from 'next/server';
import { paginate } from '@/lib/db';

export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  try {
    const sp = req.nextUrl.searchParams;
    const page = parseInt(sp.get('page') ?? '1');
    const perPage = parseInt(sp.get('per_page') ?? '20');
    const stateId = sp.get('state_id');
    const leaderId = sp.get('leader_id');
    const status = sp.get('status');
    const search = sp.get('search');
    const category = sp.get('category');

    let sql = `
      SELECT p.id, p.slug, p.title, p.category, p.status, p.deadline,
             p.promise_date, p.verification_score, p.ai_confidence, p.budget,
             l.name AS leader_name, l.slug AS leader_slug,
             s.name AS state_name
      FROM promises p
      LEFT JOIN leaders l ON l.id = p.leader_id
      LEFT JOIN states s ON s.id = p.state_id
      WHERE p.is_active = 1
    `;
    const params: unknown[] = [];
    if (stateId)  { sql += ' AND p.state_id = ?';  params.push(stateId); }
    if (leaderId) { sql += ' AND p.leader_id = ?'; params.push(leaderId); }
    if (status)   { sql += ' AND p.status = ?';    params.push(status); }
    if (category) { sql += ' AND p.category = ?';  params.push(category); }
    if (search)   { sql += ' AND p.title LIKE ?';  params.push(`%${search}%`); }
    sql += ' ORDER BY p.created_at DESC';

    const result = await paginate(sql, params, page, perPage);
    return NextResponse.json({ success: true, data: result.data, pagination: result });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

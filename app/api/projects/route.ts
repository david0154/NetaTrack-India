import { NextRequest, NextResponse } from 'next/server';
import { paginate } from '@/lib/db';

export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  try {
    const sp = req.nextUrl.searchParams;
    const page = parseInt(sp.get('page') ?? '1');
    const perPage = parseInt(sp.get('per_page') ?? '20');
    const stateId = sp.get('state_id');
    const status = sp.get('status');
    const search = sp.get('search');

    let sql = `
      SELECT pr.id, pr.slug, pr.title, pr.category, pr.budget, pr.spent,
             pr.start_date, pr.expected_end_date, pr.status, pr.progress_percent,
             l.name AS leader_name, l.slug AS leader_slug,
             s.name AS state_name
      FROM projects pr
      LEFT JOIN leaders l ON l.id = pr.leader_id
      LEFT JOIN states s ON s.id = pr.state_id
      WHERE pr.is_active = 1
    `;
    const params: unknown[] = [];
    if (stateId) { sql += ' AND pr.state_id = ?'; params.push(stateId); }
    if (status)  { sql += ' AND pr.status = ?'; params.push(status); }
    if (search)  { sql += ' AND pr.title LIKE ?'; params.push(`%${search}%`); }
    sql += ' ORDER BY pr.created_at DESC';

    const result = await paginate(sql, params, page, perPage);
    return NextResponse.json({ success: true, data: result.data, pagination: result });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

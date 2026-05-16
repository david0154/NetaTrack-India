import { NextRequest, NextResponse } from 'next/server';
import { paginate } from '@/lib/db';

export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  try {
    const sp = req.nextUrl.searchParams;
    const page = parseInt(sp.get('page') ?? '1');
    const perPage = parseInt(sp.get('per_page') ?? '20');
    const severity = sp.get('severity');
    const agency = sp.get('agency');
    const search = sp.get('search');

    let sql = `
      SELECT c.id, c.title, c.description, c.agency, c.case_number,
             c.amount_crore, c.status, c.severity, c.source_url, c.reported_date,
             l.name AS leader_name, l.slug AS leader_slug, l.photo AS leader_photo,
             p.name AS party_name, p.color_code
      FROM corruption_cases c
      LEFT JOIN leaders l ON l.id = c.leader_id
      LEFT JOIN parties p ON p.id = l.party_id
      WHERE 1=1
    `;
    const params: unknown[] = [];
    if (severity) { sql += ' AND c.severity = ?'; params.push(severity); }
    if (agency)   { sql += ' AND c.agency LIKE ?'; params.push(`%${agency}%`); }
    if (search)   { sql += ' AND (c.title LIKE ? OR l.name LIKE ?)'; params.push(`%${search}%`, `%${search}%`); }
    sql += ' ORDER BY c.reported_date DESC';

    const result = await paginate(sql, params, page, perPage);
    return NextResponse.json({ success: true, data: result.data, pagination: result });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

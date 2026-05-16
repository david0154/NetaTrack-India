import { NextRequest, NextResponse } from 'next/server';
import { query, execute } from '@/lib/db';
import { requireAdmin } from '@/lib/auth';
import { getPaginationMeta } from '@/lib/utils';

export async function GET(req: NextRequest) {
  const sp     = req.nextUrl.searchParams;
  const page   = Math.max(1, Number(sp.get('page')   || 1));
  const limit  = Math.min(50, Number(sp.get('limit')  || 20));
  const leader = sp.get('leader');
  const agency = sp.get('agency');
  const status = sp.get('status');
  const offset = (page - 1) * limit;

  const where: string[] = [];
  const params: any[] = [];
  if (leader) { where.push('l.slug = ?'); params.push(leader); }
  if (agency) { where.push('cc.agency = ?'); params.push(agency); }
  if (status) { where.push('cc.status = ?'); params.push(status); }

  const whereSQL = where.length ? 'WHERE ' + where.join(' AND ') : '';

  const [countRow, cases] = await Promise.all([
    query<any>(`SELECT COUNT(*) as c FROM corruption_cases cc LEFT JOIN leaders l ON cc.leader_id=l.id ${whereSQL}`, params),
    query<any>(`SELECT cc.*, l.name as leader_name, l.slug as leader_slug, l.photo_url as leader_photo, p.abbreviation as party_abbr
      FROM corruption_cases cc
      LEFT JOIN leaders l ON cc.leader_id = l.id
      LEFT JOIN parties p ON l.party_id = p.id
      ${whereSQL}
      ORDER BY cc.created_at DESC
      LIMIT ${limit} OFFSET ${offset}`, params),
  ]);

  return NextResponse.json({
    success: true,
    data: cases,
    pagination: getPaginationMeta(countRow[0].c, page, limit),
  });
}

export async function POST(req: NextRequest) {
  const auth = await requireAdmin(req);
  if (auth instanceof Response) return auth;
  const body = await req.json();
  const { leader_id, title, description, agency, case_number, amount_crore, status, severity, source_url, reported_date } = body;
  if (!title || !leader_id)
    return NextResponse.json({ success: false, error: 'Title and leader required' }, { status: 400 });
  const result = await execute(
    `INSERT INTO corruption_cases (leader_id, title, description, agency, case_number, amount_crore, status, severity, source_url, reported_date)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [leader_id, title, description || null, agency || 'Other', case_number || null, amount_crore || null, status || 'alleged', severity || 'low', source_url || null, reported_date || null]
  );
  return NextResponse.json({ success: true, data: { id: result.insertId } }, { status: 201 });
}

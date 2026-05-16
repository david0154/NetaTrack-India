import { NextRequest, NextResponse } from 'next/server';
import { query, execute } from '@/lib/db';
import { requireAdmin } from '@/lib/auth';
import { getPaginationMeta, slugify } from '@/lib/utils';

export async function GET(req: NextRequest) {
  const sp     = req.nextUrl.searchParams;
  const page   = Math.max(1, Number(sp.get('page')   || 1));
  const limit  = Math.min(50, Number(sp.get('limit')  || 20));
  const status = sp.get('status');
  const state  = sp.get('state');
  const leader = sp.get('leader');
  const q      = sp.get('q');
  const offset = (page - 1) * limit;

  const where: string[] = [];
  const params: any[] = [];
  if (status) { where.push('pj.status = ?'); params.push(status); }
  if (state)  { where.push('s.code = ?'); params.push(state); }
  if (leader) { where.push('l.slug = ?'); params.push(leader); }
  if (q)      { where.push('pj.title LIKE ?'); params.push(`%${q}%`); }

  const whereSQL = where.length ? 'WHERE ' + where.join(' AND ') : '';

  const [countRow, projects] = await Promise.all([
    query<any>(`SELECT COUNT(*) as c FROM projects pj LEFT JOIN states s ON pj.state_id=s.id LEFT JOIN leaders l ON pj.leader_id=l.id ${whereSQL}`, params),
    query<any>(`SELECT pj.*, l.name as leader_name, l.slug as leader_slug, s.name as state_name
      FROM projects pj
      LEFT JOIN leaders l ON pj.leader_id = l.id
      LEFT JOIN states s ON pj.state_id = s.id
      ${whereSQL}
      ORDER BY pj.created_at DESC
      LIMIT ${limit} OFFSET ${offset}`, params),
  ]);

  return NextResponse.json({
    success: true,
    data: projects,
    pagination: getPaginationMeta(countRow[0].c, page, limit),
  });
}

export async function POST(req: NextRequest) {
  const auth = await requireAdmin(req);
  if (auth instanceof Response) return auth;
  const body = await req.json();
  const { title, description, category, state_id, leader_id, budget, start_date, expected_end_date, status, source_url, tender_id } = body;
  if (!title) return NextResponse.json({ success: false, error: 'Title required' }, { status: 400 });
  const slug = slugify(title) + '-' + Date.now();
  const result = await execute(
    `INSERT INTO projects (slug, title, description, category, state_id, leader_id, budget, start_date, expected_end_date, status, source_url, tender_id, created_by)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [slug, title, description || null, category || null, state_id || null, leader_id || null, budget || null, start_date || null, expected_end_date || null, status || 'not_started', source_url || null, tender_id || null, (auth as any).sub]
  );
  return NextResponse.json({ success: true, data: { id: result.insertId, slug } }, { status: 201 });
}

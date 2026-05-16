import { NextRequest, NextResponse } from 'next/server';
import { query, execute } from '@/lib/db';
import { requireAdmin } from '@/lib/auth';
import { getPaginationMeta, slugify } from '@/lib/utils';

export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const page   = Math.max(1, Number(sp.get('page')   || 1));
  const limit  = Math.min(50, Number(sp.get('limit')  || 20));
  const status = sp.get('status');
  const leader = sp.get('leader');
  const state  = sp.get('state');
  const cat    = sp.get('category');
  const q      = sp.get('q');
  const offset = (page - 1) * limit;

  const where: string[] = [];
  const params: any[] = [];
  if (status) { where.push('pr.status = ?'); params.push(status); }
  if (leader) { where.push('l.slug = ?'); params.push(leader); }
  if (state)  { where.push('s.code = ?'); params.push(state); }
  if (cat)    { where.push('pr.category = ?'); params.push(cat); }
  if (q)      { where.push('pr.title LIKE ?'); params.push(`%${q}%`); }

  const whereSQL = where.length ? 'WHERE ' + where.join(' AND ') : '';

  const [countRow, promises] = await Promise.all([
    query<any>(`SELECT COUNT(*) as c FROM promises pr LEFT JOIN leaders l ON pr.leader_id=l.id LEFT JOIN states s ON pr.state_id=s.id ${whereSQL}`, params),
    query<any>(`SELECT pr.*, l.name as leader_name, l.slug as leader_slug, s.name as state_name
      FROM promises pr
      LEFT JOIN leaders l ON pr.leader_id = l.id
      LEFT JOIN states s ON pr.state_id = s.id
      ${whereSQL}
      ORDER BY pr.created_at DESC
      LIMIT ${limit} OFFSET ${offset}`, params),
  ]);

  return NextResponse.json({
    success: true,
    data: promises,
    pagination: getPaginationMeta(countRow[0].c, page, limit),
  });
}

export async function POST(req: NextRequest) {
  const auth = await requireAdmin(req);
  if (auth instanceof Response) return auth;
  const body = await req.json();
  const { title, description, leader_id, state_id, category, budget, deadline, promise_date, status, source_name, source_url } = body;
  if (!title || !description || !source_name || !source_url || !category)
    return NextResponse.json({ success: false, error: 'Required fields missing' }, { status: 400 });
  const slug = slugify(title) + '-' + Date.now();
  const result = await execute(
    `INSERT INTO promises (slug, title, description, leader_id, state_id, category, budget, deadline, promise_date, status, source_name, source_url, approved_by, approved_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())`,
    [slug, title, description, leader_id || null, state_id || null, category, budget || null, deadline || null, promise_date || null, status || 'pending', source_name, source_url, (auth as any).sub]
  );
  return NextResponse.json({ success: true, data: { id: result.insertId, slug } }, { status: 201 });
}

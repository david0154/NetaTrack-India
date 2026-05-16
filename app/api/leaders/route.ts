import { NextRequest, NextResponse } from 'next/server';
import { query, execute } from '@/lib/db';
import { requireAdmin } from '@/lib/auth';
import { getPaginationMeta, slugify } from '@/lib/utils';

export async function GET(req: NextRequest) {
  const sp = req.nextUrl.searchParams;
  const page  = Math.max(1, Number(sp.get('page')  || 1));
  const limit = Math.min(50, Number(sp.get('limit') || 20));
  const state = sp.get('state');
  const party = sp.get('party');
  const search = sp.get('q');
  const offset = (page - 1) * limit;

  const where: string[] = ['l.is_active = 1'];
  const params: any[] = [];
  if (state)  { where.push('s.code = ?'); params.push(state); }
  if (party)  { where.push('p.abbreviation = ?'); params.push(party); }
  if (search) { where.push('l.name LIKE ?'); params.push(`%${search}%`); }

  const whereSQL = where.length ? 'WHERE ' + where.join(' AND ') : '';

  const [countRow, leaders] = await Promise.all([
    query<any>(`SELECT COUNT(*) as c FROM leaders l LEFT JOIN states s ON l.state_id=s.id LEFT JOIN parties p ON l.party_id=p.id ${whereSQL}`, params),
    query<any>(`SELECT l.*, s.name as state_name, s.code as state_code, p.name as party_name, p.abbreviation as party_abbr
      FROM leaders l
      LEFT JOIN states s ON l.state_id = s.id
      LEFT JOIN parties p ON l.party_id = p.id
      ${whereSQL}
      ORDER BY l.final_score DESC
      LIMIT ${limit} OFFSET ${offset}`, params),
  ]);

  return NextResponse.json({
    success: true,
    data: leaders,
    pagination: getPaginationMeta(countRow[0].c, page, limit),
  });
}

export async function POST(req: NextRequest) {
  const auth = await requireAdmin(req);
  if (auth instanceof Response) return auth;

  const body = await req.json();
  const { name, party_id, state_id, position, photo_url, bio, birth_date, education, social_twitter, social_facebook, official_website } = body;
  if (!name) return NextResponse.json({ success: false, error: 'Name required' }, { status: 400 });

  const slug = slugify(name) + '-' + Date.now();
  const result = await execute(
    `INSERT INTO leaders (slug, name, party_id, state_id, position, photo_url, bio, birth_date, education, social_twitter, social_facebook, official_website)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [slug, name, party_id || null, state_id || null, position || null, photo_url || null, bio || null, birth_date || null, education || null, social_twitter || null, social_facebook || null, official_website || null]
  );
  return NextResponse.json({ success: true, data: { id: result.insertId, slug } }, { status: 201 });
}

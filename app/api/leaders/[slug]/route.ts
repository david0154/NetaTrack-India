import { NextRequest, NextResponse } from 'next/server';
import { query, queryOne, execute } from '@/lib/db';
import { requireAdmin } from '@/lib/auth';

export async function GET(_req: NextRequest, { params }: { params: { slug: string } }) {
  const leader = await queryOne<any>(
    `SELECT l.*, s.name as state_name, s.code as state_code, p.name as party_name, p.abbreviation as party_abbr
     FROM leaders l
     LEFT JOIN states s ON l.state_id = s.id
     LEFT JOIN parties p ON l.party_id = p.id
     WHERE l.slug = ? AND l.is_active = 1`,
    [params.slug]
  );
  if (!leader) return NextResponse.json({ success: false, error: 'Leader not found' }, { status: 404 });

  const [promises, projects, corruption] = await Promise.all([
    query<any>(`SELECT id, slug, title, status, category, created_at FROM promises WHERE leader_id = ? ORDER BY created_at DESC LIMIT 10`, [leader.id]),
    query<any>(`SELECT id, slug, title, status, progress_percent, budget, created_at FROM projects WHERE leader_id = ? ORDER BY created_at DESC LIMIT 10`, [leader.id]),
    query<any>(`SELECT id, title, agency, status, severity, amount_crore, reported_date FROM corruption_cases WHERE leader_id = ? ORDER BY reported_date DESC`, [leader.id]),
  ]);

  return NextResponse.json({ success: true, data: { ...leader, promises, projects, corruption } });
}

export async function PUT(req: NextRequest, { params }: { params: { slug: string } }) {
  const auth = await requireAdmin(req);
  if (auth instanceof Response) return auth;
  const body = await req.json();
  const { name, party_id, state_id, position, photo_url, bio, birth_date, education, social_twitter, social_facebook, official_website, is_active } = body;
  await execute(
    `UPDATE leaders SET name=?, party_id=?, state_id=?, position=?, photo_url=?, bio=?, birth_date=?, education=?, social_twitter=?, social_facebook=?, official_website=?, is_active=? WHERE slug=?`,
    [name, party_id || null, state_id || null, position || null, photo_url || null, bio || null, birth_date || null, education || null, social_twitter || null, social_facebook || null, official_website || null, is_active ?? 1, params.slug]
  );
  return NextResponse.json({ success: true });
}

export async function DELETE(req: NextRequest, { params }: { params: { slug: string } }) {
  const auth = await requireAdmin(req);
  if (auth instanceof Response) return auth;
  await execute('UPDATE leaders SET is_active = 0 WHERE slug = ?', [params.slug]);
  return NextResponse.json({ success: true });
}

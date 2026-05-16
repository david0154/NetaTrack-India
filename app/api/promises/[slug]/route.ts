import { NextRequest, NextResponse } from 'next/server';
import { queryOne, execute } from '@/lib/db';
import { requireAdmin } from '@/lib/auth';

export async function GET(_req: NextRequest, { params }: { params: { slug: string } }) {
  const promise = await queryOne<any>(
    `SELECT pr.*, l.name as leader_name, l.slug as leader_slug, l.photo_url as leader_photo,
            s.name as state_name, p.name as party_name
     FROM promises pr
     LEFT JOIN leaders l ON pr.leader_id = l.id
     LEFT JOIN states s ON pr.state_id = s.id
     LEFT JOIN parties p ON l.party_id = p.id
     WHERE pr.slug = ?`,
    [params.slug]
  );
  if (!promise) return NextResponse.json({ success: false, error: 'Promise not found' }, { status: 404 });
  return NextResponse.json({ success: true, data: promise });
}

export async function PUT(req: NextRequest, { params }: { params: { slug: string } }) {
  const auth = await requireAdmin(req);
  if (auth instanceof Response) return auth;
  const { title, description, status, fact_check_notes, verification_score, budget, deadline } = await req.json();
  await execute(
    `UPDATE promises SET title=?, description=?, status=?, fact_check_notes=?, verification_score=?, budget=?, deadline=? WHERE slug=?`,
    [title, description, status, fact_check_notes || null, verification_score ?? 0, budget || null, deadline || null, params.slug]
  );
  return NextResponse.json({ success: true });
}

export async function DELETE(req: NextRequest, { params }: { params: { slug: string } }) {
  const auth = await requireAdmin(req);
  if (auth instanceof Response) return auth;
  await execute('DELETE FROM promises WHERE slug = ?', [params.slug]);
  return NextResponse.json({ success: true });
}

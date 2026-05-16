import { NextRequest, NextResponse } from 'next/server';
import { queryOne, execute } from '@/lib/db';
import { requireAdmin } from '@/lib/auth';

export async function GET(_req: NextRequest, { params }: { params: { slug: string } }) {
  const project = await queryOne<any>(
    `SELECT pj.*, l.name as leader_name, l.slug as leader_slug, l.photo_url as leader_photo,
            s.name as state_name, p.name as party_name
     FROM projects pj
     LEFT JOIN leaders l ON pj.leader_id = l.id
     LEFT JOIN states s ON pj.state_id = s.id
     LEFT JOIN parties p ON l.party_id = p.id
     WHERE pj.slug = ?`,
    [params.slug]
  );
  if (!project) return NextResponse.json({ success: false, error: 'Project not found' }, { status: 404 });
  return NextResponse.json({ success: true, data: project });
}

export async function PUT(req: NextRequest, { params }: { params: { slug: string } }) {
  const auth = await requireAdmin(req);
  if (auth instanceof Response) return auth;
  const { title, description, status, progress_percent, spent, actual_end_date, budget } = await req.json();
  await execute(
    `UPDATE projects SET title=?, description=?, status=?, progress_percent=?, spent=?, actual_end_date=?, budget=? WHERE slug=?`,
    [title, description || null, status, progress_percent ?? 0, spent ?? 0, actual_end_date || null, budget || null, params.slug]
  );
  return NextResponse.json({ success: true });
}

export async function DELETE(req: NextRequest, { params }: { params: { slug: string } }) {
  const auth = await requireAdmin(req);
  if (auth instanceof Response) return auth;
  await execute('DELETE FROM projects WHERE slug = ?', [params.slug]);
  return NextResponse.json({ success: true });
}

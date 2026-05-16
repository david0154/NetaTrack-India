import { NextRequest, NextResponse } from 'next/server';
import { query } from '@/lib/db';
import { verifyJWT } from '@/lib/auth';

export async function PATCH(req: NextRequest, { params }: { params: { id: string } }) {
  try {
    const token = req.cookies.get('nt_admin_token')?.value;
    if (!token) return NextResponse.json({ success: false, error: 'Unauthorized' }, { status: 401 });
    const payload = verifyJWT(token);
    if (!payload || !['super_admin','admin','moderator'].includes(payload.role)) {
      return NextResponse.json({ success: false, error: 'Forbidden' }, { status: 403 });
    }
    const { status, review_notes } = await req.json();
    const allowed = ['approved','rejected','under_review','duplicate'];
    if (!allowed.includes(status)) {
      return NextResponse.json({ success: false, error: 'Invalid status' }, { status: 400 });
    }
    await query(
      `UPDATE public_reports SET status = ?, review_notes = ?, updated_at = NOW() WHERE id = ?`,
      [status, review_notes ?? null, params.id]
    );
    return NextResponse.json({ success: true, message: `Report ${status}` });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

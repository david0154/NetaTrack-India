import { NextRequest, NextResponse } from 'next/server';
import { query, queryOne } from '@/lib/db';
import { verifyJWT } from '@/lib/auth';

export const dynamic = 'force-dynamic';

export async function GET(req: NextRequest) {
  try {
    const token = req.cookies.get('nt_admin_token')?.value;
    if (!token) return NextResponse.json({ success: false, error: 'Unauthorized' }, { status: 401 });
    const payload = verifyJWT(token);
    if (!payload || !['super_admin','admin','moderator'].includes(payload.role)) {
      return NextResponse.json({ success: false, error: 'Forbidden' }, { status: 403 });
    }
    const sp = req.nextUrl.searchParams;
    const status = sp.get('status') ?? 'pending';
    const page = parseInt(sp.get('page') ?? '1');
    const perPage = parseInt(sp.get('per_page') ?? '20');
    const offset = (page - 1) * perPage;
    const rows = await query<any>(
      `SELECT * FROM public_reports WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?`,
      [status, perPage, offset]
    );
    const countRow = await queryOne<any>(`SELECT COUNT(*) as total FROM public_reports WHERE status = ?`, [status]);
    return NextResponse.json({ success: true, data: rows, pagination: { total: countRow?.total ?? 0, page, perPage } });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

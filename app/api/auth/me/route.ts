import { NextRequest, NextResponse } from 'next/server';
import { verifyJWT } from '@/lib/auth';
import { queryOne } from '@/lib/db';

export async function GET(req: NextRequest) {
  try {
    const token = req.cookies.get('nt_token')?.value ?? req.cookies.get('nt_admin_token')?.value;
    if (!token) return NextResponse.json({ success: false, error: 'Not authenticated' }, { status: 401 });

    const payload = verifyJWT(token);
    if (!payload) return NextResponse.json({ success: false, error: 'Invalid or expired token' }, { status: 401 });

    const user = await queryOne<any>(
      `SELECT id, name, email, role, credibility_score, is_banned, created_at FROM users WHERE id = ?`,
      [payload.sub]
    );
    if (!user) return NextResponse.json({ success: false, error: 'User not found' }, { status: 404 });
    if (user.is_banned) return NextResponse.json({ success: false, error: 'Account banned' }, { status: 403 });

    return NextResponse.json({ success: true, data: user });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

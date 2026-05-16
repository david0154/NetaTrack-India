import { NextRequest, NextResponse } from 'next/server';
import { queryOne } from '@/lib/db';
import jwt from 'jsonwebtoken';

export async function GET(req: NextRequest) {
  try {
    const token = req.cookies.get('nt_token')?.value;
    if (!token) return NextResponse.json({ success: false, error: 'Not authenticated' }, { status: 401 });

    const payload = jwt.verify(token, process.env.JWT_SECRET || 'netatrack_secret_2026') as any;
    const user = await queryOne(
      `SELECT id, name, email, role, credibility_score, is_banned, created_at FROM users WHERE id = ?`,
      [payload.id]
    );
    if (!user) return NextResponse.json({ success: false, error: 'User not found' }, { status: 404 });
    return NextResponse.json({ success: true, data: user });
  } catch {
    return NextResponse.json({ success: false, error: 'Invalid token' }, { status: 401 });
  }
}

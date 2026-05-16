import { NextRequest, NextResponse } from 'next/server';
import { queryOne } from '@/lib/db';
import bcrypt from 'bcryptjs';
import { signJWT } from '@/lib/auth';

export async function POST(req: NextRequest) {
  try {
    const { email, password } = await req.json();
    if (!email || !password) {
      return NextResponse.json({ success: false, error: 'Email and password required' }, { status: 400 });
    }

    const user = await queryOne<any>(
      `SELECT id, name, email, password_hash, role, is_banned, credibility_score FROM users WHERE email = ?`,
      [email]
    );

    if (!user) return NextResponse.json({ success: false, error: 'Invalid credentials' }, { status: 401 });
    if (user.is_banned) return NextResponse.json({ success: false, error: 'Account banned' }, { status: 403 });

    const valid = await bcrypt.compare(password, user.password_hash);
    if (!valid) return NextResponse.json({ success: false, error: 'Invalid credentials' }, { status: 401 });

    // Use unified custom JWT from lib/auth (no jsonwebtoken dependency needed)
    const token = signJWT({ sub: user.id, email: user.email, role: user.role });

    const res = NextResponse.json({
      success: true,
      data: { id: user.id, name: user.name, email: user.email, role: user.role, credibility_score: user.credibility_score }
    });

    const isAdmin = ['super_admin', 'admin', 'moderator'].includes(user.role);
    const cookieName = isAdmin ? 'nt_admin_token' : 'nt_token';
    res.cookies.set(cookieName, token, { httpOnly: true, secure: process.env.NODE_ENV === 'production', maxAge: 604800, path: '/' });
    return res;
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

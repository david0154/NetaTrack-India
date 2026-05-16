import { NextRequest, NextResponse } from 'next/server';
import bcrypt from 'bcryptjs';
import { queryOne, execute } from '@/lib/db';
import { signJWT } from '@/lib/auth';

export async function POST(req: NextRequest) {
  try {
    const { email, password } = await req.json();
    if (!email || !password)
      return NextResponse.json({ success: false, error: 'Email and password required' }, { status: 400 });

    const user = await queryOne<any>(
      `SELECT u.id, u.name, u.email, u.password_hash, u.is_banned, r.name as role
       FROM users u JOIN roles r ON u.role_id = r.id
       WHERE u.email = ?`, [email.toLowerCase().trim()]
    );
    if (!user)
      return NextResponse.json({ success: false, error: 'Invalid credentials' }, { status: 401 });
    if (user.is_banned)
      return NextResponse.json({ success: false, error: 'Account is banned' }, { status: 403 });

    const valid = await bcrypt.compare(password, user.password_hash);
    if (!valid)
      return NextResponse.json({ success: false, error: 'Invalid credentials' }, { status: 401 });

    await execute('UPDATE users SET last_login = NOW() WHERE id = ?', [user.id]);
    const token = signJWT({ sub: user.id, email: user.email, role: user.role });
    return NextResponse.json({
      success: true,
      token,
      user: { id: user.id, name: user.name, email: user.email, role: user.role },
    });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

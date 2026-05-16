import { NextRequest, NextResponse } from 'next/server';
import bcrypt from 'bcryptjs';
import { query, execute } from '@/lib/db';
import { signJWT } from '@/lib/auth';

export async function POST(req: NextRequest) {
  try {
    const { name, email, password } = await req.json();
    if (!name || !email || !password)
      return NextResponse.json({ success: false, error: 'All fields required' }, { status: 400 });
    if (password.length < 8)
      return NextResponse.json({ success: false, error: 'Password must be at least 8 characters' }, { status: 400 });

    const existing = await query('SELECT id FROM users WHERE email = ?', [email]);
    if (existing.length > 0)
      return NextResponse.json({ success: false, error: 'Email already registered' }, { status: 409 });

    const hash = await bcrypt.hash(password, 12);
    const result = await execute(
      'INSERT INTO users (name, email, password_hash, role_id) VALUES (?, ?, ?, 4)',
      [name.trim(), email.toLowerCase().trim(), hash]
    );
    const token = signJWT({ sub: result.insertId, email, role: 'viewer' });
    return NextResponse.json({ success: true, token, user: { id: result.insertId, name, email, role: 'viewer' } });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

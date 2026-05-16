import { NextRequest, NextResponse } from 'next/server';
import { query, queryOne } from '@/lib/db';
import bcrypt from 'bcryptjs';

export async function POST(req: NextRequest) {
  try {
    const { name, email, password } = await req.json();
    if (!name || !email || !password) {
      return NextResponse.json({ success: false, error: 'All fields required' }, { status: 400 });
    }
    if (password.length < 8) {
      return NextResponse.json({ success: false, error: 'Password minimum 8 characters' }, { status: 400 });
    }
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      return NextResponse.json({ success: false, error: 'Invalid email format' }, { status: 400 });
    }

    const existing = await queryOne(`SELECT id FROM users WHERE email = ?`, [email]);
    if (existing) return NextResponse.json({ success: false, error: 'Email already registered' }, { status: 409 });

    const hash = await bcrypt.hash(password, 12);
    // Use query() (not execute() which doesn't exist in lib/db)
    const rows = await query<any>(
      `INSERT INTO users (name, email, password_hash, role, credibility_score) VALUES (?, ?, ?, 'user', 0)`,
      [name, email, hash]
    );

    return NextResponse.json({ success: true, message: 'Account created successfully' });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

import { NextResponse } from 'next/server';

export async function POST() {
  const res = NextResponse.json({ success: true, message: 'Logged out' });
  res.cookies.set('nt_token', '', { maxAge: 0, path: '/' });
  res.cookies.set('nt_admin_token', '', { maxAge: 0, path: '/' });
  return res;
}

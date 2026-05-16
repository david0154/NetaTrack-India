import { NextRequest, NextResponse } from 'next/server';
import { getUserFromRequest } from '@/lib/auth';
import { queryOne } from '@/lib/db';

export async function GET(req: NextRequest) {
  const user = await getUserFromRequest(req);
  if (!user) return NextResponse.json({ success: false, error: 'Unauthorized' }, { status: 401 });
  const profile = await queryOne<any>(
    `SELECT u.id, u.name, u.email, u.credibility_score, u.email_verified, u.last_login, r.name as role
     FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?`,
    [user.sub]
  );
  if (!profile) return NextResponse.json({ success: false, error: 'User not found' }, { status: 404 });
  return NextResponse.json({ success: true, data: profile });
}

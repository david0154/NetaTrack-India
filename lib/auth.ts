import crypto from 'crypto';
import { query, queryOne } from './db';

export interface JWTPayload {
  sub: number;
  email: string;
  role: string;
  iat: number;
  exp: number;
}

const SECRET = process.env.JWT_SECRET || 'netatrack-secret-change-in-production';

function base64url(input: Buffer | string): string {
  const buf = typeof input === 'string' ? Buffer.from(input) : input;
  return buf.toString('base64').replace(/=/g, '').replace(/\+/g, '-').replace(/\//g, '_');
}

export function signJWT(payload: Omit<JWTPayload, 'iat' | 'exp'>): string {
  const now = Math.floor(Date.now() / 1000);
  const full = { ...payload, iat: now, exp: now + 60 * 60 * 24 * 7 }; // 7 days
  const header  = base64url(JSON.stringify({ alg: 'HS256', typ: 'JWT' }));
  const body    = base64url(JSON.stringify(full));
  const sig     = base64url(
    crypto.createHmac('sha256', SECRET).update(`${header}.${body}`).digest()
  );
  return `${header}.${body}.${sig}`;
}

export function verifyJWT(token: string): JWTPayload | null {
  try {
    const [header, body, sig] = token.split('.');
    const expected = base64url(
      crypto.createHmac('sha256', SECRET).update(`${header}.${body}`).digest()
    );
    if (sig !== expected) return null;
    const payload: JWTPayload = JSON.parse(Buffer.from(body, 'base64').toString());
    if (payload.exp < Math.floor(Date.now() / 1000)) return null;
    return payload;
  } catch {
    return null;
  }
}

export async function getUserFromRequest(req: Request): Promise<JWTPayload | null> {
  const auth = req.headers.get('authorization') ?? '';
  const token = auth.startsWith('Bearer ') ? auth.slice(7) : null;
  if (!token) return null;
  return verifyJWT(token);
}

export async function requireAdmin(req: Request): Promise<JWTPayload | Response> {
  const user = await getUserFromRequest(req);
  if (!user) return new Response(JSON.stringify({ error: 'Unauthorized' }), { status: 401 });
  if (!['super_admin', 'admin', 'moderator'].includes(user.role))
    return new Response(JSON.stringify({ error: 'Forbidden' }), { status: 403 });
  return user;
}

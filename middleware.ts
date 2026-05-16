/**
 * NetaTrack India - Next.js Edge Middleware
 * Protects /admin routes, handles auth, rate limiting signals
 */
import { NextResponse } from 'next/server'
import type { NextRequest } from 'next/server'

// Routes that require authentication
const PROTECTED_ADMIN = /^\/admin/
const PUBLIC_API      = /^\/api\/(public|states|leaders|promises|projects)/

export function middleware(request: NextRequest): NextResponse {
  const { pathname } = request.nextUrl

  // ── Admin route protection ───────────────────────────────────────────
  if (PROTECTED_ADMIN.test(pathname)) {
    const token =
      request.cookies.get('nt_admin_token')?.value ??
      request.headers.get('authorization')?.replace('Bearer ', '')

    if (!token) {
      const loginUrl = new URL('/admin/login', request.url)
      loginUrl.searchParams.set('redirect', pathname)
      return NextResponse.redirect(loginUrl)
    }

    // Basic JWT structure check (full verify in API handlers)
    const parts = token.split('.')
    if (parts.length !== 3) {
      const loginUrl = new URL('/admin/login', request.url)
      return NextResponse.redirect(loginUrl)
    }

    try {
      const payload = JSON.parse(atob(parts[1]))
      if (!payload.exp || payload.exp < Date.now() / 1000) {
        const loginUrl = new URL('/admin/login', request.url)
        loginUrl.searchParams.set('reason', 'expired')
        return NextResponse.redirect(loginUrl)
      }
      if (!['super_admin', 'admin', 'moderator'].includes(payload.role)) {
        return NextResponse.redirect(new URL('/', request.url))
      }
    } catch {
      return NextResponse.redirect(new URL('/admin/login', request.url))
    }
  }

  // ── Security headers for all responses ──────────────────────────────
  const response = NextResponse.next()
  response.headers.set('X-Frame-Options', 'DENY')
  response.headers.set('X-Content-Type-Options', 'nosniff')
  response.headers.set('Referrer-Policy', 'strict-origin-when-cross-origin')
  response.headers.set(
    'Permissions-Policy',
    'camera=(), microphone=(), geolocation=(self)'
  )
  response.headers.set(
    'Content-Security-Policy',
    [
      "default-src 'self'",
      "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.googletagmanager.com https://connect.facebook.net",
      "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
      "font-src 'self' https://fonts.gstatic.com",
      "img-src 'self' data: blob: https:",
      "connect-src 'self' https://api.gemini.google.com https://api.openai.com https://openrouter.ai https://api.sarvam.ai",
      "frame-ancestors 'none'",
    ].join('; ')
  )

  return response
}

export const config = {
  matcher: [
    '/((?!_next/static|_next/image|favicon.ico|public/).*)',
  ],
}

import { NextResponse } from 'next/server'

export function ok<T>(data: T, meta?: Record<string, unknown>) {
  return NextResponse.json({ success: true, data, ...meta }, { status: 200 })
}

export function created<T>(data: T) {
  return NextResponse.json({ success: true, data }, { status: 201 })
}

export function badRequest(message: string, errors?: unknown) {
  return NextResponse.json({ success: false, error: message, errors }, { status: 400 })
}

export function unauthorized(message = 'Unauthorised') {
  return NextResponse.json({ success: false, error: message }, { status: 401 })
}

export function forbidden(message = 'Forbidden') {
  return NextResponse.json({ success: false, error: message }, { status: 403 })
}

export function notFound(message = 'Not found') {
  return NextResponse.json({ success: false, error: message }, { status: 404 })
}

export function serverError(error: unknown) {
  const msg = error instanceof Error ? error.message : 'Internal server error'
  if (process.env.NODE_ENV === 'development') console.error(error)
  return NextResponse.json({ success: false, error: msg }, { status: 500 })
}

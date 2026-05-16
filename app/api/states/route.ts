import { NextResponse } from 'next/server';
import { query } from '@/lib/db';

export async function GET() {
  try {
    const states = await query('SELECT * FROM states ORDER BY name ASC');
    return NextResponse.json({ success: true, data: states });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

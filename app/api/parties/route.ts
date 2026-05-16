import { NextResponse } from 'next/server';
import { query } from '@/lib/db';

export async function GET() {
  try {
    const parties = await query('SELECT * FROM parties ORDER BY name ASC');
    return NextResponse.json({ success: true, data: parties });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

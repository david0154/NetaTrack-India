import { NextRequest, NextResponse } from 'next/server';
import { query } from '@/lib/db';

export async function GET(req: NextRequest) {
  try {
    const { searchParams } = new URL(req.url);
    const page   = Math.max(1, Number(searchParams.get('page')  || 1));
    const limit  = Math.min(50, Number(searchParams.get('limit') || 20));
    const offset = (page - 1) * limit;
    const state  = searchParams.get('state_id');
    const party  = searchParams.get('party_id');
    const search = searchParams.get('q');

    let where = 'WHERE l.is_active = 1';
    const params: any[] = [];
    if (state)  { where += ' AND l.state_id = ?';  params.push(state); }
    if (party)  { where += ' AND l.party_id = ?';  params.push(party); }
    if (search) { where += ' AND l.name LIKE ?';   params.push(`%${search}%`); }

    const leaders = await query(
      `SELECT l.*, p.name AS party_name, s.name AS state_name
       FROM leaders l
       LEFT JOIN parties p ON p.id = l.party_id
       LEFT JOIN states  s ON s.id = l.state_id
       ${where}
       ORDER BY l.final_score DESC
       LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );

    const [[{ total }]] = await (await import('@/lib/db')).default.execute(
      `SELECT COUNT(*) AS total FROM leaders l ${where}`, params
    ) as any;

    return NextResponse.json({
      success: true,
      data: leaders,
      pagination: { page, limit, total: Number(total), pages: Math.ceil(Number(total)/limit) }
    });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

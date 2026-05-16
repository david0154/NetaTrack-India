import { NextRequest, NextResponse } from 'next/server';
import { query } from '@/lib/db';
import pool from '@/lib/db';

export async function GET(req: NextRequest) {
  try {
    const sp     = new URL(req.url).searchParams;
    const page   = Math.max(1, Number(sp.get('page')  || 1));
    const limit  = Math.min(50, Number(sp.get('limit') || 20));
    const offset = (page - 1) * limit;
    const severity = sp.get('severity');
    const leader   = sp.get('leader_id');
    const search   = sp.get('q');

    let where = 'WHERE 1=1';
    const params: any[] = [];
    if (severity) { where += ' AND cc.severity = ?';    params.push(severity); }
    if (leader)   { where += ' AND cc.leader_id = ?';   params.push(leader); }
    if (search)   { where += ' AND cc.title LIKE ?';    params.push(`%${search}%`); }

    const cases = await query(
      `SELECT cc.*, l.name AS leader_name, l.slug AS leader_slug
       FROM corruption_cases cc
       LEFT JOIN leaders l ON l.id = cc.leader_id
       ${where}
       ORDER BY cc.created_at DESC
       LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );

    const [[{ total }]] = await pool.execute(
      `SELECT COUNT(*) AS total FROM corruption_cases cc ${where}`, params
    ) as any;

    return NextResponse.json({
      success: true, data: cases,
      pagination: { page, limit, total: Number(total), pages: Math.ceil(Number(total)/limit) }
    });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

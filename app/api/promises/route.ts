import { NextRequest, NextResponse } from 'next/server';
import { query } from '@/lib/db';
import pool from '@/lib/db';

export async function GET(req: NextRequest) {
  try {
    const sp     = new URL(req.url).searchParams;
    const page   = Math.max(1, Number(sp.get('page')  || 1));
    const limit  = Math.min(50, Number(sp.get('limit') || 20));
    const offset = (page - 1) * limit;
    const status = sp.get('status');
    const leader = sp.get('leader_id');
    const state  = sp.get('state_id');
    const search = sp.get('q');

    let where = 'WHERE 1=1';
    const params: any[] = [];
    if (status) { where += ' AND pr.status = ?';     params.push(status); }
    if (leader) { where += ' AND pr.leader_id = ?';  params.push(leader); }
    if (state)  { where += ' AND pr.state_id = ?';   params.push(state); }
    if (search) { where += ' AND pr.title LIKE ?';   params.push(`%${search}%`); }

    const promises = await query(
      `SELECT pr.*, l.name AS leader_name, s.name AS state_name
       FROM promises pr
       LEFT JOIN leaders l ON l.id = pr.leader_id
       LEFT JOIN states  s ON s.id = pr.state_id
       ${where}
       ORDER BY pr.created_at DESC
       LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );

    const [[{ total }]] = await pool.execute(
      `SELECT COUNT(*) AS total FROM promises pr ${where}`, params
    ) as any;

    return NextResponse.json({
      success: true, data: promises,
      pagination: { page, limit, total: Number(total), pages: Math.ceil(Number(total)/limit) }
    });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

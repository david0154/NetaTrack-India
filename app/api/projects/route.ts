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
    const state  = sp.get('state_id');
    const search = sp.get('q');

    let where = 'WHERE 1=1';
    const params: any[] = [];
    if (status) { where += ' AND pj.status = ?';    params.push(status); }
    if (state)  { where += ' AND pj.state_id = ?';  params.push(state); }
    if (search) { where += ' AND pj.title LIKE ?';  params.push(`%${search}%`); }

    const projects = await query(
      `SELECT pj.*, l.name AS leader_name, s.name AS state_name
       FROM projects pj
       LEFT JOIN leaders l ON l.id = pj.leader_id
       LEFT JOIN states  s ON s.id = pj.state_id
       ${where}
       ORDER BY pj.created_at DESC
       LIMIT ? OFFSET ?`,
      [...params, limit, offset]
    );

    const [[{ total }]] = await pool.execute(
      `SELECT COUNT(*) AS total FROM projects pj ${where}`, params
    ) as any;

    return NextResponse.json({
      success: true, data: projects,
      pagination: { page, limit, total: Number(total), pages: Math.ceil(Number(total)/limit) }
    });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

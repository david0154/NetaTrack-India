import { NextRequest, NextResponse } from 'next/server';
import { execute, query } from '@/lib/db';
import pool from '@/lib/db';

export async function GET(req: NextRequest) {
  try {
    const sp     = new URL(req.url).searchParams;
    const page   = Math.max(1, Number(sp.get('page')  || 1));
    const limit  = Math.min(50, Number(sp.get('limit') || 20));
    const offset = (page - 1) * limit;
    const status = sp.get('status') || 'approved';

    const rows = await query(
      `SELECT id, title, leader_name, state, submission_type, status, created_at
       FROM public_submissions WHERE status = ?
       ORDER BY created_at DESC LIMIT ? OFFSET ?`,
      [status, limit, offset]
    );
    const [[{ total }]] = await pool.execute(
      `SELECT COUNT(*) AS total FROM public_submissions WHERE status = ?`, [status]
    ) as any;

    return NextResponse.json({
      success: true, data: rows,
      pagination: { page, limit, total: Number(total), pages: Math.ceil(Number(total)/limit) }
    });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { title, leader_name, leader_id, state, description, submission_type, source_link } = body;

    if (!title || !description || !submission_type || !state) {
      return NextResponse.json({ success: false, error: 'Missing required fields' }, { status: 400 });
    }

    const spamScore = description.length < 20 ? 80 : 10;

    const result = await execute(
      `INSERT INTO public_submissions
       (title, leader_name, leader_id, state, description, submission_type, source_link, ai_spam_score, ai_fake_score, ai_verified, status)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, 10, 0, 'pending')`,
      [title, leader_name || '', leader_id || null, state, description, submission_type, source_link || null, spamScore]
    );

    return NextResponse.json({ success: true, message: 'Submission received. Under review.', id: result.insertId });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

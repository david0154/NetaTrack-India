import { NextRequest, NextResponse } from 'next/server';
import { query } from '@/lib/db';

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { title, leader_name, state, description, submission_type, source_link } = body;
    if (!title || !leader_name || !state || !description) {
      return NextResponse.json({ success: false, error: 'Required fields missing' }, { status: 400 });
    }
    // Basic AI spam scoring placeholder (0 = not spam)
    const ai_spam_score = 0;
    const ai_fake_score = 0;
    await query(
      `INSERT INTO public_reports (title, leader_name, state, description, submission_type, source_link, ai_spam_score, ai_fake_score, ai_verified, status)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 'pending')`,
      [title, leader_name, state, description, submission_type ?? 'general', source_link ?? null, ai_spam_score, ai_fake_score]
    );
    return NextResponse.json({ success: true, message: 'Report submitted successfully. It will be reviewed by our team.' });
  } catch (e: any) {
    return NextResponse.json({ success: false, error: e.message }, { status: 500 });
  }
}

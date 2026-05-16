import { NextResponse } from 'next/server';

export async function POST(req: Request) {
  const formData = await req.formData();
  const payload = Object.fromEntries(formData.entries());

  return NextResponse.json({
    message: 'Submission received and sent to moderation queue.',
    status: 'Pending',
    payload
  });
}

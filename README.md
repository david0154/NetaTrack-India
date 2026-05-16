# NetaTrack India

A working starter website for a political transparency platform with:
- Public website
- Promise and leader pages
- Public submission form
- Admin moderation dashboard
- API endpoint for submissions

## Stack
- Next.js 14 (App Router)
- TypeScript
- Tailwind CSS

## Quick Start
```bash
npm install
npm run dev
```
Open: `http://localhost:3000`

## Routes
- `/` Home dashboard
- `/promises` Promise listing
- `/promise/[slug]` Promise detail
- `/leaders` Leader listing
- `/leader/[slug]` Leader detail
- `/submit` Public submission form
- `/admin` Admin moderation dashboard
- `POST /api/submissions` Submission API

## What is included now
- Beautiful dark UI starter with reusable card styling.
- Mock data-driven pages for promises and leaders.
- Admin queue layout with moderation-oriented cards.
- Submission workflow entry point and API response.

## Next build steps
1. Add database (PostgreSQL + Prisma).
2. Add authentication + RBAC (Super Admin/Admin/Moderator/Public).
3. Replace mock data with real APIs.
4. Add AI provider gateway (OpenAI/Sarvam/Gemini/OpenRouter).
5. Add collectors, queues, and verification logs.

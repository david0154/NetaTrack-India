# NetaTrack India

NetaTrack India is a political transparency and public accountability platform to track promises, projects, schemes, budgets, and public evidence across all Indian states.

---

## 1) Product Vision

### Core principle
NetaTrack combines **3 input systems**:
1. Automatic AI collection
2. Public submissions
3. Admin manual uploads

All records flow through:
**Verification → Moderation → Publish**

### Primary outcomes
- Build a trusted public record of political announcements and delivery.
- Make source-backed verification visible to every citizen.
- Combine automation with human moderation to reduce misinformation.

---

## 2) End-to-End Data Flow

### A) Automatic AI Collection
Internet/Government/News/Social sources → Collector → AI extraction → AI verification score → Admin queue → Publish

### B) Public Submission
Citizen submission → Spam checks → Duplicate checks → Admin verification → Publish

### C) Admin Manual Upload
Admin entry/upload → Draft or review → Publish

### Unified governance
All three channels write into a shared moderation + verification workflow, with immutable logs and status history.

---

## 3) Feature Modules (Complete)

## 3.1 Public Website
- Home page with India map, live stats, latest verified updates.
- State pages (`/state/:slug`) with state-level analytics.
- Leader pages (`/leader/:slug`) with profile, promises, and completion score.
- Promise/project pages (`/promise/:slug`) with timeline, sources, verification box.
- Advanced search and filters (state, party, leader, category, status, date, budget range).
- Public evidence section for photos/videos/documents.
- Transparency badges: last updated, verification level, source count, review history.

## 3.2 Public Submission System
- Citizen report form with required source proof.
- Media uploads (image/video/PDF).
- User dashboard for tracking submission status.
- Status lifecycle: Pending → Under Review → Approved/Rejected → Published.
- “Need More Proof” feedback loop.

## 3.3 Automatic Data Collection
- Source connectors for RSS, APIs, HTML scrapers, docs/PDF pipelines.
- Source registry with enable/disable and trust levels.
- Cron scheduler (default every 15 mins).
- Fetch retry + dead-letter handling.
- Raw ingestion storage and extraction audit.

## 3.4 AI Verification System
- Entity extraction: leader, party, state, category, budget, deadline, progress.
- Duplicate and near-duplicate detection.
- Source corroboration checks.
- Fake/news risk checks (domain trust, manipulated media signals, anomaly flags).
- Confidence score + recommendation label:
  - 90–100: Verified candidate
  - 70–89: Likely true
  - 40–69: Needs evidence
  - 0–39: High risk / doubtful

## 3.5 Admin Moderation Panel
- Queues: AI collected, public submissions, disputed items, fake reports.
- Actions: approve, reject, edit, merge duplicates, request proof, mark fake.
- Role-based approval workflows.
- Activity feed and moderation notes.
- Bulk actions and SLA views.

## 3.6 Leader Management
- Leader CRUD with party/state/designation.
- Profile media and biography.
- Computed trust + completion summaries.

## 3.7 State Management
- State-level projects, schemes, and leadership mapping.
- State analytics dashboards.
- Region-specific taxonomies and tags.

## 3.8 Source & Media System
- Source catalog (government/news/social/manifesto/assembly/public).
- Source trust scoring and history.
- Media optimization pipeline:
  - Image compression + thumbnails
  - Video transcoding hooks
  - Optional watermarking
  - CDN-ready object storage

## 3.9 Analytics
- Public and admin dashboards.
- Promise completion analytics by state/party/leader/category.
- Budget utilization and delay trends.
- Timeline and heatmap visualizations.

## 3.10 SEO & Discoverability
- Dynamic slugs.
- XML sitemap generation.
- Schema.org structured data.
- OpenGraph + Twitter cards.
- Canonical URLs and robots controls.

## 3.11 Security & Abuse Protection
- CSRF/XSS/SQLi protections.
- reCAPTCHA + rate limiting.
- IP reputation checks and blocklist.
- Admin 2FA.
- Audit logs for sensitive actions.

## 3.12 Notifications
- Email/in-app/admin alerts.
- Triggers: status changes, approval decisions, dispute outcomes, major updates.

## 3.13 Platform Settings
- Branding/theme/content controls.
- SMTP and notification provider settings.
- Analytics tags and ad integrations.
- AI provider settings (multi-provider keys + fallback policy).

---

## 4) Admin Panel — Detailed Menu and Feature Matrix

### Dashboard
- KPIs: total promises, pending moderation, published today, fake flagged.
- Queue health: backlog size, average review time, high-risk items.

### Promises
- CRUD, status updates, timeline milestones, source linking.
- Budget/deadline/progress fields.
- Verification box editing + recalculation trigger.

### Public Reports
- Queue by risk score and submission age.
- One-click decisions with mandatory reason capture.

### AI Collection
- Source connector status, run logs, error logs.
- Retry failed jobs.
- Pause/resume source pipelines.

### Users & Roles
- Role assignment: Super Admin, Admin, Moderator, Fact Checker, Editor, Public User.
- Suspension/ban controls.
- Login + action audit trail.

### SEO, Analytics, Settings
- Meta templates.
- Sitemap reindex triggers.
- Tracking IDs and consent settings.

---

## 5) AI Provider Strategy (OpenAI, Sarvam, Gemini, OpenRouter, Others)

Implement a provider-agnostic AI gateway:
- Unified interface: `extract()`, `classify()`, `score()`, `summarize()`.
- Provider adapters per API.
- Per-task routing (e.g., extraction via provider A, moderation via provider B).
- Automatic fallback when provider fails.
- Cost/latency telemetry by provider.
- Admin setting fields:
  - Provider enable toggle
  - API key
  - Model name
  - Max tokens/temperature
  - Timeout/retry
  - Priority order

---

## 6) Recommended Technical Architecture

### Frontend
- Next.js + TypeScript + Tailwind CSS + component system (e.g., shadcn/ui).
- SSR for SEO-critical pages.
- Client search filters + server pagination.

### Backend
- NestJS/Fastify (or Next.js API routes for smaller MVP).
- Queue workers (BullMQ + Redis) for ingestion and AI jobs.
- Scheduler (cron) for recurring source pulls.

### Database
- PostgreSQL for normalized entities and logs.
- Redis for cache/queues/rate limiting.
- Object storage (S3-compatible) for media.

### Infrastructure
- Dockerized services.
- CDN + WAF (Cloudflare).
- CI/CD with staged deployments.

---

## 7) Database Design (Expanded)

Core tables (minimum):
- `leaders`
- `states`
- `parties`
- `promises`
- `promise_updates` (timeline events)
- `sources`
- `promise_sources` (many-to-many)
- `public_submissions`
- `submission_media`
- `ai_collected_data`
- `verification_logs`
- `moderation_actions`
- `users`
- `roles`
- `notifications`
- `ai_provider_configs`

Key design rules:
- Every publishable item must have at least one source.
- Maintain `created_by`, `updated_by`, timestamps, and soft-delete fields.
- Keep moderation and verification logs immutable.

---

## 8) API Surface (Minimum MVP)

### Public APIs
- `GET /api/promises`
- `GET /api/promises/:id-or-slug`
- `GET /api/leaders/:slug`
- `GET /api/states/:slug`
- `POST /api/submissions`

### Admin APIs
- `GET /api/admin/queue`
- `POST /api/admin/approve/:id`
- `POST /api/admin/reject/:id`
- `POST /api/admin/request-proof/:id`
- `POST /api/admin/merge`
- `POST /api/admin/sources`
- `POST /api/admin/ai/run`

### Internal worker APIs/events
- `collector.fetch`
- `collector.extract`
- `verifier.score`
- `moderation.enqueue`

---

## 9) Release Plan (Build Part-by-Part)

### Phase 1 (MVP foundation)
- Auth + RBAC
- Leaders/states/promises CRUD
- Public listing and detail pages
- Basic source linking
- Manual admin publishing

### Phase 2 (Public submissions)
- Submission form + media uploads
- Moderation queue
- Notification basics

### Phase 3 (AI ingestion)
- Source registry + collector jobs
- Extraction + duplicate checks
- AI score and admin queue integration

### Phase 4 (Advanced trust + analytics)
- Dispute workflows
- Fact-check history UI
- Advanced state/party dashboards
- SEO and performance hardening

---

## 10) Non-Functional Requirements

- Availability: 99.9% target for public read endpoints.
- Security: encrypted secrets, 2FA for admins, full audit logs.
- Performance: <2.5s LCP on core pages under normal load.
- Compliance: clear content policy and takedown/dispute process.
- Observability: logs, metrics, traces for ingestion and moderation flows.

---

## 11) Practical Next Steps (Immediate)

1. Finalize stack (Next.js + NestJS + Postgres + Redis recommended).
2. Create monorepo with apps:
   - `apps/web`
   - `apps/admin`
   - `apps/api`
   - `apps/workers`
3. Implement auth/RBAC + schema migrations.
4. Ship phase-1 CRUD and public pages.
5. Add submissions and moderation queue.
6. Add AI provider gateway and first collector.

---

## 12) Definition of Done (Per Feature)

A feature is done only when:
- UI completed (public/admin as applicable).
- API + validations complete.
- Source + verification constraints enforced.
- Audit logs captured.
- Tests pass (unit/integration/e2e where relevant).
- Role permissions verified.
- Monitoring and error handling in place.

---

This document is the complete blueprint to plan development scope, prioritize milestones, and avoid missing core platform capabilities.

/**
 * NetaTrack India - Environment Variable Validation (Next.js side)
 * Uses Zod to validate all required env vars at build/startup time
 */
import { z } from 'zod'

const envSchema = z.object({
  // App
  NEXT_PUBLIC_APP_URL: z.string().url(),
  NEXT_PUBLIC_APP_NAME: z.string().default('NetaTrack India'),
  NODE_ENV: z.enum(['development', 'production', 'test']).default('production'),

  // Database (server-side only)
  DB_HOST: z.string().min(1),
  DB_PORT: z.string().default('3306'),
  DB_NAME: z.string().min(1),
  DB_USER: z.string().min(1),
  DB_PASS: z.string(),

  // Auth
  JWT_SECRET: z.string().min(32),
  NEXTAUTH_SECRET: z.string().min(32).optional(),

  // AI Providers (at least one required)
  GEMINI_API_KEY: z.string().optional(),
  OPENAI_API_KEY: z.string().optional(),
  OPENROUTER_API_KEY: z.string().optional(),
  SARVAM_API_KEY: z.string().optional(),
  AI_PRIMARY_PROVIDER: z.enum(['gemini', 'openai', 'openrouter', 'sarvam']).default('gemini'),

  // Redis
  REDIS_HOST: z.string().default('127.0.0.1'),
  REDIS_PORT: z.string().default('6379'),
  REDIS_PASSWORD: z.string().optional(),

  // Cloudflare
  CF_ZONE_ID: z.string().optional(),
  CF_API_TOKEN: z.string().optional(),

  // Google Analytics
  NEXT_PUBLIC_GA_ID: z.string().optional(),
  NEXT_PUBLIC_META_PIXEL: z.string().optional(),
})

type Env = z.infer<typeof envSchema>

let _env: Env

export function getEnv(): Env {
  if (_env) return _env
  const parsed = envSchema.safeParse(process.env)
  if (!parsed.success) {
    console.error('❌ Invalid environment variables:')
    console.error(parsed.error.flatten().fieldErrors)
    // In production, throw to prevent startup with bad config
    if (process.env.NODE_ENV === 'production') {
      throw new Error('Invalid environment configuration. Check .env file.')
    }
  }
  _env = parsed.data as Env
  return _env
}

export default getEnv

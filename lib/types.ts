export interface Leader {
  id: number;
  slug: string;
  name: string;
  party_id: number | null;
  state_id: number | null;
  position: string | null;
  photo_url: string | null;
  bio: string | null;
  promise_score: number;
  project_score: number;
  transparency_score: number;
  public_score: number;
  final_score: number;
  rank_label: string;
  corruption_level: number;
  is_active: number;
  party_name?: string;
  state_name?: string;
  created_at: string;
  updated_at: string;
}

export interface Promise_ {
  id: number;
  slug: string;
  title: string;
  description: string;
  leader_id: number | null;
  state_id: number | null;
  category: string;
  budget: number | null;
  deadline: string | null;
  promise_date: string | null;
  status: 'pending' | 'in_progress' | 'completed' | 'failed' | 'delayed' | 'fake';
  verification_score: number;
  ai_confidence: number;
  source_name: string;
  source_url: string;
  fact_check_notes: string | null;
  leader_name?: string;
  state_name?: string;
  created_at: string;
}

export interface Project {
  id: number;
  slug: string;
  title: string;
  description: string | null;
  category: string | null;
  state_id: number | null;
  leader_id: number | null;
  budget: number | null;
  spent: number;
  start_date: string | null;
  expected_end_date: string | null;
  status: 'not_started' | 'in_progress' | 'completed' | 'delayed' | 'cancelled';
  progress_percent: number;
  source_url: string | null;
  leader_name?: string;
  state_name?: string;
  created_at: string;
}

export interface CorruptionCase {
  id: number;
  leader_id: number | null;
  title: string;
  description: string | null;
  agency: string;
  case_number: string | null;
  amount_crore: number | null;
  status: string;
  severity: string;
  source_url: string | null;
  reported_date: string | null;
  leader_name?: string;
  created_at: string;
}

export interface PublicSubmission {
  id: number;
  title: string;
  leader_name: string;
  leader_id: number | null;
  state: string;
  description: string;
  submission_type: string;
  source_link: string | null;
  media_paths: string[] | null;
  ai_spam_score: number;
  ai_fake_score: number;
  ai_verified: number;
  status: 'pending' | 'approved' | 'rejected' | 'duplicate' | 'under_review';
  review_notes: string | null;
  created_at: string;
}

export interface State {
  id: number;
  name: string;
  code: string;
  region: string | null;
  population: number | null;
  capital: string | null;
}

export interface Party {
  id: number;
  name: string;
  abbreviation: string;
  symbol_url: string | null;
  ideology: string | null;
  website_url: string | null;
}

export interface DashboardStats {
  promises_tracked: number;
  projects_monitored: number;
  delayed_projects: number;
  corruption_cases: number;
  fake_claims_detected: number;
  verified_reports: number;
  public_submissions: number;
  leaders_tracked: number;
}

export interface User {
  id: number;
  name: string;
  email: string;
  role: string;
  credibility_score: number;
  is_banned: number;
  email_verified: number;
  last_login: string | null;
  created_at: string;
}

export interface Setting {
  key: string;
  value: string | null;
  type: string;
  group_name: string;
  label: string | null;
}

export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  error?: string;
  message?: string;
  pagination?: {
    page: number;
    limit: number;
    total: number;
    pages: number;
  };
}

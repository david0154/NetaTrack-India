'use client';
import { useEffect, useState } from 'react';
import { Shield, Users, FileText, AlertTriangle, CheckCircle, Clock, XCircle, TrendingUp } from 'lucide-react';
import toast from 'react-hot-toast';

interface Stats {
  promises_tracked: number;
  projects_monitored: number;
  corruption_cases: number;
  verified_reports: number;
  fake_claims_detected: number;
  public_submissions: number;
  leaders_tracked: number;
  delayed_projects: number;
}

interface Submission {
  id: number;
  title: string;
  leader_name: string;
  state: string;
  submission_type: string;
  ai_spam_score: number;
  status: string;
  created_at: string;
}

export default function AdminDashboard() {
  const [stats, setStats] = useState<Stats | null>(null);
  const [submissions, setSubmissions] = useState<Submission[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState<'dashboard' | 'submissions' | 'leaders' | 'settings'>('dashboard');

  useEffect(() => {
    async function load() {
      try {
        const [sRes, subRes] = await Promise.all([
          fetch('/api/stats'),
          fetch('/api/submissions?status=pending&per_page=10'),
        ]);
        const sJson = await sRes.json();
        const subJson = await subRes.json();
        if (sJson.success) setStats(sJson.data);
        if (subJson.success) setSubmissions(subJson.data);
      } catch { toast.error('Failed to load dashboard data'); }
      finally { setLoading(false); }
    }
    load();
  }, []);

  async function handleApprove(id: number, action: 'approved' | 'rejected') {
    try {
      const res = await fetch(`/api/submissions/${id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: action }),
      });
      const json = await res.json();
      if (json.success) {
        toast.success(`Report ${action}`);
        setSubmissions(s => s.filter(x => x.id !== id));
      } else toast.error(json.error);
    } catch { toast.error('Action failed'); }
  }

  const statCards = stats ? [
    { label: 'Leaders',     value: stats.leaders_tracked,      icon: Users,         color: '#f97316' },
    { label: 'Promises',    value: stats.promises_tracked,     icon: FileText,       color: '#22c55e' },
    { label: 'Projects',    value: stats.projects_monitored,   icon: TrendingUp,     color: '#3b82f6' },
    { label: 'Corruption',  value: stats.corruption_cases,     icon: AlertTriangle,  color: '#ef4444' },
    { label: 'Verified',    value: stats.verified_reports,     icon: CheckCircle,    color: '#8b5cf6' },
    { label: 'Pending',     value: stats.public_submissions,   icon: Clock,          color: '#f59e0b' },
    { label: 'Fake Flags',  value: stats.fake_claims_detected, icon: XCircle,        color: '#ec4899' },
    { label: 'Delayed',     value: stats.delayed_projects,     icon: Clock,          color: '#64748b' },
  ] : [];

  return (
    <div className="min-h-screen">
      {/* Admin Header */}
      <div className="glass border-b border-slate-800 px-6 py-4 flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="gradient-bg p-2 rounded-xl"><Shield className="w-6 h-6 text-white" /></div>
          <div>
            <h1 className="font-black text-white text-lg">NetaTrack Admin</h1>
            <p className="text-xs text-slate-400">Super Admin Panel</p>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <span className="w-2 h-2 bg-green-400 rounded-full pulse-glow" />
          <span className="text-xs text-green-400">Live</span>
        </div>
      </div>

      {/* Tab Nav */}
      <div className="flex gap-1 px-6 pt-4">
        {(['dashboard','submissions','leaders','settings'] as const).map(tab => (
          <button key={tab} onClick={() => setActiveTab(tab)}
            className={`px-5 py-2 rounded-full text-sm font-semibold capitalize transition ${
              activeTab === tab ? 'gradient-bg text-white' : 'glass text-slate-400 hover:text-white'
            }`}>{tab}</button>
        ))}
      </div>

      <div className="max-w-7xl mx-auto px-6 py-6">
        {/* DASHBOARD TAB */}
        {activeTab === 'dashboard' && (
          <>
            {loading ? (
              <div className="text-center py-20 text-slate-400">Loading dashboard...</div>
            ) : (
              <>
                <h2 className="text-xl font-bold mb-4 text-slate-200">Platform Overview</h2>
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                  {statCards.map(s => {
                    const Icon = s.icon;
                    return (
                      <div key={s.label} className="glass rounded-2xl p-4">
                        <div className="flex items-center justify-between mb-2">
                          <span className="text-xs text-slate-400">{s.label}</span>
                          <Icon className="w-4 h-4" style={{ color: s.color }} />
                        </div>
                        <p className="text-2xl font-black text-white">{s.value ?? 0}</p>
                      </div>
                    );
                  })}
                </div>
              </>
            )}
          </>
        )}

        {/* SUBMISSIONS TAB */}
        {activeTab === 'submissions' && (
          <>
            <h2 className="text-xl font-bold mb-4 text-slate-200">Pending Public Submissions</h2>
            {submissions.length === 0 ? (
              <div className="text-center py-20 text-slate-400">
                <CheckCircle className="w-12 h-12 mx-auto mb-3 opacity-30" />
                <p>No pending submissions</p>
              </div>
            ) : (
              <div className="space-y-3">
                {submissions.map(sub => (
                  <div key={sub.id} className="glass rounded-2xl p-4">
                    <div className="flex items-start justify-between gap-4">
                      <div className="flex-1">
                        <div className="flex items-center gap-2 mb-1">
                          <span className="text-xs px-2 py-0.5 bg-orange-500/20 text-orange-400 rounded-full">{sub.submission_type}</span>
                          <span className="text-xs text-slate-500">{new Date(sub.created_at).toLocaleDateString('en-IN')}</span>
                        </div>
                        <h3 className="font-semibold text-white">{sub.title}</h3>
                        <p className="text-sm text-slate-400">{sub.leader_name} • {sub.state}</p>
                        {sub.ai_spam_score > 60 && (
                          <p className="text-xs text-red-400 mt-1">⚠ High spam score: {sub.ai_spam_score}%</p>
                        )}
                      </div>
                      <div className="flex gap-2 shrink-0">
                        <button onClick={() => handleApprove(sub.id, 'approved')}
                          className="px-3 py-1.5 bg-green-600 hover:bg-green-500 rounded-xl text-sm font-semibold transition">
                          Approve
                        </button>
                        <button onClick={() => handleApprove(sub.id, 'rejected')}
                          className="px-3 py-1.5 bg-red-700 hover:bg-red-600 rounded-xl text-sm font-semibold transition">
                          Reject
                        </button>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </>
        )}

        {/* LEADERS TAB */}
        {activeTab === 'leaders' && (
          <>
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-xl font-bold text-slate-200">Manage Leaders</h2>
              <a href="/admin/leaders/new" className="gradient-bg px-4 py-2 rounded-xl text-sm font-bold text-white hover:opacity-90 transition">
                + Add Leader
              </a>
            </div>
            <div className="glass rounded-2xl p-6 text-center text-slate-400">
              <Users className="w-12 h-12 mx-auto mb-3 opacity-30" />
              <p>Leader management coming soon.</p>
              <p className="text-sm mt-1">Add leaders via database seeder for now.</p>
            </div>
          </>
        )}

        {/* SETTINGS TAB */}
        {activeTab === 'settings' && (
          <>
            <h2 className="text-xl font-bold mb-4 text-slate-200">Platform Settings</h2>
            <div className="grid md:grid-cols-2 gap-4">
              {[{ title: 'Website Settings', desc: 'Logo, title, SEO, meta tags' }, { title: 'SMTP Email', desc: 'Gmail, SendGrid, Mailgun config' }, { title: 'AI Configuration', desc: 'OpenAI, Gemini, OpenRouter keys' }, { title: 'Advertisement', desc: 'Google Ads, banner management' }].map(s => (
                <div key={s.title} className="glass rounded-2xl p-5">
                  <h3 className="font-bold text-white mb-1">{s.title}</h3>
                  <p className="text-sm text-slate-400 mb-3">{s.desc}</p>
                  <button className="text-sm text-orange-400 hover:text-orange-300 font-semibold">Configure →</button>
                </div>
              ))}
            </div>
          </>
        )}
      </div>
    </div>
  );
}

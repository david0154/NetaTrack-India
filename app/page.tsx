import Link from 'next/link';
import { Shield, TrendingUp, AlertTriangle, CheckCircle, Users, FileText, Building2, Search } from 'lucide-react';
import StatCard from '@/components/StatCard';

async function getStats() {
  try {
    const base = process.env.NEXT_PUBLIC_BASE_URL || 'http://localhost:3000';
    const res = await fetch(`${base}/api/stats`, { next: { revalidate: 60 } });
    const json = await res.json();
    return json.success ? json.data : null;
  } catch { return null; }
}

export default async function HomePage() {
  const stats = await getStats();

  const statCards = [
    { label: 'Promises Tracked',     value: stats?.promises_tracked     ?? 0, icon: CheckCircle, color: '#22c55e' },
    { label: 'Projects Monitored',   value: stats?.projects_monitored   ?? 0, icon: Building2,   color: '#3b82f6' },
    { label: 'Corruption Cases',     value: stats?.corruption_cases     ?? 0, icon: AlertTriangle,color: '#ef4444' },
    { label: 'Leaders Tracked',      value: stats?.leaders_tracked      ?? 0, icon: Users,        color: '#f97316' },
    { label: 'Verified Reports',     value: stats?.verified_reports     ?? 0, icon: Shield,       color: '#8b5cf6' },
    { label: 'Delayed Projects',     value: stats?.delayed_projects     ?? 0, icon: TrendingUp,   color: '#f59e0b' },
  ];

  return (
    <div>
      {/* HERO */}
      <section className="relative overflow-hidden py-24 px-4">
        <div className="absolute inset-0 pointer-events-none">
          <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl" />
          <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl" />
        </div>
        <div className="max-w-5xl mx-auto text-center relative">
          <div className="inline-flex items-center gap-2 glass px-4 py-2 rounded-full text-sm text-orange-400 mb-6">
            <span className="w-2 h-2 bg-green-400 rounded-full pulse-glow" />
            Live political transparency platform
          </div>
          <h1 className="text-5xl md:text-7xl font-black mb-6 leading-tight">
            Hold Your
            <span className="gradient-text block">Neta Accountable</span>
          </h1>
          <p className="text-xl text-slate-400 mb-10 max-w-2xl mx-auto">
            AI-powered platform tracking political promises, government projects, budgets, and corruption allegations across all 28 Indian states.
          </p>
          <div className="flex flex-wrap justify-center gap-4">
            <Link href="/leaders" className="gradient-bg px-8 py-3 rounded-full font-bold text-white hover:opacity-90 transition glow-orange">
              Explore Leaders
            </Link>
            <Link href="/submit" className="glass px-8 py-3 rounded-full font-bold text-white hover:bg-white/10 transition">
              Submit Report
            </Link>
          </div>
        </div>
      </section>

      {/* STATS */}
      <section className="max-w-7xl mx-auto px-4 pb-16">
        <h2 className="text-2xl font-bold text-center mb-8 text-slate-200">
          Platform <span className="gradient-text">Statistics</span>
        </h2>
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          {statCards.map(s => (
            <StatCard key={s.label} label={s.label} value={s.value} icon={s.icon} color={s.color} />
          ))}
        </div>
      </section>

      {/* FEATURES */}
      <section className="max-w-7xl mx-auto px-4 pb-20">
        <h2 className="text-3xl font-bold text-center mb-12">
          What <span className="gradient-text">NetaTrack</span> Does
        </h2>
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {[
            { icon: CheckCircle, title: 'Promise Tracking', desc: 'Track every promise made by political leaders with real-time status updates and AI-verified fact checking.', color: '#22c55e', href: '/promises' },
            { icon: Building2,   title: 'Project Monitoring', desc: 'Monitor government projects — budget allocated, spent, progress percentage, and delay alerts.', color: '#3b82f6', href: '/projects' },
            { icon: AlertTriangle, title: 'Corruption Cases', desc: 'Track CBI, ED, court cases and corruption allegations with severity scores.', color: '#ef4444', href: '/corruption' },
            { icon: Users,      title: 'Leader Scores', desc: 'AI-generated scores for every leader based on promise completion, project delivery, and public satisfaction.', color: '#f97316', href: '/leaders' },
            { icon: FileText,   title: 'Public Submissions', desc: 'Citizens can submit corruption reports, fake claims, and evidence that gets AI-verified and published.', color: '#8b5cf6', href: '/submit' },
            { icon: Search,     title: 'AI Search', desc: 'Search across leaders, promises, projects and corruption cases with intelligent AI-powered results.', color: '#06b6d4', href: '/search' },
          ].map(f => (
            <Link key={f.title} href={f.href}>
              <div className="glass rounded-2xl p-6 card-hover h-full">
                <div className="p-3 rounded-xl w-fit mb-4" style={{ background: `${f.color}20` }}>
                  <f.icon className="w-7 h-7" style={{ color: f.color }} />
                </div>
                <h3 className="font-bold text-lg mb-2 text-white">{f.title}</h3>
                <p className="text-slate-400 text-sm leading-relaxed">{f.desc}</p>
              </div>
            </Link>
          ))}
        </div>
      </section>

      {/* CTA */}
      <section className="max-w-4xl mx-auto px-4 pb-24">
        <div className="glass rounded-3xl p-10 text-center glow-orange">
          <h2 className="text-3xl font-black mb-4">
            See Something Wrong?
            <span className="gradient-text block">Report It.</span>
          </h2>
          <p className="text-slate-400 mb-8">Submit corruption reports, project delays, fake promises, or infrastructure damage. Your report goes through AI verification and admin review before publishing.</p>
          <Link href="/submit" className="gradient-bg px-10 py-4 rounded-full font-bold text-white text-lg hover:opacity-90 transition inline-block">
            Submit a Report
          </Link>
        </div>
      </section>
    </div>
  );
}

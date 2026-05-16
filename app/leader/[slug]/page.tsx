import { notFound } from 'next/navigation';
import { User, ExternalLink, AlertTriangle, CheckCircle, Clock } from 'lucide-react';
import { scoreToRank, corruptionLevel, formatCrore } from '@/lib/utils';

async function getLeader(slug: string) {
  try {
    const base = process.env.NEXT_PUBLIC_BASE_URL || 'http://localhost:3000';
    const res = await fetch(`${base}/api/leaders/${slug}`, { next: { revalidate: 30 } });
    const json = await res.json();
    return json.success ? json.data : null;
  } catch { return null; }
}

export default async function LeaderDetailPage({ params }: { params: { slug: string } }) {
  const leader = await getLeader(params.slug);
  if (!leader) notFound();

  const rank    = scoreToRank(leader.final_score);
  const corrupt = corruptionLevel(leader.corruption_level);

  const scores = [
    { label: 'Promise Completion', value: leader.promise_score,      color: '#22c55e' },
    { label: 'Project Delivery',   value: leader.project_score,       color: '#3b82f6' },
    { label: 'Budget Efficiency',  value: leader.transparency_score,  color: '#f59e0b' },
    { label: 'Public Satisfaction',value: leader.public_score,        color: '#8b5cf6' },
  ];

  return (
    <div className="max-w-5xl mx-auto px-4 py-10">
      {/* Header */}
      <div className="glass rounded-3xl p-8 mb-8 flex flex-col sm:flex-row gap-6 items-start">
        {leader.photo_url
          ? <img src={leader.photo_url} alt={leader.name} className="w-28 h-28 rounded-2xl object-cover border-2" style={{ borderColor: rank.color }} />
          : <div className="w-28 h-28 rounded-2xl flex items-center justify-center" style={{ background: `${rank.color}20` }}><User className="w-14 h-14" style={{ color: rank.color }} /></div>
        }
        <div className="flex-1">
          <h1 className="text-3xl font-black text-white mb-1">{leader.name}</h1>
          <p className="text-slate-400 mb-2">{leader.position} • {leader.party_name} • {leader.state_name}</p>
          <div className="flex items-center gap-3">
            <span className="px-3 py-1 rounded-full text-sm font-bold" style={{ background: `${rank.color}20`, color: rank.color }}>
              {rank.label} — Score: {leader.final_score.toFixed(1)}
            </span>
            {leader.corruption_level > 30 && (
              <span className="flex items-center gap-1 text-sm" style={{ color: corrupt.color }}>
                <AlertTriangle className="w-4 h-4" />{corrupt.label}
              </span>
            )}
          </div>
        </div>
      </div>

      {/* Scores */}
      <div className="glass rounded-2xl p-6 mb-6">
        <h2 className="text-xl font-bold mb-6">Performance <span className="gradient-text">Scores</span></h2>
        <div className="space-y-5">
          {scores.map(s => (
            <div key={s.label}>
              <div className="flex justify-between text-sm mb-2">
                <span className="text-slate-300">{s.label}</span>
                <span style={{ color: s.color }} className="font-bold">{s.value.toFixed(1)}%</span>
              </div>
              <div className="h-3 bg-white/10 rounded-full overflow-hidden">
                <div className="h-full rounded-full transition-all duration-1000" style={{ width: `${s.value}%`, background: s.color }} />
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Promises */}
      {leader.promises.length > 0 && (
        <div className="glass rounded-2xl p-6 mb-6">
          <h2 className="text-xl font-bold mb-4">Recent <span className="gradient-text">Promises</span></h2>
          <div className="space-y-3">
            {leader.promises.map((p: any) => (
              <div key={p.id} className="flex items-center justify-between p-3 bg-white/5 rounded-xl">
                <span className="text-slate-200 text-sm">{p.title}</span>
                <span className={`text-xs px-2 py-0.5 rounded-full status-${p.status}`}>{p.status}</span>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Corruption Cases */}
      {leader.corruption.length > 0 && (
        <div className="glass rounded-2xl p-6 mb-6">
          <h2 className="text-xl font-bold mb-4 flex items-center gap-2">
            <AlertTriangle className="w-5 h-5 text-red-500" />
            Corruption <span className="gradient-text">Cases</span>
          </h2>
          <div className="space-y-3">
            {leader.corruption.map((c: any) => (
              <div key={c.id} className="p-4 bg-red-500/5 border border-red-500/20 rounded-xl">
                <div className="flex items-start justify-between gap-3">
                  <div>
                    <p className="text-white text-sm font-medium">{c.title}</p>
                    <p className="text-slate-400 text-xs mt-1">{c.agency} • {c.status}</p>
                  </div>
                  {c.amount_crore && <span className="text-red-400 text-sm font-bold whitespace-nowrap">{formatCrore(c.amount_crore)}</span>}
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

      {leader.bio && (
        <div className="glass rounded-2xl p-6">
          <h2 className="text-xl font-bold mb-4">About</h2>
          <p className="text-slate-300 leading-relaxed">{leader.bio}</p>
        </div>
      )}
    </div>
  );
}

import { AlertTriangle, Scale } from 'lucide-react';
import type { CorruptionCase } from '@/lib/types';

async function getCases(page = 1) {
  try {
    const base = process.env.NEXT_PUBLIC_BASE_URL || 'http://localhost:3000';
    const res = await fetch(`${base}/api/corruption?page=${page}&per_page=24`, { next: { revalidate: 30 } });
    const json = await res.json();
    return json.success ? json : { data: [], pagination: null };
  } catch { return { data: [], pagination: null }; }
}

const severityColors: Record<string, string> = {
  critical: '#ef4444',
  high:     '#f97316',
  medium:   '#f59e0b',
  low:      '#22c55e',
};

export default async function CorruptionPage() {
  const { data: cases } = await getCases();

  return (
    <div className="max-w-7xl mx-auto px-4 py-10">
      <div className="mb-10">
        <h1 className="text-4xl font-black mb-2">Corruption <span className="gradient-text">Tracker</span></h1>
        <p className="text-slate-400">Track CBI, ED, court cases and corruption allegations across political leaders.</p>
      </div>
      <div className="flex flex-wrap gap-3 mb-8">
        {['All', 'Critical', 'High', 'Medium', 'Low'].map(s => (
          <button key={s} className="px-4 py-2 glass rounded-full text-sm hover:bg-white/10 transition">{s}</button>
        ))}
      </div>
      {cases.length === 0 ? (
        <div className="text-center py-24 text-slate-400">
          <Scale className="w-16 h-16 mx-auto mb-4 opacity-30" />
          <p className="text-xl mb-2">No corruption cases found.</p>
          <p className="text-sm">Cases will appear once added via the admin panel.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
          {cases.map((c: CorruptionCase) => {
            const color = severityColors[c.severity] ?? '#64748b';
            return (
              <div key={c.id} className="glass rounded-2xl p-5 card-hover">
                <div className="flex items-start justify-between mb-3">
                  <span className="text-xs px-3 py-1 rounded-full font-bold uppercase" style={{ background: `${color}25`, color }}>
                    {c.severity} severity
                  </span>
                  <span className="text-xs text-slate-500 uppercase">{c.agency}</span>
                </div>
                <h3 className="font-bold text-white mb-1">{c.title}</h3>
                {c.leader_name && <p className="text-sm text-orange-400 mb-2">{c.leader_name}</p>}
                {c.description && <p className="text-sm text-slate-400 mb-3 line-clamp-2">{c.description}</p>}
                <div className="flex items-center justify-between text-xs text-slate-500">
                  <span>{c.status}</span>
                  {c.amount_crore && <span className="text-red-400 font-semibold">₹{c.amount_crore} Cr</span>}
                  {c.reported_date && <span>{new Date(c.reported_date).toLocaleDateString('en-IN')}</span>}
                </div>
                {c.source_url && (
                  <a href={c.source_url} target="_blank" rel="noopener noreferrer"
                    className="text-xs text-blue-400 hover:underline mt-2 inline-block">View Source ↗</a>
                )}
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}

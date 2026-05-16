import LeaderCard from '@/components/LeaderCard';
import type { Leader } from '@/lib/types';

async function getLeaders(page = 1, q = '') {
  try {
    const base = process.env.NEXT_PUBLIC_BASE_URL || 'http://localhost:3000';
    const url  = `${base}/api/leaders?page=${page}&limit=24${q ? `&q=${q}` : ''}`;
    const res  = await fetch(url, { next: { revalidate: 30 } });
    const json = await res.json();
    return json.success ? json : { data: [], pagination: null };
  } catch { return { data: [], pagination: null }; }
}

export default async function LeadersPage() {
  const { data: leaders, pagination } = await getLeaders();

  return (
    <div className="max-w-7xl mx-auto px-4 py-10">
      <div className="mb-10">
        <h1 className="text-4xl font-black mb-2">Political <span className="gradient-text">Leaders</span></h1>
        <p className="text-slate-400">Browse leader performance scores across all Indian states. Ranked by AI-generated accountability score.</p>
      </div>

      <div className="flex flex-wrap gap-3 mb-8">
        {['All', 'Excellent', 'Good', 'Average', 'Poor'].map(f => (
          <button key={f}
            className="px-4 py-2 glass rounded-full text-sm hover:bg-white/10 transition">
            {f}
          </button>
        ))}
      </div>

      {leaders.length === 0 ? (
        <div className="text-center py-24 text-slate-400">
          <p className="text-xl mb-2">No leaders found.</p>
          <p className="text-sm">Leaders will appear once added via the admin panel.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
          {leaders.map((leader: Leader) => <LeaderCard key={leader.id} leader={leader} />)}
        </div>
      )}

      {pagination && pagination.pages > 1 && (
        <div className="mt-10 flex justify-center gap-3">
          {Array.from({ length: pagination.pages }, (_, i) => i + 1).map(p => (
            <button key={p} className={`w-10 h-10 rounded-full font-bold transition ${
              p === pagination.page ? 'gradient-bg text-white' : 'glass hover:bg-white/10 text-slate-300'
            }`}>{p}</button>
          ))}
        </div>
      )}
    </div>
  );
}

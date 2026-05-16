import PromiseCard from '@/components/PromiseCard';
import type { Promise_ } from '@/lib/types';

async function getPromises(status?: string) {
  try {
    const base = process.env.NEXT_PUBLIC_BASE_URL || 'http://localhost:3000';
    const url  = `${base}/api/promises?limit=24${status ? `&status=${status}` : ''}`;
    const res  = await fetch(url, { next: { revalidate: 30 } });
    const json = await res.json();
    return json.success ? json.data : [];
  } catch { return []; }
}

export default async function PromisesPage() {
  const promises: Promise_[] = await getPromises();

  const statusCounts = promises.reduce((acc: Record<string, number>, p: Promise_) => {
    acc[p.status] = (acc[p.status] || 0) + 1;
    return acc;
  }, {});

  return (
    <div className="max-w-7xl mx-auto px-4 py-10">
      <div className="mb-8">
        <h1 className="text-4xl font-black mb-2">Political <span className="gradient-text">Promises</span></h1>
        <p className="text-slate-400">Track every promise made by Indian politicians. AI-verified statuses updated in real-time.</p>
      </div>

      <div className="flex flex-wrap gap-3 mb-8">
        {[['All', ''], ['Completed', 'completed'], ['In Progress', 'in_progress'], ['Pending', 'pending'], ['Delayed', 'delayed'], ['Failed', 'failed'], ['Fake', 'fake']].map(([label, val]) => (
          <a key={val} href={val ? `?status=${val}` : '/promises'}
            className="px-4 py-2 glass rounded-full text-sm hover:bg-white/10 transition">
            {label}
            {val && statusCounts[val] ? <span className="ml-1 text-xs opacity-60">({statusCounts[val]})</span> : null}
          </a>
        ))}
      </div>

      {promises.length === 0 ? (
        <div className="text-center py-24 text-slate-400">
          <p className="text-xl mb-2">No promises found.</p>
          <p className="text-sm">Promises will appear once added via admin panel or auto-scraper.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          {promises.map((p: Promise_) => <PromiseCard key={p.id} promise={p} />)}
        </div>
      )}
    </div>
  );
}

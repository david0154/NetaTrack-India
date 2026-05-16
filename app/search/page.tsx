'use client';
import { useState, useCallback } from 'react';
import Link from 'next/link';
import { Search, Users, FileText, Building2, AlertTriangle } from 'lucide-react';
import { useRouter, useSearchParams } from 'next/navigation';

export default function SearchPage() {
  const searchParams = useSearchParams();
  const [query, setQuery] = useState(searchParams.get('q') ?? '');
  const [results, setResults] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  const doSearch = useCallback(async (q: string) => {
    if (q.trim().length < 2) return;
    setLoading(true);
    try {
      const res = await fetch(`/api/search?q=${encodeURIComponent(q)}`);
      const json = await res.json();
      if (json.success) setResults(json.data);
    } catch {}
    finally { setLoading(false); }
  }, []);

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    doSearch(query);
  }

  const totalResults = results
    ? (results.leaders?.length + results.promises?.length + results.projects?.length + results.corruptions?.length)
    : 0;

  return (
    <div className="max-w-4xl mx-auto px-4 py-10">
      <h1 className="text-4xl font-black mb-2">AI <span className="gradient-text">Search</span></h1>
      <p className="text-slate-400 mb-8">Search across leaders, promises, projects, and corruption cases.</p>

      <form onSubmit={handleSubmit} className="flex gap-3 mb-8">
        <div className="relative flex-1">
          <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
          <input
            type="text" value={query} onChange={e => setQuery(e.target.value)}
            placeholder="Search leaders, promises, projects..."
            className="w-full bg-slate-800/60 border border-slate-700 rounded-2xl pl-12 pr-4 py-4 text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 transition"
          />
        </div>
        <button type="submit" disabled={loading}
          className="gradient-bg px-8 py-4 rounded-2xl font-bold text-white hover:opacity-90 transition disabled:opacity-50">
          {loading ? '...' : 'Search'}
        </button>
      </form>

      {results && (
        <div className="space-y-6">
          <p className="text-slate-400 text-sm">{totalResults} results found</p>

          {results.leaders?.length > 0 && (
            <div>
              <h2 className="flex items-center gap-2 font-bold text-lg mb-3"><Users className="w-5 h-5 text-orange-400" />Leaders</h2>
              <div className="space-y-2">
                {results.leaders.map((l: any) => (
                  <Link key={l.id} href={`/leader/${l.slug}`}>
                    <div className="glass rounded-xl p-4 card-hover flex items-center justify-between">
                      <span className="font-semibold text-white">{l.name}</span>
                      <span className="text-sm text-slate-400">{l.designation}</span>
                      <span className="text-sm font-bold" style={{ color: l.final_score >= 75 ? '#22c55e' : l.final_score >= 50 ? '#f59e0b' : '#ef4444' }}>{l.final_score ?? 0}/100</span>
                    </div>
                  </Link>
                ))}
              </div>
            </div>
          )}

          {results.promises?.length > 0 && (
            <div>
              <h2 className="flex items-center gap-2 font-bold text-lg mb-3"><FileText className="w-5 h-5 text-green-400" />Promises</h2>
              <div className="space-y-2">
                {results.promises.map((p: any) => (
                  <Link key={p.id} href={`/promise/${p.slug}`}>
                    <div className="glass rounded-xl p-4 card-hover flex items-center justify-between">
                      <span className="font-semibold text-white">{p.title}</span>
                      <span className="text-xs px-2 py-1 rounded-full" style={{ background: p.status === 'completed' ? '#22c55e20' : '#f9740620', color: p.status === 'completed' ? '#22c55e' : '#f97316' }}>{p.status}</span>
                    </div>
                  </Link>
                ))}
              </div>
            </div>
          )}

          {results.projects?.length > 0 && (
            <div>
              <h2 className="flex items-center gap-2 font-bold text-lg mb-3"><Building2 className="w-5 h-5 text-blue-400" />Projects</h2>
              <div className="space-y-2">
                {results.projects.map((p: any) => (
                  <div key={p.id} className="glass rounded-xl p-4 flex items-center justify-between">
                    <span className="font-semibold text-white">{p.title}</span>
                    <span className="text-sm text-slate-400">{p.progress_percent}% done</span>
                  </div>
                ))}
              </div>
            </div>
          )}

          {results.corruptions?.length > 0 && (
            <div>
              <h2 className="flex items-center gap-2 font-bold text-lg mb-3"><AlertTriangle className="w-5 h-5 text-red-400" />Corruption Cases</h2>
              <div className="space-y-2">
                {results.corruptions.map((c: any) => (
                  <div key={c.id} className="glass rounded-xl p-4 flex items-center justify-between">
                    <span className="font-semibold text-white">{c.title}</span>
                    <span className="text-xs text-red-400 font-bold uppercase">{c.severity}</span>
                  </div>
                ))}
              </div>
            </div>
          )}

          {totalResults === 0 && (
            <div className="text-center py-16 text-slate-400">
              <Search className="w-12 h-12 mx-auto mb-3 opacity-30" />
              <p>No results found for &ldquo;{query}&rdquo;</p>
            </div>
          )}
        </div>
      )}
    </div>
  );
}

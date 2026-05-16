import Link from 'next/link';
import { leaders, promises, stats } from '@/data/mock';

export default function HomePage() {
  return (
    <div className="space-y-8">
      <section className="card">
        <h1 className="text-3xl font-bold">Political Transparency Dashboard</h1>
        <p className="text-slate-300 mt-2">Track announcements, budgets, and project progress across India with verification-first moderation.</p>
      </section>

      <section className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        {Object.entries(stats).map(([key, value]) => (
          <div key={key} className="card">
            <p className="text-slate-400 text-sm capitalize">{key}</p>
            <p className="text-2xl font-semibold">{value}</p>
          </div>
        ))}
      </section>

      <section className="card">
        <h2 className="text-xl font-semibold mb-3">Latest Verified Announcements</h2>
        <div className="space-y-3">
          {promises.map((p) => (
            <Link key={p.slug} href={`/promise/${p.slug}`} className="block border border-slate-800 rounded-lg p-3 hover:bg-slate-800/50">
              <div className="flex justify-between"><span>{p.title}</span><span>{p.status}</span></div>
              <p className="text-sm text-slate-400">{p.state} • Verification: {p.verification} • AI: {p.ai}%</p>
            </Link>
          ))}
        </div>
      </section>

      <section className="card">
        <h2 className="text-xl font-semibold mb-3">Trending Leaders</h2>
        <div className="grid md:grid-cols-3 gap-4">
          {leaders.map((l) => (
            <Link key={l.slug} href={`/leader/${l.slug}`} className="border border-slate-800 rounded-lg p-3">
              <p className="font-medium">{l.name}</p>
              <p className="text-sm text-slate-400">{l.party} • {l.state}</p>
              <p className="text-sm">Completion: {l.completion}%</p>
            </Link>
          ))}
        </div>
      </section>
    </div>
  );
}

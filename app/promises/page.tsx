import Link from 'next/link';
import { promises } from '@/data/mock';

export default function PromisesPage() {
  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">All Promises</h1>
      {promises.map((p) => (
        <Link key={p.slug} href={`/promise/${p.slug}`} className="card block">
          <p className="font-semibold">{p.title}</p>
          <p className="text-sm text-slate-400">{p.state} • {p.status}</p>
        </Link>
      ))}
    </div>
  );
}

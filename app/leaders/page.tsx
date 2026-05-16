import Link from 'next/link';
import { leaders } from '@/data/mock';

export default function LeadersPage() {
  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">Leaders</h1>
      {leaders.map((l) => (
        <Link key={l.slug} href={`/leader/${l.slug}`} className="card block">
          <p className="font-semibold">{l.name}</p>
          <p className="text-sm text-slate-400">{l.party} • {l.state} • Completion {l.completion}%</p>
        </Link>
      ))}
    </div>
  );
}

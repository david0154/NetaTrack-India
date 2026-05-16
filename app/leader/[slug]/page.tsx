import { notFound } from 'next/navigation';
import { leaders } from '@/data/mock';

export default function LeaderDetail({ params }: { params: { slug: string } }) {
  const leader = leaders.find((l) => l.slug === params.slug);
  if (!leader) return notFound();

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">{leader.name}</h1>
      <div className="card">
        <p>Party: {leader.party}</p>
        <p>State: {leader.state}</p>
        <p>Completion score: {leader.completion}%</p>
      </div>
    </div>
  );
}

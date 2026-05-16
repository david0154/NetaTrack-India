import { notFound } from 'next/navigation';
import { promises } from '@/data/mock';

export default function PromiseDetail({ params }: { params: { slug: string } }) {
  const item = promises.find((p) => p.slug === params.slug);
  if (!item) return notFound();

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">{item.title}</h1>
      <div className="card">
        <p>State: {item.state}</p>
        <p>Status: {item.status}</p>
        <p>Verification: {item.verification}</p>
        <p>AI confidence: {item.ai}%</p>
      </div>
    </div>
  );
}

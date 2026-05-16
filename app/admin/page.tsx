const queue = [
  { type: 'AI', title: 'New scheme launch in Bihar', score: 86, status: 'Pending Review' },
  { type: 'Public', title: 'Road project delay report', score: 59, status: 'Need More Proof' }
];

export default function AdminPage() {
  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-bold">Admin Moderation Panel</h1>
      <div className="grid md:grid-cols-3 gap-4">
        <div className="card"><p>Pending AI Data</p><p className="text-2xl font-semibold">42</p></div>
        <div className="card"><p>Public Submissions</p><p className="text-2xl font-semibold">27</p></div>
        <div className="card"><p>Fake Flags</p><p className="text-2xl font-semibold">8</p></div>
      </div>
      <div className="card space-y-2">
        {queue.map((q) => (
          <div key={q.title} className="border border-slate-800 rounded p-3">
            <p className="font-semibold">{q.title}</p>
            <p className="text-sm text-slate-400">{q.type} • Score {q.score}% • {q.status}</p>
          </div>
        ))}
      </div>
    </div>
  );
}

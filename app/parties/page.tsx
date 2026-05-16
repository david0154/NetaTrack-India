import Link from 'next/link';
import { Flag, Users } from 'lucide-react';

async function getParties() {
  try {
    const base = process.env.NEXT_PUBLIC_BASE_URL || 'http://localhost:3000';
    const res = await fetch(`${base}/api/parties`, { next: { revalidate: 60 } });
    const json = await res.json();
    return json.success ? json.data : [];
  } catch { return []; }
}

export default async function PartiesPage() {
  const parties = await getParties();

  return (
    <div className="max-w-7xl mx-auto px-4 py-10">
      <div className="mb-10">
        <h1 className="text-4xl font-black mb-2">Political <span className="gradient-text">Parties</span></h1>
        <p className="text-slate-400">Browse all registered political parties tracked on NetaTrack India.</p>
      </div>
      {parties.length === 0 ? (
        <div className="text-center py-24 text-slate-400">
          <Flag className="w-16 h-16 mx-auto mb-4 opacity-30" />
          <p className="text-xl mb-2">No parties found.</p>
          <p className="text-sm">Parties will appear once added via the admin panel.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {parties.map((party: any) => (
            <Link key={party.id} href={`/leaders?party_id=${party.id}`}>
              <div className="glass rounded-2xl p-5 card-hover">
                <div className="flex items-center gap-3 mb-3">
                  {party.symbol_url ? (
                    <img src={party.symbol_url} alt={party.name} className="w-10 h-10 rounded-full object-cover" />
                  ) : (
                    <div className="w-10 h-10 rounded-full flex items-center justify-center font-black text-white"
                      style={{ background: party.color_code ?? '#f97316' }}>
                      {party.abbreviation?.[0]}
                    </div>
                  )}
                  <div>
                    <p className="font-bold text-white text-sm">{party.name}</p>
                    <p className="text-xs text-slate-400">{party.abbreviation}</p>
                  </div>
                </div>
                {party.ideology && <p className="text-xs text-slate-500 mb-3">{party.ideology}</p>}
                <div className="flex items-center gap-1 text-xs text-slate-400">
                  <Users className="w-3 h-3" />
                  <span>{party.leaders_count ?? 0} leaders tracked</span>
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}

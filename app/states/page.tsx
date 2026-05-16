import Link from 'next/link';
import { MapPin, Users, FileText, Building2 } from 'lucide-react';

async function getStates() {
  try {
    const base = process.env.NEXT_PUBLIC_BASE_URL || 'http://localhost:3000';
    const res = await fetch(`${base}/api/states`, { next: { revalidate: 60 } });
    const json = await res.json();
    return json.success ? json.data : [];
  } catch { return []; }
}

export default async function StatesPage() {
  const states = await getStates();

  return (
    <div className="max-w-7xl mx-auto px-4 py-10">
      <div className="mb-10">
        <h1 className="text-4xl font-black mb-2">Indian <span className="gradient-text">States</span></h1>
        <p className="text-slate-400">Explore political transparency data across all 28 states and 8 union territories.</p>
      </div>
      {states.length === 0 ? (
        <div className="text-center py-24 text-slate-400">
          <MapPin className="w-16 h-16 mx-auto mb-4 opacity-30" />
          <p className="text-xl mb-2">No states found.</p>
          <p className="text-sm">States will appear once seeded in the database.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          {states.map((state: any) => (
            <Link key={state.id} href={`/leaders?state_id=${state.id}`}>
              <div className="glass rounded-2xl p-5 card-hover h-full">
                <div className="flex items-center gap-2 mb-3">
                  <MapPin className="w-5 h-5 text-orange-400" />
                  <span className="font-bold text-white">{state.name}</span>
                  <span className="text-xs text-slate-500 ml-auto">{state.code}</span>
                </div>
                {state.capital && <p className="text-xs text-slate-400 mb-3">Capital: {state.capital}</p>}
                <div className="grid grid-cols-3 gap-2 text-center">
                  <div>
                    <Users className="w-4 h-4 text-blue-400 mx-auto mb-1" />
                    <p className="text-xs font-bold text-white">{state.leaders_count ?? 0}</p>
                    <p className="text-xs text-slate-500">Leaders</p>
                  </div>
                  <div>
                    <FileText className="w-4 h-4 text-green-400 mx-auto mb-1" />
                    <p className="text-xs font-bold text-white">{state.promises_count ?? 0}</p>
                    <p className="text-xs text-slate-500">Promises</p>
                  </div>
                  <div>
                    <Building2 className="w-4 h-4 text-purple-400 mx-auto mb-1" />
                    <p className="text-xs font-bold text-white">{state.projects_count ?? 0}</p>
                    <p className="text-xs text-slate-500">Projects</p>
                  </div>
                </div>
              </div>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
}

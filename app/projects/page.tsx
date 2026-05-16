import Link from 'next/link';
import { Building2, Clock, CheckCircle, XCircle, AlertTriangle } from 'lucide-react';
import type { Project } from '@/lib/types';

async function getProjects(page = 1) {
  try {
    const base = process.env.NEXT_PUBLIC_BASE_URL || 'http://localhost:3000';
    const res = await fetch(`${base}/api/projects?page=${page}&per_page=24`, { next: { revalidate: 30 } });
    const json = await res.json();
    return json.success ? json : { data: [], pagination: null };
  } catch { return { data: [], pagination: null }; }
}

const statusConfig: Record<string, { label: string; color: string; icon: any }> = {
  completed:   { label: 'Completed',   color: '#22c55e', icon: CheckCircle },
  in_progress: { label: 'In Progress', color: '#3b82f6', icon: Clock },
  delayed:     { label: 'Delayed',     color: '#f97316', icon: AlertTriangle },
  cancelled:   { label: 'Cancelled',   color: '#ef4444', icon: XCircle },
  not_started: { label: 'Not Started', color: '#64748b', icon: Clock },
};

export default async function ProjectsPage() {
  const { data: projects, pagination } = await getProjects();

  return (
    <div className="max-w-7xl mx-auto px-4 py-10">
      <div className="mb-10">
        <h1 className="text-4xl font-black mb-2">Government <span className="gradient-text">Projects</span></h1>
        <p className="text-slate-400">Track government infrastructure and development projects across all Indian states.</p>
      </div>
      <div className="flex flex-wrap gap-3 mb-8">
        {['All', 'In Progress', 'Completed', 'Delayed', 'Cancelled'].map(f => (
          <button key={f} className="px-4 py-2 glass rounded-full text-sm hover:bg-white/10 transition">{f}</button>
        ))}
      </div>
      {projects.length === 0 ? (
        <div className="text-center py-24 text-slate-400">
          <Building2 className="w-16 h-16 mx-auto mb-4 opacity-30" />
          <p className="text-xl mb-2">No projects found.</p>
          <p className="text-sm">Projects will appear once added via the admin panel.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          {projects.map((project: Project) => {
            const cfg = statusConfig[project.status] ?? statusConfig.not_started;
            const Icon = cfg.icon;
            const pct = project.progress_percent ?? 0;
            return (
              <div key={project.id} className="glass rounded-2xl p-5 card-hover">
                <div className="flex items-start justify-between mb-3">
                  <span className="text-xs px-2 py-1 rounded-full font-medium" style={{ background: `${cfg.color}20`, color: cfg.color }}>
                    <Icon className="inline w-3 h-3 mr-1" />{cfg.label}
                  </span>
                  {project.category && <span className="text-xs text-slate-500">{project.category}</span>}
                </div>
                <h3 className="font-bold text-white mb-1 line-clamp-2">{project.title}</h3>
                <p className="text-xs text-slate-400 mb-3">
                  {project.leader_name && <span>{project.leader_name} • </span>}
                  {project.state_name}
                </p>
                <div className="mb-3">
                  <div className="flex justify-between text-xs text-slate-400 mb-1">
                    <span>Progress</span><span>{pct}%</span>
                  </div>
                  <div className="w-full bg-slate-800 rounded-full h-2">
                    <div className="h-2 rounded-full transition-all" style={{ width: `${pct}%`, background: cfg.color }} />
                  </div>
                </div>
                {project.budget && (
                  <p className="text-xs text-slate-400">
                    Budget: <span className="text-white font-semibold">₹{(project.budget/1e7).toFixed(1)} Cr</span>
                    {project.spent > 0 && <span> • Spent: ₹{(project.spent/1e7).toFixed(1)} Cr</span>}
                  </p>
                )}
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}

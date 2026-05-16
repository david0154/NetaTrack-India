import Link from 'next/link';
import { CheckCircle, Clock, XCircle, AlertCircle, Calendar } from 'lucide-react';
import type { Promise_ } from '@/lib/types';

const STATUS_CONFIG: Record<string, { icon: any; label: string; cls: string }> = {
  completed:   { icon: CheckCircle, label: 'Completed',   cls: 'status-completed' },
  in_progress: { icon: Clock,       label: 'In Progress', cls: 'status-in_progress' },
  pending:     { icon: Clock,       label: 'Pending',     cls: 'status-pending' },
  delayed:     { icon: AlertCircle, label: 'Delayed',     cls: 'status-delayed' },
  failed:      { icon: XCircle,     label: 'Failed',      cls: 'status-failed' },
  fake:        { icon: XCircle,     label: 'Fake Claim',  cls: 'status-fake' },
};

export default function PromiseCard({ promise }: { promise: Promise_ }) {
  const cfg = STATUS_CONFIG[promise.status] || STATUS_CONFIG.pending;
  const Icon = cfg.icon;

  return (
    <Link href={`/promise/${promise.slug}`}>
      <div className="glass rounded-2xl p-5 card-hover cursor-pointer group">
        <div className="flex items-start justify-between gap-3 mb-3">
          <h3 className="font-semibold text-white group-hover:text-orange-400 transition line-clamp-2">{promise.title}</h3>
          <span className={`text-xs px-2 py-1 rounded-full whitespace-nowrap font-medium flex items-center gap-1 ${cfg.cls}`}>
            <Icon className="w-3 h-3" />{cfg.label}
          </span>
        </div>

        <p className="text-slate-400 text-sm line-clamp-2 mb-3">{promise.description}</p>

        <div className="flex flex-wrap gap-3 text-xs text-slate-500">
          {promise.leader_name && <span className="text-orange-400">{promise.leader_name}</span>}
          {promise.state_name  && <span>📍 {promise.state_name}</span>}
          {promise.category    && <span className="bg-white/10 px-2 py-0.5 rounded-full">{promise.category}</span>}
          {promise.deadline    && (
            <span className="flex items-center gap-1">
              <Calendar className="w-3 h-3" />
              {new Date(promise.deadline).toLocaleDateString('en-IN', { month: 'short', year: 'numeric' })}
            </span>
          )}
        </div>

        {promise.verification_score > 0 && (
          <div className="mt-3">
            <div className="flex justify-between text-xs text-slate-400 mb-1">
              <span>Verification Score</span>
              <span>{promise.verification_score}%</span>
            </div>
            <div className="h-1 bg-white/10 rounded-full">
              <div className="h-full rounded-full bg-green-500" style={{ width: `${promise.verification_score}%` }} />
            </div>
          </div>
        )}
      </div>
    </Link>
  );
}

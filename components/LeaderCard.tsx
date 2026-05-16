import Link from 'next/link';
import { User, TrendingUp, AlertTriangle } from 'lucide-react';
import { scoreToRank, corruptionLevel } from '@/lib/utils';
import type { Leader } from '@/lib/types';

export default function LeaderCard({ leader }: { leader: Leader }) {
  const rank    = scoreToRank(leader.final_score);
  const corrupt = corruptionLevel(leader.corruption_level);

  return (
    <Link href={`/leader/${leader.slug}`}>
      <div className="glass rounded-2xl p-5 card-hover cursor-pointer group">
        <div className="flex items-start gap-4 mb-4">
          <div className="relative">
            {leader.photo_url
              ? <img src={leader.photo_url} alt={leader.name} className="w-16 h-16 rounded-full object-cover border-2" style={{ borderColor: rank.color }} />
              : <div className="w-16 h-16 rounded-full flex items-center justify-center" style={{ background: `${rank.color}20`, border: `2px solid ${rank.color}` }}>
                  <User className="w-8 h-8" style={{ color: rank.color }} />
                </div>
            }
            <span className="absolute -bottom-1 -right-1 text-xs px-1.5 py-0.5 rounded-full text-white font-bold" style={{ background: rank.color }}>
              {leader.final_score.toFixed(0)}
            </span>
          </div>
          <div className="flex-1 min-w-0">
            <h3 className="font-bold text-white truncate group-hover:text-orange-400 transition">{leader.name}</h3>
            <p className="text-slate-400 text-sm truncate">{leader.position || 'Politician'}</p>
            <p className="text-slate-500 text-xs mt-1">{(leader as any).party_abbr} • {(leader as any).state_name}</p>
          </div>
        </div>

        <div className="space-y-2">
          <div className="flex justify-between text-xs text-slate-400">
            <span>Promise Score</span>
            <span style={{ color: rank.color }}>{leader.promise_score.toFixed(0)}%</span>
          </div>
          <div className="h-1.5 bg-white/10 rounded-full overflow-hidden">
            <div className="h-full rounded-full transition-all" style={{ width: `${leader.promise_score}%`, background: rank.color }} />
          </div>

          <div className="flex justify-between text-xs text-slate-400">
            <span>Project Delivery</span>
            <span style={{ color: '#3b82f6' }}>{leader.project_score.toFixed(0)}%</span>
          </div>
          <div className="h-1.5 bg-white/10 rounded-full overflow-hidden">
            <div className="h-full rounded-full" style={{ width: `${leader.project_score}%`, background: '#3b82f6' }} />
          </div>
        </div>

        <div className="mt-4 flex items-center justify-between">
          <span className="text-xs px-2 py-1 rounded-full font-medium" style={{ background: `${rank.color}20`, color: rank.color }}>
            {rank.label}
          </span>
          {leader.corruption_level > 30 && (
            <span className="flex items-center gap-1 text-xs" style={{ color: corrupt.color }}>
              <AlertTriangle className="w-3 h-3" />
              {corrupt.label}
            </span>
          )}
        </div>
      </div>
    </Link>
  );
}

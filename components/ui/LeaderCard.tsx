'use client'
import React from 'react'
import Link from 'next/link'
import Image from 'next/image'
import StatusBadge from './StatusBadge'

interface Leader {
  id: number | string
  name: string
  slug: string
  photo?: string
  designation?: string
  party?: { name: string; abbreviation: string; color_code?: string }
  state?: { name: string }
  final_score: number
  rank: 'Excellent' | 'Good' | 'Average' | 'Poor'
  corruption_level: string
  corruption_score: number
  criminal_cases?: number
  promises_total?: number
  promises_completed?: number
}

const RANK_CONFIG = {
  Excellent: { color: 'text-green-400',  bg: 'bg-green-500/20',  border: 'border-green-500/30' },
  Good:      { color: 'text-lime-400',   bg: 'bg-lime-500/20',   border: 'border-lime-500/30' },
  Average:   { color: 'text-yellow-400', bg: 'bg-yellow-500/20', border: 'border-yellow-500/30' },
  Poor:      { color: 'text-red-400',    bg: 'bg-red-500/20',    border: 'border-red-500/30' },
}

export default function LeaderCard({ leader }: { leader: Leader }) {
  const rank = RANK_CONFIG[leader.rank] ?? RANK_CONFIG.Average
  const promisePercent = leader.promises_total
    ? Math.round(((leader.promises_completed ?? 0) / leader.promises_total) * 100)
    : 0

  return (
    <Link href={`/leader/${leader.slug}`} className="block group">
      <div className={`relative rounded-2xl border ${rank.border} bg-white/5 backdrop-blur-sm p-5 hover:bg-white/10 hover:scale-[1.02] transition-all duration-300 hover:shadow-card-hover`}>
        {/* Score badge */}
        <div className={`absolute top-4 right-4 ${rank.bg} ${rank.color} text-xs font-bold px-2 py-1 rounded-lg`}>
          {leader.final_score.toFixed(0)}/100
        </div>

        {/* Photo + basic info */}
        <div className="flex items-center gap-4 mb-4">
          <div className="relative w-16 h-16 rounded-full overflow-hidden border-2 border-white/20 flex-shrink-0">
            {leader.photo ? (
              <Image src={leader.photo} alt={leader.name} fill className="object-cover" />
            ) : (
              <div className="w-full h-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-bold">
                {leader.name.charAt(0)}
              </div>
            )}
          </div>
          <div className="flex-1 min-w-0">
            <h3 className="font-bold text-white truncate text-base group-hover:text-indigo-300 transition-colors">
              {leader.name}
            </h3>
            {leader.designation && (
              <p className="text-xs text-slate-400 truncate">{leader.designation}</p>
            )}
            {leader.party && (
              <span
                className="inline-block text-xs px-2 py-0.5 rounded-full mt-1 font-medium"
                style={{ backgroundColor: (leader.party.color_code ?? '#6366f1') + '33', color: leader.party.color_code ?? '#818cf8' }}
              >
                {leader.party.abbreviation}
              </span>
            )}
          </div>
        </div>

        {/* State */}
        {leader.state && (
          <p className="text-xs text-slate-500 mb-3">📍 {leader.state.name}</p>
        )}

        {/* Rank badge */}
        <div className="flex items-center justify-between mb-3">
          <StatusBadge status={leader.rank} size="sm" />
          {leader.criminal_cases !== undefined && leader.criminal_cases > 0 && (
            <span className="text-xs text-red-400 bg-red-500/10 px-2 py-0.5 rounded-full">
              ⚖️ {leader.criminal_cases} case{leader.criminal_cases > 1 ? 's' : ''}
            </span>
          )}
        </div>

        {/* Promise completion bar */}
        {leader.promises_total !== undefined && leader.promises_total > 0 && (
          <div className="mb-3">
            <div className="flex justify-between text-xs text-slate-400 mb-1">
              <span>Promises</span>
              <span>{leader.promises_completed}/{leader.promises_total} ({promisePercent}%)</span>
            </div>
            <div className="h-1.5 bg-white/10 rounded-full overflow-hidden">
              <div
                className="h-full rounded-full transition-all duration-1000"
                style={{
                  width: `${promisePercent}%`,
                  backgroundColor: promisePercent > 70 ? '#22c55e' : promisePercent > 40 ? '#eab308' : '#ef4444',
                }}
              />
            </div>
          </div>
        )}

        {/* Corruption indicator */}
        <div className="flex items-center gap-2">
          <div className="h-1 flex-1 bg-white/10 rounded-full overflow-hidden">
            <div
              className="h-full rounded-full"
              style={{
                width: `${leader.corruption_score}%`,
                backgroundColor: leader.corruption_score > 60 ? '#dc2626' : leader.corruption_score > 30 ? '#f97316' : '#22c55e',
              }}
            />
          </div>
          <span className="text-xs text-slate-500">Corruption: {leader.corruption_score.toFixed(0)}</span>
        </div>
      </div>
    </Link>
  )
}

'use client'
import React from 'react'

interface CorruptionBadgeProps {
  score: number
  level?: string
  showScore?: boolean
  size?: 'sm' | 'md' | 'lg'
}

function getCorruptionConfig(score: number) {
  if (score <= 10)  return { label: 'Very Clean',          bg: 'bg-green-500/20',  text: 'text-green-400',  icon: '✅', bar: 'bg-green-500' }
  if (score <= 30)  return { label: 'Minor Allegations',   bg: 'bg-yellow-500/20', text: 'text-yellow-400', icon: '⚠️', bar: 'bg-yellow-500' }
  if (score <= 60)  return { label: 'Moderate Risk',       bg: 'bg-orange-500/20', text: 'text-orange-400', icon: '🔶', bar: 'bg-orange-500' }
  return             { label: 'High Corruption Risk',      bg: 'bg-red-500/20',    text: 'text-red-400',    icon: '🚨', bar: 'bg-red-500' }
}

export default function CorruptionBadge({ score, showScore = true, size = 'md' }: CorruptionBadgeProps) {
  const cfg = getCorruptionConfig(score)
  const sizeClasses = { sm: 'text-xs px-2 py-0.5', md: 'text-xs px-3 py-1', lg: 'text-sm px-4 py-2' }
  return (
    <div className="inline-flex flex-col gap-1">
      <span className={`inline-flex items-center gap-1.5 rounded-full font-medium ${cfg.bg} ${cfg.text} ${sizeClasses[size]}`}>
        <span>{cfg.icon}</span>
        <span>{cfg.label}</span>
        {showScore && <span className="opacity-70">({score}/100)</span>}
      </span>
      {size !== 'sm' && (
        <div className="h-1 w-full bg-white/10 rounded-full overflow-hidden">
          <div className={`h-full rounded-full ${cfg.bar} transition-all duration-700`} style={{ width: `${score}%` }} />
        </div>
      )}
    </div>
  )
}

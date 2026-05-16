'use client'
import React from 'react'

type Status = 'Completed' | 'In Progress' | 'Pending' | 'Broken' | 'Delayed' | 'Stalled' | 'Cancelled' | 'Partially Fulfilled' | 'Expired' | 'Approved' | 'Rejected' | 'Alleged' | 'Verified' | 'Fake'

const STATUS_CONFIG: Record<string, { bg: string; text: string; dot: string; label: string }> = {
  'Completed':           { bg: 'bg-green-500/20',  text: 'text-green-400',  dot: 'bg-green-400',  label: 'Completed' },
  'Verified':            { bg: 'bg-green-500/20',  text: 'text-green-400',  dot: 'bg-green-400',  label: 'Verified' },
  'Approved':            { bg: 'bg-green-500/20',  text: 'text-green-400',  dot: 'bg-green-400',  label: 'Approved' },
  'In Progress':         { bg: 'bg-blue-500/20',   text: 'text-blue-400',   dot: 'bg-blue-400',   label: 'In Progress' },
  'Pending':             { bg: 'bg-yellow-500/20', text: 'text-yellow-400', dot: 'bg-yellow-400', label: 'Pending' },
  'Partially Fulfilled': { bg: 'bg-yellow-500/20', text: 'text-yellow-400', dot: 'bg-yellow-400', label: 'Partial' },
  'Broken':              { bg: 'bg-red-500/20',    text: 'text-red-400',    dot: 'bg-red-400',    label: 'Broken' },
  'Fake':                { bg: 'bg-red-500/20',    text: 'text-red-400',    dot: 'bg-red-400',    label: 'Fake' },
  'Rejected':            { bg: 'bg-red-500/20',    text: 'text-red-400',    dot: 'bg-red-400',    label: 'Rejected' },
  'Delayed':             { bg: 'bg-orange-500/20', text: 'text-orange-400', dot: 'bg-orange-400', label: 'Delayed' },
  'Stalled':             { bg: 'bg-orange-500/20', text: 'text-orange-400', dot: 'bg-orange-400', label: 'Stalled' },
  'Cancelled':           { bg: 'bg-gray-500/20',   text: 'text-gray-400',   dot: 'bg-gray-400',   label: 'Cancelled' },
  'Expired':             { bg: 'bg-gray-500/20',   text: 'text-gray-400',   dot: 'bg-gray-400',   label: 'Expired' },
  'Alleged':             { bg: 'bg-orange-500/20', text: 'text-orange-400', dot: 'bg-orange-400', label: 'Alleged' },
}

interface StatusBadgeProps {
  status: string
  size?: 'sm' | 'md' | 'lg'
  showDot?: boolean
}

export default function StatusBadge({ status, size = 'md', showDot = true }: StatusBadgeProps) {
  const cfg = STATUS_CONFIG[status] ?? { bg: 'bg-gray-500/20', text: 'text-gray-400', dot: 'bg-gray-400', label: status }
  const sizeClasses = { sm: 'text-xs px-2 py-0.5', md: 'text-xs px-3 py-1', lg: 'text-sm px-4 py-1.5' }
  return (
    <span className={`inline-flex items-center gap-1.5 rounded-full font-medium ${cfg.bg} ${cfg.text} ${sizeClasses[size]}`}>
      {showDot && <span className={`w-1.5 h-1.5 rounded-full ${cfg.dot} animate-pulse`} />}
      {cfg.label}
    </span>
  )
}

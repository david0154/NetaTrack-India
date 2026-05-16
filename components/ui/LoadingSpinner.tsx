'use client'
import React from 'react'

export default function LoadingSpinner({ size = 'md', text }: { size?: 'sm' | 'md' | 'lg'; text?: string }) {
  const s = { sm: 'w-5 h-5', md: 'w-8 h-8', lg: 'w-12 h-12' }
  return (
    <div className="flex flex-col items-center justify-center gap-3">
      <div className={`${s[size]} border-2 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin`} />
      {text && <p className="text-sm text-slate-400 animate-pulse">{text}</p>}
    </div>
  )
}

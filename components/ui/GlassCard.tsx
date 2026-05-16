'use client'
import React from 'react'

interface GlassCardProps {
  children: React.ReactNode
  className?: string
  hover?: boolean
  glow?: 'brand' | 'green' | 'red' | 'orange' | 'none'
  padding?: 'sm' | 'md' | 'lg' | 'xl'
}

const GLOW_MAP = {
  brand:  'hover:shadow-glow-brand hover:border-indigo-500/50',
  green:  'hover:shadow-glow-green hover:border-green-500/50',
  red:    'hover:shadow-glow-red hover:border-red-500/50',
  orange: 'hover:shadow-glow-orange hover:border-orange-500/50',
  none:   '',
}

const PADDING_MAP = { sm: 'p-3', md: 'p-5', lg: 'p-6', xl: 'p-8' }

export default function GlassCard({ children, className = '', hover = true, glow = 'brand', padding = 'md' }: GlassCardProps) {
  return (
    <div
      className={`
        relative rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm
        ${PADDING_MAP[padding]}
        ${hover ? `transition-all duration-300 hover:bg-white/10 hover:scale-[1.01] ${GLOW_MAP[glow]}` : ''}
        ${className}
      `}
    >
      {children}
    </div>
  )
}

'use client'
import React, { useEffect, useRef, useState } from 'react'

interface StatCardProps {
  title: string
  value: number | string
  subtitle?: string
  icon: React.ReactNode
  color?: 'brand' | 'green' | 'red' | 'orange' | 'blue' | 'yellow'
  trend?: { value: number; label: string }
  animate?: boolean
}

const COLOR_MAP = {
  brand:  { border: 'border-indigo-500/30', glow: 'shadow-glow-brand', icon: 'bg-indigo-500/20 text-indigo-400', text: 'text-indigo-400' },
  green:  { border: 'border-green-500/30',  glow: 'shadow-glow-green',  icon: 'bg-green-500/20 text-green-400',  text: 'text-green-400' },
  red:    { border: 'border-red-500/30',    glow: 'shadow-glow-red',    icon: 'bg-red-500/20 text-red-400',      text: 'text-red-400' },
  orange: { border: 'border-orange-500/30', glow: 'shadow-glow-orange', icon: 'bg-orange-500/20 text-orange-400',text: 'text-orange-400' },
  blue:   { border: 'border-blue-500/30',   glow: '',                   icon: 'bg-blue-500/20 text-blue-400',    text: 'text-blue-400' },
  yellow: { border: 'border-yellow-500/30', glow: '',                   icon: 'bg-yellow-500/20 text-yellow-400',text: 'text-yellow-400' },
}

function useCountUp(target: number, duration = 2000, active = true) {
  const [count, setCount] = useState(0)
  useEffect(() => {
    if (!active) { setCount(target); return }
    let start = 0
    const step = target / (duration / 16)
    const timer = setInterval(() => {
      start += step
      if (start >= target) { setCount(target); clearInterval(timer) }
      else setCount(Math.floor(start))
    }, 16)
    return () => clearInterval(timer)
  }, [target, duration, active])
  return count
}

export default function StatCard({ title, value, subtitle, icon, color = 'brand', trend, animate = true }: StatCardProps) {
  const [visible, setVisible] = useState(false)
  const ref = useRef<HTMLDivElement>(null)
  const numericValue = typeof value === 'number' ? value : parseInt(String(value).replace(/,/g, '')) || 0
  const count = useCountUp(numericValue, 2000, visible && animate)
  const displayValue = typeof value === 'string' && isNaN(numericValue) ? value
    : animate && visible ? count.toLocaleString('en-IN') : numericValue.toLocaleString('en-IN')
  const colors = COLOR_MAP[color]

  useEffect(() => {
    const obs = new IntersectionObserver(([e]) => e.isIntersecting && setVisible(true), { threshold: 0.2 })
    if (ref.current) obs.observe(ref.current)
    return () => obs.disconnect()
  }, [])

  return (
    <div
      ref={ref}
      className={`relative group rounded-2xl p-6 border ${colors.border} bg-white/5 backdrop-blur-sm hover:bg-white/10 transition-all duration-300 hover:scale-105 hover:${colors.glow} cursor-default`}
    >
      {/* Background glow */}
      <div className="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-gradient-to-br from-white/5 to-transparent" />
      <div className="relative z-10 flex items-start justify-between">
        <div className="flex-1">
          <p className="text-sm text-slate-400 font-medium mb-1">{title}</p>
          <p className={`text-3xl font-bold ${colors.text} tabular-nums`}>{displayValue}</p>
          {subtitle && <p className="text-xs text-slate-500 mt-1">{subtitle}</p>}
          {trend && (
            <p className={`text-xs mt-2 font-medium ${trend.value >= 0 ? 'text-green-400' : 'text-red-400'}`}>
              {trend.value >= 0 ? '↑' : '↓'} {Math.abs(trend.value)}% {trend.label}
            </p>
          )}
        </div>
        <div className={`p-3 rounded-xl ${colors.icon} text-2xl`}>{icon}</div>
      </div>
    </div>
  )
}

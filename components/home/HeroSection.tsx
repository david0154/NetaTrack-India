'use client'
import React, { useState } from 'react'
import Link from 'next/link'
import { useRouter } from 'next/navigation'

export default function HeroSection() {
  const [query, setQuery] = useState('')
  const router = useRouter()

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault()
    if (query.trim()) router.push(`/search?q=${encodeURIComponent(query.trim())}`)
  }

  const QUICK_LINKS = [
    { label: 'PM Modi',        href: '/leader/narendra-modi' },
    { label: 'Delhi Budget',   href: '/projects?state=delhi&category=budget' },
    { label: 'Broken Promises',href: '/promises?status=Broken' },
    { label: 'Top Corruption', href: '/corruption' },
  ]

  return (
    <section className="relative min-h-screen flex items-center justify-center overflow-hidden bg-hero-gradient">
      {/* Animated background particles */}
      <div className="absolute inset-0 overflow-hidden">
        {Array.from({ length: 20 }).map((_, i) => (
          <div
            key={i}
            className="absolute rounded-full opacity-10 animate-float"
            style={{
              width:  Math.random() * 4 + 2 + 'px',
              height: Math.random() * 4 + 2 + 'px',
              left:   Math.random() * 100 + '%',
              top:    Math.random() * 100 + '%',
              background: i % 3 === 0 ? '#6366f1' : i % 3 === 1 ? '#22c55e' : '#ef4444',
              animationDelay:    Math.random() * 6 + 's',
              animationDuration: Math.random() * 4 + 4 + 's',
            }}
          />
        ))}
      </div>

      {/* Gradient orbs */}
      <div className="absolute top-1/4 left-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl animate-pulse-slow" />
      <div className="absolute bottom-1/4 right-1/4 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl animate-pulse-slow" style={{ animationDelay: '2s' }} />

      <div className="relative z-10 text-center px-4 max-w-5xl mx-auto pt-20">
        {/* Live badge */}
        <div className="inline-flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/30 rounded-full px-4 py-2 mb-6">
          <span className="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
          <span className="text-sm text-indigo-300 font-medium">Live Political Transparency Platform</span>
        </div>

        {/* Headline */}
        <h1 className="text-5xl md:text-7xl font-black text-white mb-4 leading-tight">
          Track India&apos;s{' '}
          <span className="bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
            Political
          </span>
          <br />
          <span className="bg-gradient-to-r from-green-400 to-emerald-400 bg-clip-text text-transparent">
            Accountability
          </span>
        </h1>

        <p className="text-lg md:text-xl text-slate-400 mb-8 max-w-2xl mx-auto leading-relaxed">
          AI-powered platform tracking promises, projects, corruption &amp; ground reality across all 28 states &amp; 8 union territories of India.
        </p>

        {/* Search Bar */}
        <form onSubmit={handleSearch} className="max-w-2xl mx-auto mb-8">
          <div className="flex items-center gap-2 bg-white/10 backdrop-blur border border-white/20 rounded-2xl p-2 focus-within:border-indigo-500/60 focus-within:bg-white/15 transition-all">
            <span className="pl-3 text-slate-400">🔍</span>
            <input
              type="text"
              value={query}
              onChange={e => setQuery(e.target.value)}
              placeholder="Search leaders, promises, projects, states..."
              className="flex-1 bg-transparent text-white placeholder-slate-500 outline-none text-sm px-2 py-2"
            />
            <button
              type="submit"
              className="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-semibold transition-all hover:shadow-glow-brand"
            >
              Search
            </button>
          </div>
        </form>

        {/* Quick search tags */}
        <div className="flex flex-wrap justify-center gap-2 mb-10">
          {QUICK_LINKS.map(({ label, href }) => (
            <Link
              key={label}
              href={href}
              className="px-3 py-1.5 bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 rounded-full text-xs text-slate-400 hover:text-white transition-all"
            >
              {label}
            </Link>
          ))}
        </div>

        {/* CTA Buttons */}
        <div className="flex flex-col sm:flex-row justify-center gap-4">
          <Link
            href="/leaders"
            className="px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-2xl transition-all hover:scale-105 hover:shadow-glow-brand text-sm"
          >
            🔍 Explore Leaders
          </Link>
          <Link
            href="/submit"
            className="px-8 py-4 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold rounded-2xl transition-all hover:scale-105 text-sm"
          >
            📤 Submit a Report
          </Link>
          <Link
            href="/corruption"
            className="px-8 py-4 bg-red-500/20 hover:bg-red-500/30 border border-red-500/30 text-red-300 font-bold rounded-2xl transition-all hover:scale-105 hover:shadow-glow-red text-sm"
          >
            🚨 Corruption Cases
          </Link>
        </div>
      </div>
    </section>
  )
}

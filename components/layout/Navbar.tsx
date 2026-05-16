'use client'
import React, { useState, useEffect } from 'react'
import Link from 'next/link'
import { usePathname } from 'next/navigation'

const NAV_LINKS = [
  { href: '/',           label: 'Home',       icon: '🏠' },
  { href: '/leaders',    label: 'Leaders',    icon: '👤' },
  { href: '/promises',   label: 'Promises',   icon: '📋' },
  { href: '/projects',   label: 'Projects',   icon: '🏗️' },
  { href: '/corruption', label: 'Corruption', icon: '🚨' },
  { href: '/submit',     label: 'Report',     icon: '📤' },
]

export default function Navbar() {
  const pathname   = usePathname()
  const [scrolled, setScrolled] = useState(false)
  const [mobileOpen, setMobileOpen] = useState(false)

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 20)
    window.addEventListener('scroll', onScroll)
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  return (
    <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
      scrolled ? 'bg-slate-900/95 backdrop-blur-lg border-b border-white/10 shadow-glass' : 'bg-transparent'
    }`}>
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Link href="/" className="flex items-center gap-2 group">
            <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-glow-brand group-hover:scale-110 transition-transform">
              NT
            </div>
            <div>
              <span className="font-bold text-white text-sm">NetaTrack</span>
              <span className="text-indigo-400 text-sm font-bold"> India</span>
            </div>
          </Link>

          {/* Desktop Links */}
          <div className="hidden md:flex items-center gap-1">
            {NAV_LINKS.map(({ href, label, icon }) => (
              <Link
                key={href}
                href={href}
                className={`px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center gap-1.5 ${
                  pathname === href
                    ? 'bg-indigo-500/20 text-indigo-300'
                    : 'text-slate-400 hover:text-white hover:bg-white/10'
                }`}
              >
                <span className="text-base">{icon}</span>
                {label}
              </Link>
            ))}
          </div>

          {/* Right Actions */}
          <div className="hidden md:flex items-center gap-3">
            <Link
              href="/submit"
              className="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold rounded-xl transition-all duration-200 hover:shadow-glow-brand"
            >
              + Submit Report
            </Link>
          </div>

          {/* Mobile hamburger */}
          <button
            className="md:hidden p-2 text-slate-400 hover:text-white"
            onClick={() => setMobileOpen(!mobileOpen)}
            aria-label="Toggle menu"
          >
            <span className="text-xl">{mobileOpen ? '✕' : '☰'}</span>
          </button>
        </div>

        {/* Mobile Menu */}
        {mobileOpen && (
          <div className="md:hidden pb-4 border-t border-white/10 pt-4 space-y-1">
            {NAV_LINKS.map(({ href, label, icon }) => (
              <Link
                key={href}
                href={href}
                onClick={() => setMobileOpen(false)}
                className={`flex items-center gap-2 px-4 py-2 rounded-lg text-sm ${
                  pathname === href ? 'bg-indigo-500/20 text-indigo-300' : 'text-slate-400 hover:text-white hover:bg-white/10'
                }`}
              >
                {icon} {label}
              </Link>
            ))}
            <Link href="/submit" onClick={() => setMobileOpen(false)} className="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-indigo-300 bg-indigo-500/20">
              📤 Submit Report
            </Link>
          </div>
        )}
      </div>
    </nav>
  )
}

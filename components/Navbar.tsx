'use client';
import Link from 'next/link';
import { useState } from 'react';
import { Shield, Menu, X, Search, Users, FileText, Building2, AlertTriangle, MapPin, Flag, Send } from 'lucide-react';

const navLinks = [
  { href: '/leaders',    label: 'Leaders',    icon: Users },
  { href: '/promises',   label: 'Promises',   icon: FileText },
  { href: '/projects',   label: 'Projects',   icon: Building2 },
  { href: '/corruption', label: 'Corruption', icon: AlertTriangle },
  { href: '/states',     label: 'States',     icon: MapPin },
  { href: '/parties',    label: 'Parties',    icon: Flag },
];

export default function Navbar() {
  const [open, setOpen] = useState(false);

  return (
    <nav className="glass border-b border-slate-800 sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
        {/* Logo */}
        <Link href="/" className="flex items-center gap-2 font-black text-xl">
          <div className="gradient-bg p-1.5 rounded-lg">
            <Shield className="w-5 h-5 text-white" />
          </div>
          <span className="gradient-text">NetaTrack</span>
          <span className="text-slate-400 text-sm font-normal hidden sm:block">India</span>
        </Link>

        {/* Desktop Nav */}
        <div className="hidden lg:flex items-center gap-1">
          {navLinks.map(l => (
            <Link key={l.href} href={l.href}
              className="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm text-slate-300 hover:text-white hover:bg-white/10 transition">
              <l.icon className="w-4 h-4" />{l.label}
            </Link>
          ))}
        </div>

        {/* Right Actions */}
        <div className="flex items-center gap-2">
          <Link href="/search" className="p-2 glass rounded-xl text-slate-300 hover:text-white transition">
            <Search className="w-5 h-5" />
          </Link>
          <Link href="/submit" className="hidden sm:flex items-center gap-1.5 gradient-bg px-4 py-2 rounded-xl text-sm font-bold text-white hover:opacity-90 transition">
            <Send className="w-4 h-4" />Submit
          </Link>
          <Link href="/auth/login" className="hidden sm:block glass px-4 py-2 rounded-xl text-sm font-semibold text-slate-300 hover:text-white transition">
            Login
          </Link>
          <button onClick={() => setOpen(o => !o)} className="lg:hidden p-2 glass rounded-xl">
            {open ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </button>
        </div>
      </div>

      {/* Mobile Menu */}
      {open && (
        <div className="lg:hidden border-t border-slate-800 px-4 py-3 space-y-1">
          {navLinks.map(l => (
            <Link key={l.href} href={l.href} onClick={() => setOpen(false)}
              className="flex items-center gap-2 px-3 py-2.5 rounded-xl text-slate-300 hover:text-white hover:bg-white/10 transition">
              <l.icon className="w-4 h-4" />{l.label}
            </Link>
          ))}
          <div className="pt-2 grid grid-cols-2 gap-2">
            <Link href="/submit" onClick={() => setOpen(false)}
              className="flex items-center justify-center gap-1 gradient-bg py-2 rounded-xl text-sm font-bold text-white">
              <Send className="w-4 h-4" />Submit
            </Link>
            <Link href="/auth/login" onClick={() => setOpen(false)}
              className="flex items-center justify-center glass py-2 rounded-xl text-sm font-semibold text-slate-300">
              Login
            </Link>
          </div>
        </div>
      )}
    </nav>
  );
}

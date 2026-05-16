'use client';
import { useState } from 'react';
import Link from 'next/link';
import { Menu, X, Search, Shield } from 'lucide-react';

const links = [
  { href: '/',          label: 'Home' },
  { href: '/leaders',   label: 'Leaders' },
  { href: '/promises',  label: 'Promises' },
  { href: '/projects',  label: 'Projects' },
  { href: '/corruption',label: 'Corruption' },
  { href: '/submit',    label: 'Submit Report' },
];

export default function Navbar() {
  const [open, setOpen] = useState(false);
  return (
    <nav className="sticky top-0 z-50 glass border-b border-white/10">
      <div className="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
        <Link href="/" className="flex items-center gap-2 font-bold text-xl">
          <Shield className="w-7 h-7 text-orange-500" />
          <span className="gradient-text">NetaTrack</span>
          <span className="text-slate-400 text-sm font-normal hidden sm:block">India</span>
        </Link>

        <div className="hidden md:flex items-center gap-1">
          {links.map(l => (
            <Link key={l.href} href={l.href}
              className="px-3 py-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 transition text-sm">
              {l.label}
            </Link>
          ))}
        </div>

        <div className="flex items-center gap-3">
          <Link href="/search"
            className="hidden sm:flex items-center gap-2 px-3 py-1.5 glass rounded-full text-slate-400 hover:text-white transition text-sm">
            <Search className="w-4 h-4" /> Search
          </Link>
          <button onClick={() => setOpen(!open)} className="md:hidden text-slate-300">
            {open ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
          </button>
        </div>
      </div>

      {open && (
        <div className="md:hidden border-t border-white/10 bg-[#0a0a0f] px-4 py-3 flex flex-col gap-2">
          {links.map(l => (
            <Link key={l.href} href={l.href} onClick={() => setOpen(false)}
              className="py-2 px-3 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 transition">
              {l.label}
            </Link>
          ))}
        </div>
      )}
    </nav>
  );
}

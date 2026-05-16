import Link from 'next/link';
import { Shield } from 'lucide-react';

export default function Footer() {
  return (
    <footer className="border-t border-white/10 mt-20 py-12 px-4">
      <div className="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <div className="flex items-center gap-2 mb-3">
            <Shield className="w-6 h-6 text-orange-500" />
            <span className="font-bold text-lg gradient-text">NetaTrack India</span>
          </div>
          <p className="text-slate-400 text-sm">AI-powered political transparency platform tracking promises, projects, and accountability across all Indian states.</p>
        </div>
        <div>
          <h4 className="font-semibold mb-3 text-slate-200">Track</h4>
          <div className="flex flex-col gap-2 text-sm text-slate-400">
            <Link href="/leaders" className="hover:text-orange-400 transition">Political Leaders</Link>
            <Link href="/promises" className="hover:text-orange-400 transition">Promises</Link>
            <Link href="/projects" className="hover:text-orange-400 transition">Projects</Link>
            <Link href="/corruption" className="hover:text-orange-400 transition">Corruption Cases</Link>
          </div>
        </div>
        <div>
          <h4 className="font-semibold mb-3 text-slate-200">Participate</h4>
          <div className="flex flex-col gap-2 text-sm text-slate-400">
            <Link href="/submit" className="hover:text-orange-400 transition">Submit Report</Link>
            <Link href="/auth/login" className="hover:text-orange-400 transition">Login</Link>
            <Link href="/auth/register" className="hover:text-orange-400 transition">Register</Link>
          </div>
        </div>
        <div>
          <h4 className="font-semibold mb-3 text-slate-200">Platform</h4>
          <div className="flex flex-col gap-2 text-sm text-slate-400">
            <span>Data from public government sources</span>
            <span>AI-verified content</span>
            <span>Non-partisan reporting</span>
          </div>
        </div>
      </div>
      <div className="max-w-7xl mx-auto mt-8 pt-6 border-t border-white/10 text-center text-slate-500 text-sm">
        © {new Date().getFullYear()} NetaTrack India. Built for public accountability. Not affiliated with any political party.
      </div>
    </footer>
  );
}

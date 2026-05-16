'use client'
import React from 'react'
import Link from 'next/link'

const FOOTER_LINKS = {
  Platform: [
    { href: '/leaders',    label: 'Leaders' },
    { href: '/promises',   label: 'Promises' },
    { href: '/projects',   label: 'Projects' },
    { href: '/corruption', label: 'Corruption Cases' },
    { href: '/states',     label: 'States' },
  ],
  'Get Involved': [
    { href: '/submit',        label: 'Submit Report' },
    { href: '/submit/rti',    label: 'Submit RTI' },
    { href: '/submit/complaint', label: 'File Complaint' },
  ],
  Legal: [
    { href: '/privacy',     label: 'Privacy Policy' },
    { href: '/terms',       label: 'Terms of Use' },
    { href: '/disclaimer',  label: 'Disclaimer' },
    { href: '/about',       label: 'About' },
  ],
}

export default function Footer() {
  return (
    <footer className="bg-slate-900/80 border-t border-white/10 backdrop-blur-sm mt-20">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
          {/* Brand */}
          <div>
            <div className="flex items-center gap-2 mb-4">
              <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                NT
              </div>
              <span className="font-bold text-white">NetaTrack <span className="text-indigo-400">India</span></span>
            </div>
            <p className="text-sm text-slate-400 leading-relaxed">
              AI-powered political transparency platform. Tracking promises, projects &amp; accountability across all Indian states.
            </p>
            <div className="flex gap-3 mt-4">
              {['𝕏', 'f', 'in', '▶'].map((s, i) => (
                <a key={i} href="#" className="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/20 transition-colors text-sm">{s}</a>
              ))}
            </div>
          </div>
          {/* Link groups */}
          {Object.entries(FOOTER_LINKS).map(([group, links]) => (
            <div key={group}>
              <h3 className="text-white font-semibold text-sm mb-4">{group}</h3>
              <ul className="space-y-2">
                {links.map(({ href, label }) => (
                  <li key={href}>
                    <Link href={href} className="text-slate-400 hover:text-white text-sm transition-colors">
                      {label}
                    </Link>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>
        <div className="border-t border-white/10 pt-6 flex flex-col md:flex-row items-center justify-between gap-4">
          <p className="text-xs text-slate-500">
            © {new Date().getFullYear()} NetaTrack India. Built for transparency &amp; public accountability.
          </p>
          <p className="text-xs text-slate-600">
            Not affiliated with any political party. Data for informational purposes only.
          </p>
        </div>
      </div>
    </footer>
  )
}

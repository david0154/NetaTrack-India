'use client'
import React from 'react'
import StatCard from '../ui/StatCard'

interface Stats {
  promises_tracked: number
  projects_monitored: number
  delayed_projects: number
  corruption_allegations: number
  fake_claims_detected: number
  verified_reports: number
  public_submissions: number
  leaders_tracked: number
}

export default function LiveStatsSection({ stats }: { stats: Stats }) {
  return (
    <section className="py-16 px-4">
      <div className="max-w-7xl mx-auto">
        <div className="text-center mb-10">
          <h2 className="text-3xl font-bold text-white mb-2">Live Platform Statistics</h2>
          <p className="text-slate-400">Real-time data updated automatically from government sources</p>
          <div className="flex items-center justify-center gap-2 mt-3">
            <span className="w-2 h-2 rounded-full bg-green-400 animate-pulse" />
            <span className="text-xs text-green-400 font-medium">Live Data</span>
          </div>
        </div>
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          <StatCard title="Promises Tracked"      value={stats.promises_tracked}      icon="📋" color="brand"  />
          <StatCard title="Projects Monitored"    value={stats.projects_monitored}    icon="🏗️" color="blue"   />
          <StatCard title="Delayed Projects"      value={stats.delayed_projects}      icon="⏰" color="orange" />
          <StatCard title="Corruption Cases"      value={stats.corruption_allegations} icon="🚨" color="red"    />
          <StatCard title="Fake Claims Detected"  value={stats.fake_claims_detected}  icon="🤖" color="red"    />
          <StatCard title="Verified Reports"      value={stats.verified_reports}      icon="✅" color="green"  />
          <StatCard title="Public Submissions"    value={stats.public_submissions}    icon="📤" color="yellow" />
          <StatCard title="Leaders Tracked"       value={stats.leaders_tracked}       icon="👤" color="brand"  />
        </div>
      </div>
    </section>
  )
}

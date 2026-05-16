'use client';
import { useEffect, useRef, useState } from 'react';
import { LucideIcon } from 'lucide-react';

interface Props {
  label: string;
  value: number;
  icon: LucideIcon;
  color?: string;
  suffix?: string;
}

export default function StatCard({ label, value, icon: Icon, color = '#f97316', suffix = '' }: Props) {
  const [display, setDisplay] = useState(0);
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const obs = new IntersectionObserver(([entry]) => {
      if (entry.isIntersecting) {
        let start = 0;
        const step = Math.ceil(value / 60);
        const timer = setInterval(() => {
          start += step;
          if (start >= value) { setDisplay(value); clearInterval(timer); }
          else setDisplay(start);
        }, 16);
        obs.disconnect();
      }
    }, { threshold: 0.3 });
    if (ref.current) obs.observe(ref.current);
    return () => obs.disconnect();
  }, [value]);

  return (
    <div ref={ref} className="glass rounded-2xl p-6 card-hover" style={{ borderColor: `${color}30` }}>
      <div className="flex items-start justify-between mb-4">
        <div className="p-3 rounded-xl" style={{ background: `${color}20` }}>
          <Icon className="w-6 h-6" style={{ color }} />
        </div>
        <div className="text-right">
          <div className="text-3xl font-bold text-white count-up">
            {display.toLocaleString('en-IN')}{suffix}
          </div>
        </div>
      </div>
      <p className="text-slate-400 text-sm font-medium">{label}</p>
    </div>
  );
}

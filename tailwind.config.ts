import type { Config } from 'tailwindcss'

const config: Config = {
  content: [
    './app/**/*.{js,ts,jsx,tsx,mdx}',
    './components/**/*.{js,ts,jsx,tsx,mdx}',
    './lib/**/*.{js,ts,jsx,tsx,mdx}',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        // NetaTrack Brand
        brand: {
          50:  '#eef2ff',
          100: '#e0e7ff',
          200: '#c7d2fe',
          300: '#a5b4fc',
          400: '#818cf8',
          500: '#6366f1',
          600: '#4f46e5',
          700: '#4338ca',
          800: '#3730a3',
          900: '#312e81',
        },
        // Status Colors
        verified:  { DEFAULT: '#22c55e', dark: '#16a34a', light: '#dcfce7' },
        pending:   { DEFAULT: '#eab308', dark: '#ca8a04', light: '#fef9c3' },
        fake:      { DEFAULT: '#ef4444', dark: '#dc2626', light: '#fee2e2' },
        delayed:   { DEFAULT: '#f97316', dark: '#ea580c', light: '#ffedd5' },
        progress:  { DEFAULT: '#3b82f6', dark: '#2563eb', light: '#dbeafe' },
        // Corruption Levels
        clean:     { DEFAULT: '#10b981', light: '#d1fae5' },
        minor:     { DEFAULT: '#f59e0b', light: '#fef3c7' },
        moderate:  { DEFAULT: '#f97316', light: '#ffedd5' },
        highrisk:  { DEFAULT: '#dc2626', light: '#fee2e2' },
        // Rank Colors
        excellent: '#22c55e',
        good:      '#84cc16',
        average:   '#eab308',
        poor:      '#ef4444',
        // Dark UI
        dark: {
          50:  '#f8fafc',
          100: '#0f172a',
          200: '#1e293b',
          300: '#334155',
          400: '#475569',
          500: '#64748b',
          600: '#94a3b8',
          700: '#cbd5e1',
          800: '#e2e8f0',
          900: '#f1f5f9',
        },
      },
      fontFamily: {
        sans: ['Inter', 'Noto Sans', 'sans-serif'],
        hindi: ['Noto Sans Devanagari', 'sans-serif'],
        mono: ['JetBrains Mono', 'monospace'],
      },
      backgroundImage: {
        'gradient-radial':   'radial-gradient(var(--tw-gradient-stops))',
        'gradient-conic':    'conic-gradient(from 180deg at 50% 50%, var(--tw-gradient-stops))',
        'glass-gradient':    'linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%)',
        'hero-gradient':     'linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%)',
        'corruption-gradient':'linear-gradient(135deg, #7f1d1d 0%, #1e1b4b 100%)',
      },
      boxShadow: {
        'glass':        '0 8px 32px 0 rgba(31,38,135,0.37)',
        'glass-lg':     '0 25px 50px -12px rgba(0,0,0,0.7)',
        'glow-brand':   '0 0 20px rgba(99,102,241,0.4)',
        'glow-green':   '0 0 20px rgba(34,197,94,0.4)',
        'glow-red':     '0 0 20px rgba(239,68,68,0.4)',
        'glow-orange':  '0 0 20px rgba(249,115,22,0.4)',
        'card':         '0 4px 6px -1px rgba(0,0,0,0.4), 0 2px 4px -2px rgba(0,0,0,0.4)',
        'card-hover':   '0 20px 25px -5px rgba(0,0,0,0.6), 0 8px 10px -6px rgba(0,0,0,0.5)',
      },
      backdropBlur: {
        xs: '2px',
      },
      animation: {
        'float':          'float 6s ease-in-out infinite',
        'pulse-slow':     'pulse 4s cubic-bezier(0.4,0,0.6,1) infinite',
        'glow':           'glow 2s ease-in-out infinite alternate',
        'counter':        'counter 2s ease-out forwards',
        'slide-in-left':  'slideInLeft 0.5s ease-out',
        'slide-in-right': 'slideInRight 0.5s ease-out',
        'fade-in-up':     'fadeInUp 0.6s ease-out',
        'spin-slow':      'spin 8s linear infinite',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0px)' },
          '50%':      { transform: 'translateY(-20px)' },
        },
        glow: {
          from: { boxShadow: '0 0 10px rgba(99,102,241,0.3)' },
          to:   { boxShadow: '0 0 30px rgba(99,102,241,0.8), 0 0 60px rgba(99,102,241,0.3)' },
        },
        slideInLeft: {
          from: { transform: 'translateX(-100%)', opacity: '0' },
          to:   { transform: 'translateX(0)',      opacity: '1' },
        },
        slideInRight: {
          from: { transform: 'translateX(100%)', opacity: '0' },
          to:   { transform: 'translateX(0)',     opacity: '1' },
        },
        fadeInUp: {
          from: { transform: 'translateY(30px)', opacity: '0' },
          to:   { transform: 'translateY(0)',    opacity: '1' },
        },
      },
      borderRadius: {
        '4xl': '2rem',
        '5xl': '2.5rem',
      },
    },
  },
  plugins: [],
}

export default config

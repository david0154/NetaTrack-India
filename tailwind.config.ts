import type { Config } from 'tailwindcss';

const config: Config = {
  content: ['./app/**/*.{ts,tsx}', './components/**/*.{ts,tsx}', './admin/**/*.{ts,tsx}'],
  theme: { extend: {} },
  plugins: []
};

export default config;

import { type ClassValue, clsx } from 'clsx';

export function cn(...inputs: ClassValue[]) {
  return clsx(inputs);
}

export function slugify(text: string): string {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim();
}

export function formatCrore(amount: number): string {
  if (amount >= 100) return `₹${(amount / 100).toFixed(1)}L Cr`;
  return `₹${amount.toFixed(1)} Cr`;
}

export function scoreToRank(score: number): { label: string; color: string } {
  if (score >= 90) return { label: 'Excellent', color: '#22c55e' };
  if (score >= 75) return { label: 'Good', color: '#3b82f6' };
  if (score >= 50) return { label: 'Average', color: '#f59e0b' };
  return { label: 'Poor', color: '#ef4444' };
}

export function corruptionLevel(score: number): { label: string; color: string } {
  if (score <= 10)  return { label: 'Very Clean', color: '#22c55e' };
  if (score <= 30)  return { label: 'Minor Allegations', color: '#84cc16' };
  if (score <= 60)  return { label: 'Moderate', color: '#f59e0b' };
  return { label: 'High Risk', color: '#ef4444' };
}

export function paginateQuery(page: number, limit: number) {
  const offset = (page - 1) * limit;
  return { limit, offset };
}

export function getPaginationMeta(total: number, page: number, limit: number) {
  return {
    page,
    limit,
    total,
    pages: Math.ceil(total / limit),
  };
}

export function sanitize(str: string): string {
  return str.replace(/[<>"']/g, (c) => ({ '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c] || c));
}

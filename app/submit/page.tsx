'use client';
import { useState } from 'react';
import toast from 'react-hot-toast';
import { Send, FileText, AlertTriangle, Camera } from 'lucide-react';

const TYPES = [
  { value: 'corruption',        label: 'Corruption Report', icon: AlertTriangle, color: '#ef4444' },
  { value: 'fake_claim',        label: 'Fake Claim',        icon: FileText,      color: '#f59e0b' },
  { value: 'project_delay',     label: 'Project Delay',     icon: AlertTriangle, color: '#f97316' },
  { value: 'infrastructure',    label: 'Infrastructure',    icon: Camera,        color: '#8b5cf6' },
  { value: 'general',           label: 'General Report',    icon: Send,          color: '#3b82f6' },
];

export default function SubmitPage() {
  const [form, setForm] = useState({
    title: '', leader_name: '', state: '', description: '',
    submission_type: 'general', source_link: '',
  });
  const [loading, setLoading] = useState(false);

  function handleChange(e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) {
    setForm(f => ({ ...f, [e.target.name]: e.target.value }));
  }

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    if (!form.title || !form.leader_name || !form.state || !form.description) {
      toast.error('Please fill all required fields');
      return;
    }
    setLoading(true);
    try {
      const res = await fetch('/api/submit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form),
      });
      const json = await res.json();
      if (json.success) {
        toast.success('Report submitted! Our team will review it.');
        setForm({ title: '', leader_name: '', state: '', description: '', submission_type: 'general', source_link: '' });
      } else {
        toast.error(json.error || 'Submission failed');
      }
    } catch {
      toast.error('Network error. Please try again.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="max-w-3xl mx-auto px-4 py-10">
      <div className="mb-10">
        <h1 className="text-4xl font-black mb-2">Submit a <span className="gradient-text">Report</span></h1>
        <p className="text-slate-400">Help us hold leaders accountable. Submit corruption reports, fake claims, project delays, or infrastructure issues. All submissions are AI-verified before publishing.</p>
      </div>

      {/* Report Type Selection */}
      <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-8">
        {TYPES.map(t => {
          const Icon = t.icon;
          const active = form.submission_type === t.value;
          return (
            <button key={t.value} type="button" onClick={() => setForm(f => ({ ...f, submission_type: t.value }))}
              className={`glass rounded-2xl p-4 text-left transition border-2 ${
                active ? 'border-orange-500 bg-orange-500/10' : 'border-transparent hover:bg-white/5'
              }`}>
              <Icon className="w-5 h-5 mb-2" style={{ color: t.color }} />
              <p className="text-sm font-semibold text-white">{t.label}</p>
            </button>
          );
        })}
      </div>

      <form onSubmit={handleSubmit} className="glass rounded-3xl p-6 space-y-4">
        <div>
          <label className="block text-sm font-medium text-slate-300 mb-1">Report Title *</label>
          <input name="title" value={form.title} onChange={handleChange} required
            className="w-full bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 transition"
            placeholder="Brief title of the issue" />
        </div>
        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-slate-300 mb-1">Leader Name *</label>
            <input name="leader_name" value={form.leader_name} onChange={handleChange} required
              className="w-full bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 transition"
              placeholder="Politician's name" />
          </div>
          <div>
            <label className="block text-sm font-medium text-slate-300 mb-1">State *</label>
            <input name="state" value={form.state} onChange={handleChange} required
              className="w-full bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 transition"
              placeholder="State name" />
          </div>
        </div>
        <div>
          <label className="block text-sm font-medium text-slate-300 mb-1">Description *</label>
          <textarea name="description" value={form.description} onChange={handleChange} required rows={5}
            className="w-full bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 transition resize-none"
            placeholder="Describe the issue in detail. Include dates, amounts, locations if known." />
        </div>
        <div>
          <label className="block text-sm font-medium text-slate-300 mb-1">Source / Evidence URL</label>
          <input name="source_link" value={form.source_link} onChange={handleChange} type="url"
            className="w-full bg-slate-800/60 border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-orange-500 transition"
            placeholder="https://news-article-or-document.com" />
        </div>
        <button type="submit" disabled={loading}
          className="w-full gradient-bg py-4 rounded-2xl font-bold text-white text-lg hover:opacity-90 transition disabled:opacity-50 flex items-center justify-center gap-2">
          <Send className="w-5 h-5" />
          {loading ? 'Submitting...' : 'Submit Report'}
        </button>
        <p className="text-center text-xs text-slate-500">Your report will go through AI verification and admin review before being published publicly.</p>
      </form>
    </div>
  );
}

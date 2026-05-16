import Link from 'next/link';

export default function Header() {
  return (
    <header className="border-b border-slate-800 bg-slate-950/90 sticky top-0 backdrop-blur z-50">
      <div className="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <Link href="/" className="font-bold text-xl">NetaTrack India</Link>
        <nav className="flex gap-4 text-sm text-slate-300">
          <Link href="/promises">Promises</Link>
          <Link href="/leaders">Leaders</Link>
          <Link href="/submit">Submit Report</Link>
          <Link href="/admin">Admin</Link>
        </nav>
      </div>
    </header>
  );
}

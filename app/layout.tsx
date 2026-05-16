import type { Metadata } from 'next';
import './globals.css';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';

export const metadata: Metadata = {
  title: 'NetaTrack India — Political Transparency Platform',
  description: 'Track political promises, government projects, budgets, and ground reality across all Indian states.',
  keywords: 'India politics, politician tracker, promise tracker, corruption tracker, India government',
  openGraph: {
    title: 'NetaTrack India',
    description: 'AI-powered political transparency platform for India',
    type: 'website',
  },
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en" className="dark">
      <body>
        <Navbar />
        <main className="min-h-screen">{children}</main>
        <Footer />
      </body>
    </html>
  );
}

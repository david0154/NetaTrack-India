import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import './globals.css';
import Navbar from '@/components/Navbar';
import Footer from '@/components/Footer';
import { Toaster } from 'react-hot-toast';

const inter = Inter({ subsets: ['latin'] });

export const metadata: Metadata = {
  title: 'NetaTrack India — Political Transparency & Public Accountability',
  description: 'AI-powered platform tracking political promises, government projects, budgets, and corruption allegations across all Indian states.',
  keywords: 'India politics, political transparency, leader accountability, corruption tracker, government projects, election promises',
  openGraph: {
    title: 'NetaTrack India',
    description: 'Track political promises, government projects, and corruption across India.',
    url: 'https://netatrack.in',
    siteName: 'NetaTrack India',
    locale: 'en_IN',
    type: 'website',
  },
  twitter: {
    card: 'summary_large_image',
    title: 'NetaTrack India',
    description: 'AI-powered political transparency platform for India.',
  },
  robots: { index: true, follow: true },
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <body className={`${inter.className} bg-slate-950 text-white min-h-screen`}>
        <Navbar />
        <main>{children}</main>
        <Footer />
        <Toaster
          position="top-right"
          toastOptions={{
            style: { background: '#1e293b', color: '#f8fafc', border: '1px solid #334155' },
            success: { iconTheme: { primary: '#22c55e', secondary: '#fff' } },
            error:   { iconTheme: { primary: '#ef4444', secondary: '#fff' } },
          }}
        />
      </body>
    </html>
  );
}

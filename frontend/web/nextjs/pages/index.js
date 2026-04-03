import Head from 'next/head';
import Dashboard from '../components/Dashboard';

export default function Home() {
  return (
    <div>
      <Head>
        <title>Orchard.ai Dashboard</title>
        <meta name="description" content="AI-driven governance and compliance dashboard" />
        <link rel="icon" href="/favicon.ico" />
      </Head>

      <main className="dashboard-container">
        <header className="header">
          <div className="logo">ORCHARD.AI (v0.1-alpha)</div>
          <div className="badge badge-success">Live: Sprint 1 (Day 15)</div>
        </header>
        <Dashboard />
      </main>
    </div>
  );
}

import React from 'react';

const Governance = () => {
  return (
    <div className="card">
      <div className="card-title">Governance & Quality (ISO 9001)</div>
      
      <div style={{display: 'flex', justifyContent: 'space-between', marginBottom: '1.5rem'}}>
        <div className="badge badge-success">PLAN</div>
        <div className="badge badge-success">DO</div>
        <div className="badge badge-warning">CHECK</div>
        <div className="badge" style={{opacity: 0.2}}>ACT</div>
      </div>

      <div style={{marginBottom: '1rem', borderBottom: '1px solid var(--glass-border)', paddingBottom: '0.75rem'}}>
        <div style={{opacity: 0.5, fontSize: '0.75rem'}}>Latest Minutes: </div>
        <div style={{fontSize: '0.85rem', fontWeight: '600'}}>Sprint 1 Risk Review (2025-01-15)</div>
      </div>

      <ul style={{listStyle: 'none', padding: 0, margin: 0, fontSize: '0.85rem'}}>
        <li style={{marginBottom: '0.5rem'}}>• Accuracy Goal: 95% (Current: 85%)</li>
        <li style={{marginBottom: '0.5rem'}}>• Latency Goal: &lt;100ms (Average: 42ms)</li>
        <li>• Storage Goal: -40% (Current: -32%)</li>
      </ul>
    </div>
  );
};

export default Governance;

import React from 'react';

const Financial = () => {
  return (
    <div className="card">
      <div className="card-title">Financial (90-Day Sprint)</div>
      
      <div style={{marginBottom: '1.25rem'}}>
        <div style={{display: 'flex', justifyContent: 'space-between', fontSize: '0.8rem', marginBottom: '0.5rem'}}>
          <span style={{opacity: 0.5}}>Burn Rate (Credits Used)</span>
          <span style={{fontWeight: 600}}>$1,200 / $10,000</span>
        </div>
        <div className="gauge-container"><div className="gauge-fill" style={{width: '12%'}}></div></div>
      </div>

      <div style={{display: 'flex', gap: '1rem', marginBottom: '1.5rem'}}>
        <div style={{flex: 1}}>
          <div style={{fontSize: '1rem', fontWeight: 800}}>15%</div>
          <div style={{fontSize: '0.6rem', opacity: 0.5}}>Equity Tracker</div>
        </div>
        <div style={{flex: 1}}>
          <div style={{fontSize: '1rem', fontWeight: 800, color: 'var(--accent)'}}>0.65</div>
          <div style={{fontSize: '0.6rem', opacity: 0.5}}>Resale Readiness</div>
        </div>
      </div>

      <div style={{fontSize: '0.75rem', opacity: 0.5, fontStyle: 'italic'}}>
        Next Payout: Day 30 ($2k Tranche)
      </div>
    </div>
  );
};

export default Financial;

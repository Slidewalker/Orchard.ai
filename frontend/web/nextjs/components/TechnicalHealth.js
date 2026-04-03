import React from 'react';

const TechnicalHealth = () => {
  return (
    <div className="card">
      <div className="card-title">Technical Health</div>
      
      <div style={{display: 'flex', flexDirection: 'column', gap: '1rem'}}>
        <div style={{padding: '0.75rem', borderRadius: '1rem', background: 'rgba(255,255,255,0.02)', border: '1px solid var(--glass-border)'}}>
          <div style={{fontSize: '0.7rem', opacity: 0.5, marginBottom: '0.25rem'}}>Shard Latency (ms)</div>
          <div style={{display: 'flex', gap: '1rem', alignItems: 'flex-end', height: '40px'}}>
             <div style={{flex: 1, background: 'var(--success)', height: '12px', borderRadius: '4px'}}></div>
             <div style={{flex: 1, background: 'var(--success)', height: '18px', borderRadius: '4px'}}></div>
             <div style={{flex: 1, background: 'var(--warning)', height: '28px', borderRadius: '4px'}}></div>
             <div style={{flex: 1, background: 'var(--success)', height: '14px', borderRadius: '4px'}}></div>
          </div>
          <div style={{display: 'flex', justifyContent: 'space-between', fontSize: '0.6rem', opacity: 0.5, marginTop: '0.25rem'}}>
            <span>42ms</span>
            <span>89ms</span>
            <span>108ms</span>
            <span>56ms</span>
          </div>
        </div>

        <div style={{display: 'flex', gap: '0.5rem', flexWrap: 'wrap'}}>
           <div className="badge badge-success">S1: ONLINE</div>
           <div className="badge badge-warning">S2: DEPTH 142</div>
           <div className="badge badge-success">BEDROCK: ACTIVE</div>
        </div>
      </div>
    </div>
  );
};

export default TechnicalHealth;

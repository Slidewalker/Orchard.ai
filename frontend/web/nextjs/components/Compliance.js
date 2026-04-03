import React from 'react';

const Compliance = () => {
  return (
    <div className="card">
      <div className="card-title">Compliance (GDPR/HIPAA)</div>
      
      <div style={{display: 'flex', gap: '1rem', marginBottom: '1.5rem'}}>
        <div style={{flex: 1, padding: '1rem', background: 'rgba(255,255,255,0.02)', borderRadius: '1rem'}}>
          <div style={{fontSize: '1.5rem', fontWeight: 800}}>1,432</div>
          <div style={{fontSize: '0.65rem', opacity: 0.5}}>Chaff Deleted (24h)</div>
        </div>
        <div style={{flex: 1, padding: '1rem', background: 'rgba(255,255,255,0.02)', borderRadius: '1rem'}}>
          <div style={{fontSize: '1.5rem', fontWeight: 800, color: 'var(--success)'}}>100%</div>
          <div style={{fontSize: '0.65rem', opacity: 0.5}}>Anonymization Rate</div>
        </div>
      </div>

      <div className="card-title" style={{fontSize: '0.75rem'}}>Regulatory Change Alerts</div>
      <div className="stream-item" style={{borderColor: 'var(--warning)', background: 'rgba(245, 158, 11, 0.05)'}}>
        <div style={{fontWeight: 700, color: 'var(--warning)', fontSize: '0.75rem', marginBottom: '0.25rem'}}>HIPAA Update §164.x</div>
        <div style={{fontSize: '0.8rem', opacity: 0.8}}>New encryption standards for Bedrock input streams...</div>
      </div>
    </div>
  );
};

export default Compliance;

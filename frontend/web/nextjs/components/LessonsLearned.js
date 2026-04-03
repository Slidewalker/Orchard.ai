import React from 'react';

const LessonsLearned = () => {
  return (
    <div className="card">
      <div className="card-title">Lessons Learned Feed</div>
      
      <div className="stream-item" style={{borderColor: 'var(--glass-border)', padding: '0.75rem'}}>
        <div style={{fontWeight: 700, fontSize: '1.25rem', color: 'var(--success)', marginBottom: '0.25rem'}}>Mistake #31</div>
        <div style={{fontSize: '0.8rem', opacity: 0.8, marginBottom: '0.5rem'}}>Shard 03 latency spiked to 450ms during fan-out...</div>
        <div style={{fontSize: '0.7rem', color: 'var(--success)', opacity: 0.8, fontWeight: 700}}>[FIX] Added Redis replica to shard 03</div>
      </div>

      <div style={{marginTop: '1rem', background: 'rgba(255,255,255,0.02)', borderRadius: '1rem', padding: '1rem'}}>
        <div style={{fontSize: '0.65rem', opacity: 0.5, marginBottom: '0.5rem', textTransform: 'uppercase'}}>Threshold Adjustments</div>
        <div style={{display: 'flex', justifyContent: 'space-between', fontSize: '0.9rem', fontWeight: 600}}>
          <span>Utility: 0.70 &rarr; 0.75</span>
          <span style={{color: 'var(--success)'}}>+0.05</span>
        </div>
      </div>
    </div>
  );
};

export default LessonsLearned;

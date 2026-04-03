import React, { useState, useEffect } from 'react';

const WinnowingCore = () => {
  const [feed, setFeed] = useState([
    { id: 1, text: "Q4 sales up 22% due to new pricing...", score: 0.87, status: "WHEAT" },
    { id: 2, text: "Patient ID 4432 has hypertension recorded by nurse @ 14:00", score: 0.45, status: "CHAFF" },
    { id: 3, text: "Just had coffee ☕", score: 0.49, status: "CHAFF" }
  ]);

  return (
    <div className="card">
      <div className="card-title">
        <span>Winnowing Core</span>
        <div className="badge badge-success">85% Accuracy</div>
      </div>
      
      <div className="score-gauges">
        <div className="gauge-label">Utility: 0.85</div>
        <div className="gauge-container"><div className="gauge-fill" style={{width: '85%'}}></div></div>
        
        <div className="gauge-label">Privacy: 0.92</div>
        <div className="gauge-container"><div className="gauge-fill" style={{width: '92%'}}></div></div>
        
        <div className="gauge-label">Sustainability: 0.80</div>
        <div className="gauge-container"><div className="gauge-fill" style={{width: '80%'}}></div></div>
      </div>

      <div style={{marginTop: '1.5rem', maxHeight: '200px', overflowY: 'hidden'}}>
        {feed.map(item => (
          <div key={item.id} className="stream-item">
            <div style={{display: 'flex', justifyContent: 'space-between', marginBottom: '0.25rem'}}>
              <span className={item.status === 'WHEAT' ? 'badge badge-success' : 'badge badge-error'}>{item.status}</span>
              <span style={{opacity: 0.5}}>{item.score}</span>
            </div>
            {item.text}
          </div>
        ))}
      </div>

      <div style={{display: 'flex', gap: '0.5rem', marginTop: '1.5rem'}}>
        <button className="card" style={{flex: 1, padding: '0.75rem', cursor: 'pointer'}}>KEEP WHEAT</button>
        <button className="card" style={{flex: 1, padding: '0.75rem', cursor: 'pointer', borderColor: 'var(--error)'}}>BURN CHAFF</button>
      </div>
    </div>
  );
};

export default WinnowingCore;

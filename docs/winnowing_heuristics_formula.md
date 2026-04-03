# Winnowing Heuristics: Wheat vs. Chaff

**Biblical Matthew 3:12 | Implemented via AWS Bedrock**

## Scoring Formula
`Final Score = (Utility * 0.5) + (Privacy * 0.3) + (Sustainability * 0.2)`

| Metric | Weight | Threshold | Scoring Logic |
|--------|--------|-----------|---------------|
| **Utility** | 50% | Actionable insight? | 1.0 = direct decision value; 0.0 = noise |
| **Privacy** | 30% | Anonymization completeness | 1.0 = fully anonymized; 0.0 = PII present |
| **Sustainability** | 20% | CO₂ footprint per interaction | 1.0 = <0.1g CO₂; 0.0 = >10g CO₂ |

## Classification
- **WHEAT (Keep)** → Score > 0.7 (or 80% confidence from ensemble)
- **CHAFF (Discard)** → Score ≤ 0.7 → deleted within 24h, no storage cost

## Example
| Content | Utility | Privacy | Sustainability | Final | Verdict |
|---------|--------|---------|----------------|-------|---------|
| "Q4 sales up 22% due to new pricing" | 0.9 | 0.9 | 0.8 | **0.87** | ✅ WHEAT |
| "Just had coffee ☕" | 0.1 | 0.95 | 0.9 | **0.49** | ❌ CHAFF |
| "Patient ID 4432 has hypertension" | 0.7 | 0.0 | 0.8 | **0.45** | ❌ CHAFF (PII) |

## Lessons Learned Hook
Every misclassification (false wheat / false chaff) triggers a `Lessons Learned Report` entry → retrains threshold model weekly.

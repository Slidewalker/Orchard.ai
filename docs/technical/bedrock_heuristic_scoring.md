# Bedrock Heuristic Scoring Details
## Model
- **Primary**: AWS Bedrock (Claude-3/Titan)
- **Heuristic Inputs**: Utility, Privacy, Sustainability (CO2)
- **Ensemble Mode**: Verification by secondary model for borderline scores (0.65-0.75).

## Latency Controls
- Model inference must happen <250ms for winnowing cycle.
- Local fallback heuristics for infrastructure failures.

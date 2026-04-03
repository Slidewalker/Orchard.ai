# Developer Guide - Winnowing Logic
## Backend Setup
1. Clone repository.
2. `php artisan winnow:init` (Placeholder command).
3. Set thresholds in `config/winnowing.php`.

## Scoring
- Implement `BedrockScoringAgent` hooks.
- Focus on low-latency (<100ms) scoring cycles.
- Integrate CO2 tracking into model calls.

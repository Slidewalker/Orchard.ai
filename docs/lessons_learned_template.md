# Lessons Learned Template

## Sprint: [Sprint ID]

## What went well?

- [Item 1]

## What could be improved?

- [Item 1]

## Action Items

- [ ] [Action 1]
- [ ] [Action 2]

# Lessons Learned Report – Orchard.ai Sprint [X]

**ISO 9001:2015 Clause 10.2 | 90-Day Sprint**

## Metadata

- **Date:** YYYY-MM-DD
- **Sprint Day:** [30 / 60 / 90]
- **Trigger:** [Scheduled / Winnowing error / Compliance event]

## What happened?

**Technical challenge / mistake** (max 240 chars, wheat format):
> [e.g., Shard 03 latency spiked to 450ms during fan-out]

## Root cause analysis (5 Whys)

1. Why? ...
2. Why? ...
3. Why? ...
4. Why? ...
5. Why? → **Root:** Missing Redis cache on that shard

## Corrective action (PDCA Act phase)

- **Fix:** [e.g., Add Redis replica to shard 03]
- **Owner:** [Name]
- **Due:** [Date]

## Winnowing impact

- **Chaff misclassified as wheat?** Y/N
- **Wheat discarded as chaff?** Y/N
- **Threshold adjustment needed?** [New value]

## Management review signature

- Governance Lead: _________________  
- Quality rep (ISO 9001): _________________

## Retention

Move to `governance/lessons_learned/archive/` after 90-day sprint.

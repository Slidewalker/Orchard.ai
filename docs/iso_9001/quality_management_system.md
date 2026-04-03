# ISO 9001:2015 Quality Management System (QMS)

## Scope

The scope of the QMS includes the development, deployment, and monitoring of AI-driven compliance and governance solutions (Orchard.ai).

## Principles

1. Customer Focus
2. Leadership
3. Engagement of People
4. Process Approach
5. Improvement
6. Evidence-based Decision Making
7. Relationship Management

# ISO 9001:2015 Quality Management System (QMS)

**Orchard.ai Winnowing Platform**

## PDCA Integration

| Phase | Orchard.ai Implementation | Evidence Location |
|-------|--------------------------|-------------------|
| **Plan** | Risk assessment: "chaff" (low-value data) vs "wheat" | `governance/iso_9001_2015/pdca_cycles/plan_risk_assessment.md` |
| **Do** | Winnowing pipeline execution (Bedrock scoring) | `backend/laravel/app/Services/WinnowingHeuristics.php` |
| **Check** | Internal audits (ICE framework) | `compliance/internal_audits/ice_framework_checklist.md` |
| **Act** | Lessons learned → update thresholds | `governance/lessons_learned/90_day_sprint_retrospective.md` |

## Quality Policy Statement
>
> "Only wheat enters the barn. Chaff is discarded within 24 hours. Every byte must serve utility, privacy, or sustainability — or be burned."

## Quality Objectives (SMART)

1. **Winnowing accuracy ≥95%** by Day 60 (measured via precision/recall on test corpus)
2. **Latency <100ms** for 99th percentile micro-post writes
3. **Storage cost reduction ≥40%** vs. non-winnowed baseline
4. **Zero regulatory fines** (GDPR/HIPAA) through automated consent checks

## Management Review Schedule

- Sprint 1 (Day 30): Risk review + winnowing thresholds
- Sprint 2 (Day 60): Audit findings + corrective actions
- Sprint 3 (Day 90): Lessons learned + flip readiness

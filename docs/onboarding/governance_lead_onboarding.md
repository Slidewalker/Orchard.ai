# Governance Lead Onboarding Checklist

## Immediate Actions

- [ ] Review `90_day_sprint.md`.
- [ ] Access ISO 9001 PDCA docs.
- [ ] Coordinate with Compliance Officer on ICE framework.

## Key Stakeholders

- Development Team (Winnowing/Sharding)
- Compliance Officer (GDPR/HIPAA/ISO)
- Financial Controller (AWS Credits/Equity)

# Governance Lead Onboarding – First 7 Days

**Orchard.ai 90‑Day Sprint**

## Day 1: Sign & Secure

- [ ] Sign liability acknowledgment (`liability_framework/professional_standards_pozolanic.md`)
- [ ] Set up compliance alerts (Slack #compliance)
- [ ] Review ISO 9001 quality policy

## Day 2: Winnowing Thresholds

- [ ] Run baseline winnowing on sample data → verify >0.7 threshold
- [ ] Document starting thresholds in `winnowing_heuristics_formula.md`

## Day 3: Sharding & Infrastructure

- [ ] Verify Terraform plan (`infrastructure/terraform/`)
- [ ] Test fan‑out with Kafka locally

## Day 4: Compliance Check

- [ ] Run GDPR/HIPAA scanner (`scripts/compliance/hipaa_gdpr_scanner.py`)
- [ ] Confirm no PII retained after winnowing

## Day 5: Financial Setup

- [ ] Apply for AWS Startup Grant (`financial/aws_startup_grant_application.md`)
- [ ] Activate Kraken Business Edition

## Day 6: Lessons Learned Process

- [ ] Schedule weekly 30‑min lessons learned review (every Friday)
- [ ] Create `governance/lessons_learned/90_day_sprint_retrospective.md`

## Day 7: First Management Review (ISO 9001)

- [ ] Present readiness to quality rep (virtual)
- [ ] Sign off Sprint 0 completion

## Daily habits

- 09:00 – Check compliance dashboard
- 12:00 – Review winnowing logs (any chaff kept >24h?)
- 16:00 – Update decision log
- 17:30 – Lessons learned entry (if applicable)

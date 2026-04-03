# GDPR & HIPAA Implementation Details

## GDPR Minimization

- Store only `tree_id` and score.
- Content is ephemeral unless explicitly stored by user.
- Data deletion after 90 days (alpha limit).

## HIPAA Privacy

- BAA in place for all infrastructure providers.
- Data anonymization for Bedrock input.
- Detailed audit logs for PHI/PII access.

# GDPR/HIPAA Compliance: The Digital Sieve

**Orchard.ai Winnowing Pipeline**

## Data Minimization (GDPR Art.5(1)(c))

- **Retention:** Chaff deleted within 24h
- **Anonymization:** AWS Comprehend medical (HIPAA) + regex PII scrubber
- **Consent:** Granular opt‑in per "utility" category

## Winnowing as a Compliance Control

| Regulation | Requirement | Winnowing Implementation |
|------------|-------------|--------------------------|
| GDPR Art.17 (Right to erasure) | Delete on request | Chaff auto‑deleted; wheat flagged for manual review |
| HIPAA §164.308(a)(1) | Risk analysis | Bedrock scores privacy dimension → blocks PII >0.1 |
| GDPR Art.35 (DPIA) | Data protection impact assessment | Logged per winnowing batch |

## Continuous Monitoring (Predictive Compliance)

- **Tool:** Custom compliance scanner (`compliance/continuous_monitoring/regulatory_change_webhook.py`)
- **Frequency:** Daily delta against GDPR/HIPAA updates
- **Alert:** Slack #compliance if threshold changes by >5%

## Audit Trail

Every winnowing decision logged to `winnowing_logs` table (immutable, 7‑year retention).

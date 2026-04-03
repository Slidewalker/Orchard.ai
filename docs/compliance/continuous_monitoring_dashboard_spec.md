# Continuous Monitoring Dashboard Spec
## Metrics
- **Winnowing Accuracy**: 95% target.
- **Micro-post Latency**: 99th percentile <100ms.
- **Chaff Storage Ratio**: Target <10% of total data.
- **PII Breach Rate**: Goal 0%.

## Alerts
- **Critical**: PII identified in `good_fruits` table.
- **Warning**: Winnowing accuracy drops below 90%.
- **Info**: Daily CO2 footprint > 10% above baseline.

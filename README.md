# Orchard.ai (Alpha 0.1)
## Vision
A resilient, AI-powered governance and compliance ecosystem.

## Components
- **Backend**: Laravel + Kafka/RabbitMQ
- **Workers**: Python/Bedrock
- **Infrastructure**: Terraform + Kubernetes
- **Compliance**: HIPAA/GDPR Automated Scanning

## Setup
1. Copy environment file (enables real AI providers when keys are present):
   - Copy `.env.example` → `.env`
2. Start Orchard:
   - `docker compose up -d --build`
3. Verify the dashboard:
   - `http://localhost:8080`
4. Verify AI configuration (real vs fallback):
   - `http://localhost:8080/api/ai/status`

Notes
- If `api/ai/status` shows `"configured": false`, Orchard will run with heuristic fallback until you set one of:
  - `OPENAI_API_KEY` (and optional `OPENAI_MODEL`)
  - `ANTHROPIC_API_KEY` (and optional `ANTHROPIC_MODEL`)
  - AWS Bedrock creds (`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, and region)

## Sovereign Local AI (Ollama)
1. Keep `AI_PROVIDER=local` in `.env` (default in `.env.example`).
2. Start services:
   - `docker compose up -d --build`
3. Pull a local model into Ollama:
   - `docker compose exec ollama ollama pull llama3.2:1b`
4. Verify:
   - `http://localhost:8080/api/ai/status` should show `provider=local` and `reachable=true`.

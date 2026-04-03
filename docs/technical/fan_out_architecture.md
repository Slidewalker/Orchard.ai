# Fan-Out Architecture (Orchard.ai)
## Workflow
1. User creates a micro-post.
2. Winnowing scores content (Wheat).
3. Post is stored in the user's primary shard.
4. Fan-out service (Kafka/RabbitMQ) sends post ID to followers.
5. Post is asynchronously written to followers' Redis caches on their respective shards.

## Performance
- Target fan-out completion <1s for 10k followers.
- Zero impact on write path latency.

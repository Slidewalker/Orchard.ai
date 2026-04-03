# Winnowing Pipeline API Reference
## Endpoint: `/api/winnow`
- **Method**: `POST`
- **Input**: `{ "content": "string", "user_id": "uuid" }`
- **Output**: `{ "score": 0.85, "status": "wheat", "metrics": { "utility": 0.9, "privacy": 0.8 } }`

## Endpoint: `/api/shards`
- **Method**: `GET`
- **Output**: `{ "shard_id": "shard_01", "latency": "2ms" }`

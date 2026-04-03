# Orchard.ai Sharding Strategy

To ensure sub-100ms latency for all users, Orchard.ai employs a horizontal sharding strategy (shared-nothing architecture) based on `UserID`.

## Shard Key Formula

The shard is determined using the CRC32 hash of the `UserID`:

`shard_id = CRC32(UserID) % num_shards`

## Shard Layout (Initial)

| Shard | UserID range (hash) | MySQL instance | Redis cache |
| :--- | :--- | :--- | :--- |
| 01 | 0–42,949,672 | orchard-shard-01.cdefgh.us-east-1.rds | cache-01 |
| 02 | 42,949,673–85,899,345 | orchard-shard-02.ijklmn.us-east-1.rds | cache-02 |

## Write Path (240‑char micro‑post)

1. UserID → shard router → target shard
2. INSERT into `good_fruits` (wheat) or `chaff` (temp)
3. Fan‑out: push to followers' caches (async via Kafka)

## Read Path

- **Own posts:** Direct shard query (<100ms)
- **Followed feeds:** Redis cache (cache‑aside pattern)

## Rebalancing (Day 45 if needed)

Script: `scripts/sharding/rebalance_shards.py`

- Move users between shards during maintenance window (2h)
- Update `UserShardMap` table

## Monitoring

- **Per‑shard latency:** CloudWatch alarm >100ms → page on‑call
- **Chaff ratio per shard:** Alert if >30% (winnowing ineffective)

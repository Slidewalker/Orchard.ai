<?php

namespace App\Services;

use App\Models\User;

class ShardRouter
{
    /**
     * Determine which database shard a user belongs to.
     * Goal: <100ms routing latency.
     */
    public function getShardForUser($userId)
    {
        $shardCount = config('sharding.shard_count', 2);
        // User-based sharding for <100ms latency
        $shardIndex = (abs(crc32($userId)) % $shardCount) + 1;
        
        return "shard_0" . $shardIndex;
    }

    public function getConnectionForUser($userId)
    {
        $shard = $this->getShardForUser($userId);
        return config("database.connections.{$shard}");
    }
}

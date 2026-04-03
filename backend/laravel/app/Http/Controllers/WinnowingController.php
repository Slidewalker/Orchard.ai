<?php

namespace App\Http\Controllers;

use App\Services\BedrockScoringAgent;
use Illuminate\Http\Request;

class WinnowingController extends Controller
{
    protected $scoringAgent;

    public function __construct(BedrockScoringAgent $scoringAgent)
    {
        $this->scoringAgent = $scoringAgent;
    }

    /**
     * Alpha-01 Resource: Returns a stream (list) of winnowed posts.
     */
    public function stream()
    {
        $samplePosts = [
            "Just finishing sprint planning. Wheat: 3 new features. Chaff: 2 bugs discarded.",
            "My lunch was great 🍎 (non-actionable → chaff)",
            "GDPR update: New consent rules — our digital sieve auto-adapted.",
            "Shard rebalancing completed under 100ms latency, praise the winnowing.",
            "Patient ID 4432 has hypertension"
        ];

        $posts = [];
        foreach ($samplePosts as $text) {
            $scoreData = $this->scoringAgent->score($text);
            $score = round($scoreData['total_score'], 2);
            $verdict = $score > 0.7 ? 'wheat' : 'chaff';

            $posts[] = [
                'text' => $text,
                'utility' => round($scoreData['utility'], 2),
                'privacy' => round($scoreData['privacy'], 2),
                'co2' => round($scoreData['sustainability'], 2),
                'score' => $score,
                'verdict' => $verdict
            ];
        }

        return response()->json(['posts' => $posts]);
    }
}

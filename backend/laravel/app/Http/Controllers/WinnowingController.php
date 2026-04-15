<?php

namespace App\Http\Controllers;

use App\Services\BedrockScoringAgent;
use App\Models\Tree;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class WinnowingController extends Controller
{
    protected $scoringAgent;

    public function __construct(BedrockScoringAgent $scoringAgent)
    {
        $this->scoringAgent = $scoringAgent;
    }

    public function stream()
    {
        $trees = Tree::query()->latest('created_at')->limit(12)->get();
        $posts = $trees->map(function (Tree $tree) {
            $scoreData = $this->scoringAgent->score((string) $tree->content);
            return [
                'text' => $tree->content ?? $tree->name ?? '',
                'utility' => round($scoreData['utility'], 2),
                'privacy' => round($scoreData['privacy'], 2),
                'co2' => round($scoreData['sustainability'], 2),
                'score' => round((float) ($tree->score ?? $scoreData['total_score']), 2),
                'verdict' => $tree->verdict ?? ($scoreData['total_score'] > config('winnowing.thresholds.wheat_score', 0.7) ? 'wheat' : 'chaff'),
            ];
        })->values();

        return response()->json(['posts' => $posts]);
    }

    public function dispatch(Request $request)
    {
        $content = trim((string) $request->query('content', ''));
        if ($content === '') {
            return response()->json(['status' => 'error', 'message' => 'content is required'], 422);
        }

        $payload = ['properties' => new \stdClass(), 'routing_key' => 'orchard_fanout', 'payload' => json_encode(['text' => $content]), 'payload_encoding' => 'string'];

        try {
            $response = Http::withBasicAuth('guest', 'guest')
                ->timeout(4)
                ->post('http://rabbitmq:15672/api/exchanges/%2F/amq.default/publish', $payload);

            if ($response->ok() && data_get($response->json(), 'routed') === true) {
                return response()->json(['status' => 'success', 'message' => 'dispatched to queue']);
            }
        } catch (\Throwable $e) {
            // Fallback below preserves functionality when MQ API is unavailable.
        }

        // Reliable fallback: process synchronously to keep the app functional.
        $scoreData = $this->scoringAgent->score($content);
        $score = round($scoreData['total_score'], 2);
        $verdict = $score > config('winnowing.thresholds.wheat_score', 0.7) ? 'wheat' : 'chaff';

        Tree::query()->create([
            'name' => strlen($content) > 50 ? substr($content, 0, 47) . '..' : $content,
            'content' => $content,
            'score' => $score,
            'verdict' => $verdict,
            'shard_id' => 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'processed via local fallback',
            'score' => $score,
            'verdict' => $verdict,
        ]);
    }

    public function chat(Request $request)
    {
        $message = strtolower(trim((string) $request->input('message', '')));
        if ($message === '') {
            return response()->json(['reply' => 'Share your question and I will map it to a winnowing action.'], 422);
        }

        $reply = match (true) {
            str_contains($message, 'latency') => 'Current optimization path: monitor queue depth, cap heavy payloads, and prioritize shard rebalance tasks for sub-100ms response targets.',
            str_contains($message, 'compliance') || str_contains($message, 'gdpr') || str_contains($message, 'hipaa') => 'Compliance posture: score high-utility content, reduce PII retention, and let the cleaner purge stale chaff after 24h.',
            str_contains($message, 'wheat') || str_contains($message, 'chaff') => 'Wheat/chaff verdict comes from weighted utility, privacy, and sustainability scoring with a wheat threshold of 0.7.',
            default => 'I can dispatch content for scoring, explain verdict logic, and suggest next actions for compliance, quality, and performance.',
        };

        return response()->json(['reply' => $reply]);
    }
}

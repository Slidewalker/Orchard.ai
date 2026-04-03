<?php

// Minimal Alpha-01 Backend Handler (Server.php)
// This file handles internal 8000 port requests before Laravel is fully bootstrapped for Alpha-01.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Mock API Route: /api/winnowing/stream
if ($uri === '/api/winnowing/stream') {
    require_once __DIR__ . '/app/Services/BedrockScoringAgent.php';
    $agent = new \App\Services\BedrockScoringAgent();
    
    $sampleTexts = [
        "Q4 sales up 22% due to new pricing strategy. Actionable insight.",
        "Just had coffee ☕",
        "Patient ID 4432 has hypertension",
        "Breaking: Orchard.ai winnowing hits 97% this hour",
        "Just finished sprint planning. Wheat: 3 new features."
    ];
    
    $posts = [];
    foreach ($sampleTexts as $text) {
        $scoreData = $agent->score($text);
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
    
    header('Content-Type: application/json');
    echo json_encode(['posts' => $posts]);
    exit;
}

// Fallback: 404
http_response_code(404);
echo json_encode(['error' => 'Orchard.ai Alpha-01: Resource not found at ' . $uri]);

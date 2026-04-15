<?php

namespace App\Services;

class BedrockScoringAgent
{
    private array $fallbackWeights = [
        'utility' => 0.5,
        'privacy' => 0.3,
        'sustainability' => 0.2,
    ];

    /**
     * Scores content based on Utility, Privacy, and CO2 (Sustainability).
     */
    public function score($content)
    {
        $weights = function_exists('config') ? config('winnowing.scoring_weights', $this->fallbackWeights) : $this->fallbackWeights;

        $providerScore = $this->scoreWithProvider((string) $content);
        if ($providerScore !== null) {
            $providerScore['total_score'] = $this->clamp(
                ($providerScore['utility'] * $weights['utility']) +
                ($providerScore['privacy'] * $weights['privacy']) +
                ($providerScore['sustainability'] * $weights['sustainability'])
            );

            return $providerScore;
        }

        $utility = $this->analyzeUtility($content);
        $privacy = $this->analyzePrivacy($content);
        $sustainability = $this->analyzeCO2($content);
        $total = ($utility * $weights['utility']) + ($privacy * $weights['privacy']) + ($sustainability * $weights['sustainability']);

        return [
            'utility' => $utility,
            'privacy' => $privacy,
            'sustainability' => $sustainability,
            'total_score' => $this->clamp($total),
        ];
    }

    public function chat(string $message): string
    {
        $providerReply = $this->chatWithProvider($message);
        if (is_string($providerReply) && trim($providerReply) !== '') {
            return trim($providerReply);
        }

        $msg = strtolower(trim($message));
        if ($msg === '') {
            return 'Send a message and I will help with Orchard operations.';
        }

        return match (true) {
            str_contains($msg, 'status') => 'System status: web, app, db, rabbitmq, and ai-worker are expected online. Use /api/winnowing/dispatch to submit content and /api/winnowing/stream to inspect verdicts.',
            str_contains($msg, 'wheat'), str_contains($msg, 'chaff') => 'Wheat/chaff scoring uses weighted utility (50%), privacy (30%), and sustainability (20%). Scores above 0.70 are classified as wheat.',
            str_contains($msg, 'latency') => 'Latency guidance: keep queue payloads concise, monitor queue depth, and isolate heavy tasks onto worker lanes so interactive endpoints stay fast.',
            str_contains($msg, 'compliance'), str_contains($msg, 'gdpr'), str_contains($msg, 'hipaa') => 'Compliance guidance: avoid storing direct identifiers in messages, keep audit logs for actions, and allow 24h chaff cleanup to reduce retention risk.',
            str_contains($msg, 'hi'), str_contains($msg, 'hello') => 'Greetings. Orchard Assistant online. I can help with winnowing operations, compliance checks, and queue-backed processing.',
            default => 'I can help you dispatch content for AI scoring, explain verdict logic, or troubleshoot queue/database flow.',
        };
    }

    public function activeProvider(): string
    {
        return $this->detectProvider();
    }

    public function providerStatus(): array
    {
        $provider = $this->detectProvider();
        $status = [
            'provider' => $provider,
            'configured' => $provider !== 'none',
            'reachable' => null,
            'model' => null,
            'base_url' => null,
        ];

        if ($provider === 'local') {
            $status['model'] = getenv('LOCAL_AI_MODEL') ?: 'llama3.2:1b';
            $status['base_url'] = rtrim((string) (getenv('LOCAL_AI_BASE_URL') ?: 'http://ollama:11434'), '/');
            $status['reachable'] = $this->localProviderReachable($status['base_url']);
        }

        return $status;
    }

    private function analyzeUtility($content)
    {
        $normalized = strtolower((string) $content);
        $signalTerms = [
            'compliance', 'gdpr', 'hipaa', 'latency', 'incident', 'audit', 'sprint',
            'deployment', 'api', 'bug', 'risk', 'mitigation', 'metrics', 'queue',
        ];

        $hits = 0;
        foreach ($signalTerms as $term) {
            if (str_contains($normalized, $term)) {
                $hits++;
            }
        }

        // Utility rises with operational signal density and message substance.
        $lengthFactor = min(0.15, strlen($normalized) / 1200);
        return $this->clamp(0.45 + ($hits * 0.08) + $lengthFactor);
    }

    private function analyzePrivacy($content)
    {
        $normalized = strtolower((string) $content);
        $piiPatterns = [
            '/\b\d{3}-\d{2}-\d{4}\b/', // SSN pattern
            '/\b\d{10}\b/', // likely phone/id sequence
            '/\bpatient\b/',
            '/\bmrn\b/',
            '/\bemail\b/',
            '/\baddress\b/',
        ];

        foreach ($piiPatterns as $pattern) {
            if (preg_match($pattern, $normalized) === 1) {
                return 0.35;
            }
        }

        return 0.94;
    }

    private function analyzeCO2($content)
    {
        $normalized = strtolower((string) $content);
        $heavyTerms = ['video', 'raw dump', 'full export', 'archive', 'binary blob'];
        foreach ($heavyTerms as $term) {
            if (str_contains($normalized, $term)) {
                return 0.62;
            }
        }

        return 0.88;
    }

    private function scoreWithProvider(string $content): ?array
    {
        $provider = $this->detectProvider();
        if ($provider === 'none') {
            return null;
        }

        $prompt = "You are Orchard.ai's scoring model. Score the content for utility, privacy, and sustainability from 0.0 to 1.0. Respond with strict JSON only using keys utility, privacy, sustainability.\n\nContent:\n{$content}";
        $response = match ($provider) {
            'local' => $this->callLocalOllama($prompt, true),
            'openai' => $this->callOpenAI($prompt, true),
            'anthropic' => $this->callAnthropic($prompt, true),
            default => null,
        };

        if (!is_array($response)) {
            return null;
        }

        return [
            'utility' => $this->clamp((float) ($response['utility'] ?? 0)),
            'privacy' => $this->clamp((float) ($response['privacy'] ?? 0)),
            'sustainability' => $this->clamp((float) ($response['sustainability'] ?? 0)),
        ];
    }

    private function chatWithProvider(string $message): ?string
    {
        $provider = $this->detectProvider();
        if ($provider === 'none') {
            return null;
        }

        $prompt = "You are Orchard.ai Assistant. Give a concise operations-focused response for governance, compliance, queue-backed AI, and wheat/chaff decisions.\n\nUser message:\n{$message}";
        return match ($provider) {
            'local' => $this->callLocalOllama($prompt, false),
            'openai' => $this->callOpenAI($prompt, false),
            'anthropic' => $this->callAnthropic($prompt, false),
            default => null,
        };
    }

    private function detectProvider(): string
    {
        $preferred = strtolower((string) getenv('AI_PROVIDER'));
        if ($preferred !== '' && $preferred !== 'auto') {
            return $preferred;
        }

        if (getenv('LOCAL_AI_BASE_URL') || getenv('LOCAL_AI_MODEL')) {
            return 'local';
        }

        if (getenv('OPENAI_API_KEY')) {
            return 'openai';
        }

        if (getenv('ANTHROPIC_API_KEY')) {
            return 'anthropic';
        }

        return 'none';
    }

    private function callLocalOllama(string $prompt, bool $expectJson): array|string|null
    {
        $baseUrl = rtrim((string) (getenv('LOCAL_AI_BASE_URL') ?: 'http://ollama:11434'), '/');
        $model = getenv('LOCAL_AI_MODEL') ?: 'llama3.2:1b';

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $expectJson ? 'Return valid JSON only.' : 'Respond concisely and helpfully.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'stream' => false,
        ];

        if ($expectJson) {
            $payload['format'] = 'json';
        }

        $decoded = $this->postJson($baseUrl . '/api/chat', [
            'Content-Type: application/json',
        ], $payload);

        $content = $decoded['message']['content'] ?? null;
        if (!is_string($content)) {
            return null;
        }

        return $expectJson ? $this->extractJson($content) : $content;
    }

    private function callOpenAI(string $prompt, bool $expectJson): array|string|null
    {
        $apiKey = getenv('OPENAI_API_KEY');
        if (!$apiKey) {
            return null;
        }

        $model = getenv('OPENAI_MODEL') ?: 'gpt-4o-mini';
        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $expectJson ? 'Return valid JSON only.' : 'Respond concisely and helpfully.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
        ];

        $decoded = $this->postJson('https://api.openai.com/v1/chat/completions', [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ], $payload);

        $content = $decoded['choices'][0]['message']['content'] ?? null;
        if (!is_string($content)) {
            return null;
        }

        return $expectJson ? $this->extractJson($content) : $content;
    }

    private function callAnthropic(string $prompt, bool $expectJson): array|string|null
    {
        $apiKey = getenv('ANTHROPIC_API_KEY');
        if (!$apiKey) {
            return null;
        }

        $model = getenv('ANTHROPIC_MODEL') ?: 'claude-3-5-haiku-latest';
        $payload = [
            'model' => $model,
            'max_tokens' => 350,
            'temperature' => 0.2,
            'system' => $expectJson ? 'Return valid JSON only.' : 'Respond concisely and helpfully.',
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        $decoded = $this->postJson('https://api.anthropic.com/v1/messages', [
            'Content-Type: application/json',
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
        ], $payload);

        $content = $decoded['content'][0]['text'] ?? null;
        if (!is_string($content)) {
            return null;
        }

        return $expectJson ? $this->extractJson($content) : $content;
    }

    private function postJson(string $url, array $headers, array $payload): ?array
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!is_string($response) || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function extractJson(string $content): ?array
    {
        $trimmed = trim($content);
        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $trimmed, $matches) === 1) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function localProviderReachable(string $baseUrl): bool
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $ch = curl_init($baseUrl . '/api/tags');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return is_string($response) && $httpCode >= 200 && $httpCode < 300;
    }

    private function clamp(float $value): float
    {
        return max(0.0, min(1.0, round($value, 4)));
    }
}

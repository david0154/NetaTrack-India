<?php
/**
 * NetaTrack India — PHP Multi-AI Router
 * Auto-selects first working API from configured keys.
 * Priority: Gemini → OpenAI → OpenRouter → Claude → Sarvam
 *
 * Usage anywhere in PHP:
 *   $ai = AIRouter::make();          // loads keys from settings table
 *   $text = $ai->ask($prompt);        // plain text response
 *   $data = $ai->askJson($prompt);    // returns array
 *   $ai->isAvailable();               // true if at least one key exists
 */

namespace App\AI;

class AIRouter
{
    private array $keys = [];

    private const PRIORITY = ['gemini', 'openai', 'openrouter', 'claude', 'sarvam'];

    // ── Factory ──────────────────────────────────────────────────────────────
    public static function make(\PDO $pdo = null): static
    {
        $router = new static();
        $db = $pdo ?? db()->getPdo();
        $rows = $db->query(
            "SELECT `key`, `value` FROM settings WHERE `key` IN (
                'gemini_api_key','openai_api_key','openrouter_api_key',
                'claude_api_key','sarvam_api_key'
            )"
        )->fetchAll(\PDO::FETCH_KEY_PAIR);

        $map = [
            'gemini_api_key'     => 'gemini',
            'openai_api_key'     => 'openai',
            'openrouter_api_key' => 'openrouter',
            'claude_api_key'     => 'claude',
            'sarvam_api_key'     => 'sarvam',
        ];
        foreach ($map as $dbKey => $name) {
            if (!empty($rows[$dbKey])) {
                $router->keys[$name] = $rows[$dbKey];
            }
        }
        return $router;
    }

    public static function fromKeys(array $keys): static
    {
        $router = new static();
        $router->keys = array_filter($keys);
        return $router;
    }

    // ── Public API ───────────────────────────────────────────────────────────
    public function isAvailable(): bool
    {
        return !empty($this->keys);
    }

    public function activeProviders(): array
    {
        return array_keys($this->keys);
    }

    /**
     * Ask AI — returns plain text. Tries providers in priority order.
     */
    public function ask(string $prompt, int $maxTokens = 1024): string
    {
        foreach (self::PRIORITY as $provider) {
            if (empty($this->keys[$provider])) continue;
            try {
                $result = $this->call($provider, $this->keys[$provider], $prompt, $maxTokens);
                if ($result !== '') return $result;
            } catch (\Throwable) {
                continue;
            }
        }
        return '';
    }

    /**
     * Ask AI — returns decoded array from JSON response.
     */
    public function askJson(string $prompt): array
    {
        $raw = $this->ask($prompt . "\n\nRespond ONLY with valid JSON. No markdown, no explanation.");
        if (preg_match('/\{[\s\S]*\}/u', $raw, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) return $decoded;
        }
        return [];
    }

    /**
     * Summarise a leader's profile for public display.
     */
    public function summariseLeader(array $leader): string
    {
        if (!$this->isAvailable()) return '';
        $prompt = "Write a neutral 3-sentence Wikipedia-style summary for Indian politician \"{$leader['name']}\"."
            . " Party: {$leader['party']}. Constituency: {$leader['constituency']}."
            . " Performance score: {$leader['total_score']}/100."
            . " Write in English. No opinions. Just facts.";
        return $this->ask($prompt, 300);
    }

    /**
     * Analyse a public report about a leader.
     */
    public function analyseReport(string $report_text, string $leader_name): array
    {
        if (!$this->isAvailable()) return [];
        $prompt = "A citizen filed this report about Indian politician '{$leader_name}':\n\n\"{$report_text}\"\n\n"
            . 'Analyse it. Return JSON: {"category": "<corruption|performance|promise|project|personal|other>",'
            . '"sentiment": "<negative|positive|neutral>", "credibility": <0-100>,'
            . '"is_spam": <true|false>, "summary": "<one sentence>", "suggested_action": "<approve|reject|review>"}';
        return $this->askJson($prompt);
    }

    /**
     * Generate AI fact-check for a claim.
     */
    public function factCheck(string $claim, string $leader_name): array
    {
        if (!$this->isAvailable()) return [];
        $prompt = "Fact-check this claim about Indian politician '{$leader_name}':\n\"{$claim}\"\n\n"
            . 'Return JSON: {"verdict": "<true|false|partially_true|unverified>",'
            . '"confidence": <0-100>, "explanation": "<2 sentences>", "sources_hint": "<where to verify>"}';
        return $this->askJson($prompt);
    }

    /**
     * Classify a news headline for the announcement feed.
     */
    public function classifyNews(string $title, string $description = ''): array
    {
        if (!$this->isAvailable()) return ['category' => 'general', 'importance' => 1];
        $prompt = "Indian political news headline: \"{$title}\"\nDescription: \"{$description}\"\n"
            . 'Classify. Return JSON: {"category": "<promise|project|fund|scam|criminal|election|general>",'
            . '"leader_name": "<politician name or empty>", "sentiment": "<positive|negative|neutral>",'
            . '"importance": <1-5>, "tags": ["tag1","tag2"]}';
        return $this->askJson($prompt);
    }

    /**
     * Translate text to Hindi using Sarvam AI (falls back to Gemini translation).
     */
    public function translateToHindi(string $text): string
    {
        // Try Sarvam first (best for Indian languages)
        if (!empty($this->keys['sarvam'])) {
            try {
                return $this->callSarvamTranslate($this->keys['sarvam'], $text);
            } catch (\Throwable) {}
        }
        // Fallback: ask any AI
        return $this->ask("Translate this to Hindi (Devanagari script):\n\"{$text}\"", 500);
    }

    // ── Internal HTTP ────────────────────────────────────────────────────────
    private function call(string $provider, string $key, string $prompt, int $maxTokens): string
    {
        return match ($provider) {
            'gemini'     => $this->callGemini($key, $prompt, $maxTokens),
            'openai'     => $this->callOpenAI($key, $prompt, $maxTokens),
            'openrouter' => $this->callOpenRouter($key, $prompt, $maxTokens),
            'claude'     => $this->callClaude($key, $prompt, $maxTokens),
            'sarvam'     => $this->callSarvam($key, $prompt),
            default      => '',
        };
    }

    private function post(string $url, array $payload, array $headers): array
    {
        $ctx = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => implode("\r\n", array_map(
                    fn($k, $v) => "$k: $v", array_keys($headers), $headers
                )),
                'content' => json_encode($payload),
                'timeout' => 30,
                'ignore_errors' => true,
            ]
        ]);
        $raw = file_get_contents($url, false, $ctx);
        if ($raw === false) throw new \RuntimeException("HTTP request failed: $url");
        $data = json_decode($raw, true);
        if (!is_array($data)) throw new \RuntimeException("Invalid JSON response");
        return $data;
    }

    private function callGemini(string $key, string $prompt, int $max): string
    {
        $url  = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$key}";
        $resp = $this->post($url, ['contents' => [['parts' => [['text' => $prompt]]]]], ['Content-Type' => 'application/json']);
        return $resp['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    private function callOpenAI(string $key, string $prompt, int $max): string
    {
        $resp = $this->post(
            'https://api.openai.com/v1/chat/completions',
            ['model' => 'gpt-4o-mini', 'messages' => [['role' => 'user', 'content' => $prompt]], 'max_tokens' => $max],
            ['Content-Type' => 'application/json', 'Authorization' => "Bearer {$key}"]
        );
        return $resp['choices'][0]['message']['content'] ?? '';
    }

    private function callOpenRouter(string $key, string $prompt, int $max): string
    {
        $resp = $this->post(
            'https://openrouter.ai/api/v1/chat/completions',
            ['model' => 'meta-llama/llama-3-8b-instruct:free', 'messages' => [['role' => 'user', 'content' => $prompt]]],
            ['Content-Type' => 'application/json', 'Authorization' => "Bearer {$key}",
             'HTTP-Referer' => 'https://netatrack.in', 'X-Title' => 'NetaTrack India']
        );
        return $resp['choices'][0]['message']['content'] ?? '';
    }

    private function callClaude(string $key, string $prompt, int $max): string
    {
        $resp = $this->post(
            'https://api.anthropic.com/v1/messages',
            ['model' => 'claude-3-haiku-20240307', 'max_tokens' => $max,
             'messages' => [['role' => 'user', 'content' => $prompt]]],
            ['Content-Type' => 'application/json', 'x-api-key' => $key, 'anthropic-version' => '2023-06-01']
        );
        return $resp['content'][0]['text'] ?? '';
    }

    private function callSarvam(string $key, string $prompt): string
    {
        $resp = $this->post(
            'https://api.sarvam.ai/v1/chat/completions',
            ['model' => 'sarvam-2b', 'messages' => [['role' => 'user', 'content' => $prompt]]],
            ['Content-Type' => 'application/json', 'api-subscription-key' => $key]
        );
        return $resp['choices'][0]['message']['content'] ?? '';
    }

    private function callSarvamTranslate(string $key, string $text): string
    {
        $resp = $this->post(
            'https://api.sarvam.ai/translate',
            ['input' => $text, 'source_language_code' => 'en-IN', 'target_language_code' => 'hi-IN',
             'speaker_gender' => 'Male', 'mode' => 'formal'],
            ['Content-Type' => 'application/json', 'api-subscription-key' => $key]
        );
        return $resp['translated_text'] ?? '';
    }
}

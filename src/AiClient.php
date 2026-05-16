<?php
/**
 * AiClient - unified AI gateway supporting OpenAI, Gemini, OpenRouter, Sarvam AI, AWS Bedrock
 */
class AiClient
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'openai_key'      => getenv('OPENAI_API_KEY') ?: '',
            'gemini_key'      => getenv('GEMINI_API_KEY') ?: '',
            'openrouter_key'  => getenv('OPENROUTER_API_KEY') ?: '',
            'sarvam_key'      => getenv('SARVAM_API_KEY') ?: '',
            'aws_region'      => getenv('AWS_REGION') ?: '',
            'aws_key'         => getenv('AWS_ACCESS_KEY_ID') ?: '',
            'aws_secret'      => getenv('AWS_SECRET_ACCESS_KEY') ?: '',
            'provider'        => getenv('AI_PROVIDER') ?: 'gemini',
            'model'           => getenv('AI_MODEL') ?: 'gemini-1.5-flash',
        ];
    }

    /**
     * Extract political entities from raw text
     */
    public function extractEntities(string $text, string $sourceType = 'news'): array
    {
        $prompt = <<<PROMPT
You are a political data extractor for India. Analyze this text and extract structured data.
Return ONLY valid JSON with these fields:
{
  "type": "promise|project|corruption|scheme|speech|other",
  "title": "concise title",
  "leader_name": "name or null",
  "party": "party abbreviation or null",
  "state": "state name or null",
  "budget": number_or_null,
  "deadline": "YYYY-MM-DD or null",
  "status": "pending|in_progress|completed|delayed",
  "confidence": 0_to_100,
  "summary": "2 sentence summary"
}

Text: {$text}
PROMPT;
        $response = $this->chat($prompt);
        if (!$response['ok']) {
            return ['ok' => false, 'error' => $response['error'], 'confidence' => 0];
        }
        $json = $this->extractJson($response['content']);
        if (!$json) {
            return ['ok' => false, 'error' => 'JSON parse failed', 'confidence' => 0];
        }
        return array_merge(['ok' => true], $json);
    }

    /**
     * Verify a claim against known facts
     */
    public function verifyClaim(string $claim, string $context = ''): array
    {
        $prompt = <<<PROMPT
You are a fact-checker for Indian politics. Evaluate this claim and return ONLY valid JSON:
{
  "verdict": "true|false|partial|unverifiable",
  "confidence": 0_to_100,
  "reasoning": "brief explanation",
  "is_fake": true_or_false,
  "is_propaganda": true_or_false
}

Claim: {$claim}
Context: {$context}
PROMPT;
        $response = $this->chat($prompt);
        if (!$response['ok']) return ['ok' => false, 'confidence' => 0, 'verdict' => 'unverifiable'];
        $json = $this->extractJson($response['content']);
        return $json ? array_merge(['ok' => true], $json) : ['ok' => false, 'confidence' => 0, 'verdict' => 'unverifiable'];
    }

    /**
     * Detect spam / AI-generated submission
     */
    public function detectSpam(string $text): array
    {
        $prompt = <<<PROMPT
Analyze this submission for spam, AI generation, or propaganda. Return ONLY valid JSON:
{
  "is_spam": true_or_false,
  "is_ai_generated": true_or_false,
  "is_propaganda": true_or_false,
  "spam_score": 0_to_100,
  "reason": "brief reason"
}

Text: {$text}
PROMPT;
        $response = $this->chat($prompt);
        if (!$response['ok']) return ['ok' => false, 'spam_score' => 0, 'is_spam' => false];
        $json = $this->extractJson($response['content']);
        return $json ? array_merge(['ok' => true], $json) : ['ok' => false, 'spam_score' => 0, 'is_spam' => false];
    }

    /**
     * Translate text to English if in regional language (uses Sarvam AI)
     */
    public function translateToEnglish(string $text): array
    {
        if (!$this->config['sarvam_key']) {
            return ['ok' => true, 'text' => $text, 'translated' => false];
        }
        $response = $this->httpPost('https://api.sarvam.ai/translate', [
            'input'           => $text,
            'source_language_code' => 'auto',
            'target_language_code' => 'en-IN',
            'model'           => 'mayura:v1',
        ], ['API-Subscription-Key: ' . $this->config['sarvam_key']]);
        if ($response['ok']) {
            $data = json_decode($response['body'], true);
            return ['ok' => true, 'text' => $data['translated_text'] ?? $text, 'translated' => true];
        }
        return ['ok' => false, 'text' => $text, 'translated' => false];
    }

    /**
     * Core chat completion
     */
    public function chat(string $prompt, string $model = null): array
    {
        $provider = $this->config['provider'];
        $model    = $model ?: $this->config['model'];

        return match ($provider) {
            'openai'      => $this->openaiChat($prompt, $model),
            'openrouter'  => $this->openrouterChat($prompt, $model),
            'gemini'      => $this->geminiChat($prompt, $model),
            default       => $this->geminiChat($prompt, $model),
        };
    }

    private function geminiChat(string $prompt, string $model): array
    {
        if (!$this->config['gemini_key']) return ['ok' => false, 'error' => 'Gemini key not set'];
        $url  = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->config['gemini_key']}";
        $body = json_encode(['contents' => [['parts' => [['text' => $prompt]]]]]);
        $resp = $this->httpPost($url, [], [], $body);
        if (!$resp['ok']) return ['ok' => false, 'error' => $resp['error'] ?? 'HTTP error'];
        $data = json_decode($resp['body'], true);
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
        return $text ? ['ok' => true, 'content' => $text] : ['ok' => false, 'error' => 'Empty response'];
    }

    private function openaiChat(string $prompt, string $model): array
    {
        if (!$this->config['openai_key']) return ['ok' => false, 'error' => 'OpenAI key not set'];
        $url  = 'https://api.openai.com/v1/chat/completions';
        $body = json_encode(['model' => $model, 'messages' => [['role' => 'user', 'content' => $prompt]], 'temperature' => 0.2]);
        $resp = $this->httpPost($url, [], ['Authorization: Bearer ' . $this->config['openai_key']], $body);
        if (!$resp['ok']) return ['ok' => false, 'error' => $resp['error'] ?? 'HTTP error'];
        $data = json_decode($resp['body'], true);
        $text = $data['choices'][0]['message']['content'] ?? null;
        return $text ? ['ok' => true, 'content' => $text] : ['ok' => false, 'error' => 'Empty response'];
    }

    private function openrouterChat(string $prompt, string $model): array
    {
        if (!$this->config['openrouter_key']) return ['ok' => false, 'error' => 'OpenRouter key not set'];
        $url  = 'https://openrouter.ai/api/v1/chat/completions';
        $body = json_encode(['model' => $model, 'messages' => [['role' => 'user', 'content' => $prompt]]]);
        $resp = $this->httpPost($url, [], [
            'Authorization: Bearer ' . $this->config['openrouter_key'],
            'HTTP-Referer: https://netatrack.in',
        ], $body);
        if (!$resp['ok']) return ['ok' => false, 'error' => 'HTTP error'];
        $data = json_decode($resp['body'], true);
        $text = $data['choices'][0]['message']['content'] ?? null;
        return $text ? ['ok' => true, 'content' => $text] : ['ok' => false, 'error' => 'Empty response'];
    }

    private function httpPost(string $url, array $data = [], array $headers = [], string $rawBody = null): array
    {
        $ch = curl_init($url);
        $headers[] = 'Content-Type: application/json';
        $body = $rawBody ?: json_encode($data);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);
        if ($error) return ['ok' => false, 'error' => $error];
        if ($httpCode >= 400) return ['ok' => false, 'error' => "HTTP $httpCode", 'body' => $response];
        return ['ok' => true, 'body' => $response, 'code' => $httpCode];
    }

    private function extractJson(string $text): ?array
    {
        // Strip markdown code fences
        $text = preg_replace('/```(json)?\s*/i', '', $text);
        $text = preg_replace('/```\s*/i', '', $text);
        $text = trim($text);
        $data = json_decode($text, true);
        if (json_last_error() === JSON_ERROR_NONE) return $data;
        // Try to extract JSON object from text
        if (preg_match('/\{[\s\S]+\}/m', $text, $m)) {
            $data = json_decode($m[0], true);
            return json_last_error() === JSON_ERROR_NONE ? $data : null;
        }
        return null;
    }
}

<?php
namespace NetaTrack\Services;

class AIService {
    private string $geminiKey;
    private string $openaiKey;
    private string $openrouterKey;

    public function __construct() {
        $ai = APP_CONFIG['ai'];
        $this->geminiKey     = $ai['gemini_key']     ?? '';
        $this->openaiKey     = $ai['openai_key']     ?? '';
        $this->openrouterKey = $ai['openrouter_key'] ?? '';
    }

    public function analyzeText(string $text, string $prompt = ''): ?string {
        if ($this->geminiKey) return $this->geminiAnalyze($text, $prompt);
        if ($this->openaiKey)  return $this->openaiAnalyze($text, $prompt);
        return null;
    }

    private function geminiAnalyze(string $text, string $prompt): ?string {
        $url     = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key={$this->geminiKey}";
        $payload = json_encode([
            'contents' => [['parts' => [['text' => ($prompt ?: 'Analyze this political content for India context:') . "\n\n$text"]]]]
        ]);
        $response = $this->httpPost($url, $payload, ['Content-Type: application/json']);
        $data = json_decode($response, true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    private function openaiAnalyze(string $text, string $prompt): ?string {
        $url     = 'https://api.openai.com/v1/chat/completions';
        $payload = json_encode([
            'model'    => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a political fact-checker for India.'],
                ['role' => 'user',   'content' => ($prompt ?: 'Analyze:') . "\n\n$text"]
            ],
            'max_tokens' => 500
        ]);
        $response = $this->httpPost($url, $payload, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->openaiKey
        ]);
        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? null;
    }

    public function detectFakeClaim(string $claim): array {
        $prompt  = "You are a political fact-checker for India. Analyze if this claim is likely fake or real. Respond in JSON: {\"is_fake\": bool, \"confidence\": 0-100, \"reason\": \"...\"}";
        $result  = $this->analyzeText($claim, $prompt);
        if (!$result) return ['is_fake' => false, 'confidence' => 0, 'reason' => 'AI unavailable'];
        $json = json_decode($result, true);
        return $json ?: ['is_fake' => false, 'confidence' => 0, 'reason' => $result];
    }

    public function verifyImage(string $imagePath): array {
        return ['is_edited' => false, 'confidence' => 50, 'notes' => 'Image verification pending AI setup'];
    }

    private function httpPost(string $url, string $payload, array $headers = []): string {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response ?: '';
    }
}

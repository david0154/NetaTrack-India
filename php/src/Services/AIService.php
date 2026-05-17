<?php
namespace NetaTrack\Services;

/**
 * AIService — Gemini API integration for:
 * 1. Report confidence analysis
 * 2. Promise status detection from news text
 * 3. Fake claim detection
 * 4. Leader mention extraction from news
 */
class AIService
{
    private string $apiKey;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    private int    $timeout = 15;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Analyze a corruption/political report and return confidence score (0-100).
     */
    public function analyzeReport(string $title, string $description): int
    {
        $prompt = <<<PROMPT
You are a political fact-checker for India. Analyze this report about a political leader.

Title: {$title}
Description: {$description}

Rate the credibility of this report on a scale of 0-100 based on:
- Specificity of claims (names, dates, amounts)
- Plausibility given Indian political context
- Presence of verifiable facts

Respond with ONLY a JSON object: {"confidence": <number 0-100>, "reason": "<brief reason>"}
PROMPT;

        $response = $this->generate($prompt);
        if (!$response) return 50;

        $json = $this->extractJson($response);
        return (int)min(100, max(0, $json['confidence'] ?? 50));
    }

    /**
     * Detect promise status from a news article text.
     * Returns: kept | broken | in_progress | partial | unknown
     */
    public function detectPromiseStatus(string $promiseTitle, string $newsText): string
    {
        $prompt = <<<PROMPT
You are a political promise tracker for India.

Promise: "{$promiseTitle}"

News article excerpt:
{$newsText}

Based on the news, determine the current status of this promise.
Respond ONLY with JSON: {"status": "kept|broken|in_progress|partial|unknown", "confidence": <0-100>}
PROMPT;

        $response = $this->generate($prompt);
        if (!$response) return 'unknown';

        $json   = $this->extractJson($response);
        $status = $json['status'] ?? 'unknown';
        $valid  = ['kept','broken','in_progress','partial','unknown'];
        return in_array($status, $valid) ? $status : 'unknown';
    }

    /**
     * Detect if a political claim is likely fake/misleading.
     * Returns confidence score 0-100 that it IS fake.
     */
    public function detectFakeClaim(string $claim, string $context = ''): int
    {
        $prompt = <<<PROMPT
You are a misinformation detector specializing in Indian politics.

Claim: "{$claim}"
{$context ? "Context: {$context}" : ''}

Analyze if this claim is fake, misleading, or out-of-context.
Respond ONLY with JSON: {"fake_probability": <0-100>, "reason": "<brief>"}
PROMPT;

        $response = $this->generate($prompt);
        if (!$response) return 0;

        $json = $this->extractJson($response);
        return (int)min(100, max(0, $json['fake_probability'] ?? 0));
    }

    /**
     * Extract leader names mentioned in a news article.
     * Returns array of leader names.
     */
    public function extractLeaderMentions(string $text): array
    {
        $prompt = <<<PROMPT
Extract the names of Indian political leaders, politicians, or government officials mentioned in this text.
Only include actual politicians (not journalists, actors, etc.).

Text: "{$text}"

Respond ONLY with JSON: {"leaders": ["Name1", "Name2", ...]}
PROMPT;

        $response = $this->generate($prompt);
        if (!$response) return [];

        $json = $this->extractJson($response);
        return array_filter((array)($json['leaders'] ?? []));
    }

    /**
     * Summarize a long political news article into 2 sentences.
     */
    public function summarize(string $text, int $maxLength = 300): string
    {
        $trimmed = mb_substr($text, 0, 2000);
        $prompt  = "Summarize this Indian political news in exactly 2 concise sentences (max {$maxLength} characters total). Be factual and neutral.\n\n{$trimmed}";

        $response = $this->generate($prompt);
        return $response ? mb_substr(trim($response), 0, $maxLength) : '';
    }

    /**
     * Core Gemini API call.
     */
    private function generate(string $prompt): ?string
    {
        if (empty($this->apiKey)) return null;

        $payload = json_encode([
            'contents' => [[
                'parts' => [['text' => $prompt]]
            ]],
            'generationConfig' => [
                'temperature'     => 0.2,
                'maxOutputTokens' => 256,
            ],
        ]);

        $ch = curl_init($this->baseUrl . '?key=' . $this->apiKey);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || !$raw) return null;

        $data = json_decode($raw, true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    private function extractJson(string $text): array
    {
        // Strip markdown code fences if present
        $text = preg_replace('/```(?:json)?\s*/i', '', $text);
        $text = str_replace('```', '', $text);
        $text = trim($text);

        // Find JSON object
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');
        if ($start === false || $end === false) return [];

        $json = substr($text, $start, $end - $start + 1);
        return json_decode($json, true) ?? [];
    }
}

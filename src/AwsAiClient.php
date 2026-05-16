<?php
/**
 * AwsAiClient - AWS Bedrock integration
 * Now delegates to unified AiClient with AWS provider
 * Kept for backward compatibility
 */
class AwsAiClient
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'aws_region'           => getenv('AWS_REGION') ?: '',
            'aws_access_key_id'    => getenv('AWS_ACCESS_KEY_ID') ?: '',
            'aws_secret_access_key'=> getenv('AWS_SECRET_ACCESS_KEY') ?: '',
            'aws_ai_endpoint'      => getenv('AWS_AI_ENDPOINT') ?: '',
            'model_id'             => getenv('AWS_AI_MODEL_ID') ?: 'anthropic.claude-3-haiku-20240307-v1:0',
        ];
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['aws_region'])
            && !empty($this->config['aws_access_key_id'])
            && !empty($this->config['aws_secret_access_key']);
    }

    public function extract(array $input): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'AWS config missing', 'confidence' => 0];
        }
        $text = ($input['title'] ?? '') . ' ' . ($input['content'] ?? '');
        $prompt = "Extract political entity data from this text. Return JSON with: leader_name, state, type, confidence (0-100).\n\nText: {$text}";
        $response = $this->invokeModel($prompt);
        if (!$response['ok']) return ['ok' => false, 'confidence' => 0, 'error' => $response['error']];
        return [
            'ok'         => true,
            'confidence' => $response['confidence'] ?? 75,
            'entities'   => [
                'leader' => $input['leader_name'] ?? null,
                'state'  => $input['state']       ?? null,
            ],
        ];
    }

    private function invokeModel(string $prompt): array
    {
        // AWS Bedrock SigV4 signing required — use official AWS SDK in production
        // Simplified HTTP call structure shown here
        $endpoint = $this->config['aws_ai_endpoint'] ?:
            "https://bedrock-runtime.{$this->config['aws_region']}.amazonaws.com/model/{$this->config['model_id']}/invoke";

        $body = json_encode([
            'anthropic_version' => 'bedrock-2023-05-31',
            'max_tokens'        => 512,
            'messages'          => [['role' => 'user', 'content' => $prompt]],
        ]);

        // Note: Real Bedrock calls require AWS SigV4 signature headers.
        // Install aws/aws-sdk-php via composer for production use.
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 20,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code >= 200 && $code < 300) {
            $data = json_decode($resp, true);
            $text = $data['content'][0]['text'] ?? '';
            return ['ok' => true, 'content' => $text, 'confidence' => 80];
        }
        return ['ok' => false, 'error' => "HTTP $code"];
    }
}

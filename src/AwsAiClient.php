<?php
class AwsAiClient
{
    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/ai.php';
    }

    public function isConfigured(): bool
    {
        return !empty($this->config['aws_region']) &&
            !empty($this->config['aws_access_key_id']) &&
            !empty($this->config['aws_secret_access_key']) &&
            !empty($this->config['aws_ai_endpoint']) &&
            !empty($this->config['model_id']);
    }

    public function extract(array $input): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'AWS AI config missing'];
        }
        // Placeholder implementation. Connect AWS Bedrock/SageMaker runtime here.
        return [
            'ok' => true,
            'confidence' => 82,
            'entities' => [
                'leader' => $input['leader_name'] ?? null,
                'state' => $input['state'] ?? null,
            ],
        ];
    }
}

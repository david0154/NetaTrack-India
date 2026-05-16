<?php
/**
 * NetaTrack India - AI Providers Configuration
 */
return [
    'primary'    => getenv('AI_PRIMARY_PROVIDER') ?: 'gemini',
    'openai'     => [
        'key'     => getenv('OPENAI_API_KEY') ?: '',
        'model'   => getenv('OPENAI_MODEL') ?: 'gpt-4o',
        'base_url'=> 'https://api.openai.com/v1',
    ],
    'gemini'     => [
        'key'     => getenv('GEMINI_API_KEY') ?: '',
        'model'   => getenv('GEMINI_MODEL') ?: 'gemini-1.5-pro',
        'base_url'=> 'https://generativelanguage.googleapis.com/v1beta',
    ],
    'openrouter' => [
        'key'     => getenv('OPENROUTER_API_KEY') ?: '',
        'model'   => getenv('OPENROUTER_MODEL') ?: 'anthropic/claude-3-haiku',
        'base_url'=> 'https://openrouter.ai/api/v1',
    ],
    'sarvam'     => [
        'key'     => getenv('SARVAM_API_KEY') ?: '',
        'base_url'=> 'https://api.sarvam.ai',
        'language'=> getenv('SARVAM_LANGUAGE') ?: 'hi-IN',
    ],
    'aws_bedrock'=> [
        'key'     => getenv('AWS_ACCESS_KEY') ?: '',
        'secret'  => getenv('AWS_SECRET_KEY') ?: '',
        'region'  => getenv('AWS_REGION') ?: 'ap-south-1',
        'model'   => 'amazon.titan-text-express-v1',
    ],
];

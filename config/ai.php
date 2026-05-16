<?php
// NetaTrack India — AI Service Configuration
// All keys loaded from environment variables. Never hardcode keys here.

return [
    // Google Gemini (primary AI)
    'gemini_api_key'    => getenv('GEMINI_API_KEY')    ?: '',
    'gemini_model'      => getenv('GEMINI_MODEL')      ?: 'gemini-1.5-flash',

    // OpenAI (fallback)
    'openai_api_key'    => getenv('OPENAI_API_KEY')    ?: '',
    'openai_model'      => getenv('OPENAI_MODEL')      ?: 'gpt-4o-mini',

    // OpenRouter (multi-model gateway)
    'openrouter_api_key' => getenv('OPENROUTER_API_KEY') ?: '',
    'openrouter_model'   => getenv('OPENROUTER_MODEL')   ?: 'google/gemma-3-27b-it:free',

    // Sarvam AI (Hindi/Indian language support)
    'sarvam_api_key'    => getenv('SARVAM_API_KEY')    ?: '',
    'sarvam_model'      => getenv('SARVAM_MODEL')      ?: 'sarvam-2b-v0.5',

    // AWS Bedrock (optional)
    'aws_region'              => getenv('AWS_REGION')              ?: 'ap-south-1',
    'aws_access_key_id'       => getenv('AWS_ACCESS_KEY_ID')       ?: '',
    'aws_secret_access_key'   => getenv('AWS_SECRET_ACCESS_KEY')   ?: '',
    'aws_ai_endpoint'         => getenv('AWS_AI_ENDPOINT')         ?: '',
    'model_id'                => getenv('AWS_MODEL_ID')            ?: '',

    // Confidence thresholds
    'min_confidence_auto_queue' => (int)(getenv('AI_MIN_CONFIDENCE') ?: 75),
    'min_confidence_auto_approve' => (int)(getenv('AI_AUTO_APPROVE') ?: 92),
];

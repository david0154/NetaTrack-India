<?php
return [
    'aws_region' => getenv('AWS_REGION') ?: '',
    'aws_access_key_id' => getenv('AWS_ACCESS_KEY_ID') ?: '',
    'aws_secret_access_key' => getenv('AWS_SECRET_ACCESS_KEY') ?: '',
    'aws_ai_endpoint' => getenv('AWS_AI_ENDPOINT') ?: '',
    'model_id' => getenv('AWS_AI_MODEL_ID') ?: '',
    'enabled' => getenv('AWS_AI_ENABLED') === 'true',
];

<?php
namespace NetaTrack\Helpers;

class Security
{
    public static function sanitizeInput(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map([static::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(strip_tags(trim((string)$input)), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeHtml(string $html): string
    {
        // Allow basic formatting tags only
        $allowed = '<p><br><strong><em><ul><ol><li><a><h2><h3><h4><blockquote>';
        return strip_tags($html, $allowed);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    public static function validateJwt(string $token, string $secret): array|false
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        [$header, $payload, $signature] = $parts;
        $expectedSig = hash_hmac('sha256', "$header.$payload", $secret, true);
        $expectedSigB64 = rtrim(base64_encode($expectedSig), '=');

        if (!hash_equals($expectedSigB64, $signature)) return false;

        $data = json_decode(base64_decode($payload), true);
        if (isset($data['exp']) && $data['exp'] < time()) return false;

        return $data;
    }

    public static function createJwt(array $payload, string $secret, int $expiry = 3600): string
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;
        $payloadB64 = rtrim(base64_encode(json_encode($payload)), '=');
        $sig = hash_hmac('sha256', "$header.$payloadB64", $secret, true);
        $sigB64 = rtrim(base64_encode($sig), '=');
        return "$header.$payloadB64.$sigB64";
    }

    public static function isRateLimited(string $key, int $maxAttempts, int $decaySeconds): bool
    {
        $cacheFile = sys_get_temp_dir() . '/rl_' . md5($key) . '.json';
        $data = ['attempts' => 0, 'reset_at' => time() + $decaySeconds];

        if (file_exists($cacheFile)) {
            $saved = json_decode(file_get_contents($cacheFile), true);
            if ($saved['reset_at'] > time()) {
                $data = $saved;
            }
        }

        $data['attempts']++;
        file_put_contents($cacheFile, json_encode($data));

        return $data['attempts'] > $maxAttempts;
    }

    public static function validateFileUpload(array $file, array $allowedTypes, int $maxSize): array
    {
        $errors = [];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed with error code: ' . $file['error'];
            return $errors;
        }
        if ($file['size'] > $maxSize) {
            $errors[] = 'File too large. Max size: ' . round($maxSize / 1024 / 1024, 1) . 'MB';
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            $errors[] = 'File type not allowed. Allowed: ' . implode(', ', $allowedTypes);
        }
        // Verify MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $safeMimes = ['image/jpeg','image/png','image/webp','image/gif','video/mp4','application/pdf'];
        if (!in_array($mimeType, $safeMimes) && !in_array($ext, ['doc','docx'])) {
            $errors[] = 'Invalid file MIME type detected';
        }
        return $errors;
    }
}

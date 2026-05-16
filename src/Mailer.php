<?php
require_once __DIR__ . '/Database.php';

/**
 * Mailer - sends emails via SMTP using PHP's mail() or cURL to SMTP
 * Supports Gmail, SendGrid, Mailgun, custom SMTP
 */
class Mailer
{
    private ?array $config = null;

    private function loadConfig(): bool
    {
        if ($this->config !== null) return !empty($this->config);
        $this->config = Database::queryOne('SELECT * FROM smtp_settings WHERE is_active = 1 LIMIT 1');
        return !empty($this->config);
    }

    public function send(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
    {
        if (!$this->loadConfig()) {
            error_log('[Mailer] No active SMTP config found');
            return false;
        }
        if ($this->config['provider'] === 'sendgrid') {
            return $this->sendViaSendGrid($to, $subject, $htmlBody, $textBody);
        }
        if ($this->config['provider'] === 'mailgun') {
            return $this->sendViaMailgun($to, $subject, $htmlBody, $textBody);
        }
        return $this->sendViaSmtp($to, $subject, $htmlBody);
    }

    public function sendVerificationEmail(string $to, string $name, string $token): bool
    {
        $url  = rtrim(getenv('APP_URL') ?: 'https://netatrack.in', '/') . '/verify-email?token=' . $token;
        $html = "<h2>Hello {$name},</h2><p>Click the link below to verify your email:</p><p><a href='{$url}'>{$url}</a></p><p>This link expires in 24 hours.</p>";
        return $this->send($to, 'Verify your NetaTrack India account', $html);
    }

    public function sendPasswordReset(string $to, string $name, string $token): bool
    {
        $url  = rtrim(getenv('APP_URL') ?: 'https://netatrack.in', '/') . '/reset-password?token=' . $token;
        $html = "<h2>Hello {$name},</h2><p>Click below to reset your password:</p><p><a href='{$url}'>{$url}</a></p><p>This link expires in 1 hour.</p>";
        return $this->send($to, 'Reset your NetaTrack India password', $html);
    }

    public function sendSubmissionUpdate(string $to, string $name, string $title, string $status): bool
    {
        $html = "<h2>Hello {$name},</h2><p>Your submission <b>{$title}</b> has been updated to: <b>{$status}</b>.</p><p>Thank you for contributing to political transparency.</p>";
        return $this->send($to, 'Your submission has been updated — NetaTrack India', $html);
    }

    private function sendViaSmtp(string $to, string $subject, string $html): bool
    {
        // Use PHP mail() as fallback for basic SMTP (production should use PHPMailer or Symfony Mailer)
        $from    = $this->config['from_email'];
        $name    = $this->config['from_name'];
        $headers = implode("\r\n", [
            "MIME-Version: 1.0",
            "Content-type: text/html; charset=UTF-8",
            "From: {$name} <{$from}>",
            "Reply-To: {$from}",
        ]);
        return mail($to, $subject, $html, $headers);
    }

    private function sendViaSendGrid(string $to, string $subject, string $html, string $text): bool
    {
        $key  = getenv('SENDGRID_API_KEY') ?: '';
        if (!$key) return false;
        $body = json_encode([
            'personalizations' => [['to' => [['email' => $to]]]],
            'from'             => ['email' => $this->config['from_email'], 'name' => $this->config['from_name']],
            'subject'          => $subject,
            'content'          => [
                ['type' => 'text/plain', 'value' => $text ?: strip_tags($html)],
                ['type' => 'text/html', 'value' => $html],
            ],
        ]);
        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $key, 'Content-Type: application/json'],
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    private function sendViaMailgun(string $to, string $subject, string $html, string $text): bool
    {
        $key    = getenv('MAILGUN_API_KEY') ?: '';
        $domain = getenv('MAILGUN_DOMAIN') ?: '';
        if (!$key || !$domain) return false;
        $ch = curl_init("https://api.mailgun.net/v3/{$domain}/messages");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_USERPWD        => 'api:' . $key,
            CURLOPT_POSTFIELDS     => [
                'from'    => $this->config['from_name'] . ' <' . $this->config['from_email'] . '>',
                'to'      => $to,
                'subject' => $subject,
                'html'    => $html,
                'text'    => $text ?: strip_tags($html),
            ],
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code === 200;
    }
}

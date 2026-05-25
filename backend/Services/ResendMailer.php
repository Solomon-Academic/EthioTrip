<?php
namespace Backend\Services;

class ResendMailer
{
    private string $apiKey;
    private string $from;

    public function __construct(?string $apiKey = null, ?string $from = null)
    {
        $this->apiKey = $apiKey ?? (Env::get('RESEND_API_KEY') ?? Env::get('EMAIL_API') ?? '');
        $this->from = $from ?? (Env::get('RESEND_FROM') ?? 'EthioTrip <onboarding@resend.dev>');
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== '' && str_starts_with($this->apiKey, 're_');
    }

    public function send(string $to, string $subject, string $html, ?string $text = null): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !$this->isConfigured()) {
            return false;
        }

        $payload = [
            'from' => $this->from,
            'to' => [$to],
            'subject' => $subject,
            'html' => $html,
        ];

        if ($text !== null) {
            $payload['text'] = $text;
        }

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        error_log('Resend API error (' . $httpCode . '): ' . (string) $response);
        return false;
    }
}

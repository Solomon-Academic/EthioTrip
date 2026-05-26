<?php
namespace Backend\Services\Mail;

interface MailerInterface
{
    public function isConfigured(): bool;

    /**
     * @return array{success: bool, error: ?string}
     */
    public function send(string $to, string $subject, string $html, ?string $text = null): array;
}

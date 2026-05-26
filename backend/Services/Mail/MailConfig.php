<?php
namespace Backend\Services\Mail;

use Backend\Services\Env;

class MailConfig
{
    public static function gmailUser(): string
    {
        return trim(Env::get('GMAIL_USER') ?? Env::get('SMTP_USER') ?? '');
    }

    public static function gmailAppPassword(): string
    {
        return trim(Env::get('GMAIL_APP_PASSWORD') ?? Env::get('SMTP_PASSWORD') ?? '');
    }

    public static function fromEmail(): string
    {
        $from = trim(Env::get('MAIL_FROM_EMAIL') ?? '');
        return $from !== '' ? $from : self::gmailUser();
    }

    public static function fromName(): string
    {
        return trim(Env::get('MAIL_FROM_NAME') ?? 'EthioTrip');
    }

    public static function isConfigured(): bool
    {
        return self::gmailUser() !== ''
            && self::gmailAppPassword() !== ''
            && filter_var(self::fromEmail(), FILTER_VALIDATE_EMAIL);
    }
}

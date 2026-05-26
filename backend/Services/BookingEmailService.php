<?php
namespace Backend\Services;

use Backend\Services\Mail\EmailTemplates;
use Backend\Services\Mail\GmailSmtpMailer;
use Backend\Services\Mail\MailerInterface;
/**
 * Sends booking/payment notifications via Gmail SMTP.
 */
class BookingEmailService
{
    private MailerInterface $mailer;
    private EmailTemplates $templates;

    public function __construct(?MailerInterface $mailer = null, ?EmailTemplates $templates = null)
    {
        $this->mailer = $mailer ?? new GmailSmtpMailer();
        $this->templates = $templates ?? new EmailTemplates();
    }

    /**
     * @return array{sent: bool, skipped: bool, reason: string, email: string}
     */
    public function sendPaymentApprovedNotification(array $booking): array
    {
        $tpl = $this->templates->paymentApproved($booking);
        return $this->dispatch($booking, $tpl['subject'], $tpl['html'], $tpl['text']);
    }

    /**
     * @return array{sent: bool, skipped: bool, reason: string, email: string}
     */
    public function sendPaymentRejectedNotification(array $booking): array
    {
        $tpl = $this->templates->paymentRejected($booking);
        return $this->dispatch($booking, $tpl['subject'], $tpl['html'], $tpl['text']);
    }

    /**
     * @return array{sent: bool, skipped: bool, reason: string, email: string}
     */
    public function sendBookingApprovedNotification(array $booking): array
    {
        $tpl = $this->templates->bookingConfirmed($booking);
        return $this->dispatch($booking, $tpl['subject'], $tpl['html'], $tpl['text']);
    }

    public function isDeliverableEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        $lower = strtolower($email);
        if (str_contains($lower, '@ethiotrip.local') || str_contains($lower, 'guest+')) {
            return false;
        }
        return true;
    }

    public function isMailConfigured(): bool
    {
        return $this->mailer->isConfigured();
    }

    /**
     * @return array{sent: bool, skipped: bool, reason: string, email: string}
     */
    private function dispatch(array $booking, string $subject, string $html, string $text): array
    {
        $email = trim($booking['user_email'] ?? '');

        if ($email === '') {
            return [
                'sent' => false,
                'skipped' => true,
                'reason' => 'No email address on file for this customer.',
                'email' => '',
            ];
        }

        if (!$this->isDeliverableEmail($email)) {
            return [
                'sent' => false,
                'skipped' => true,
                'reason' => 'This account uses a placeholder email. Ask the customer to register with a valid email address.',
                'email' => $email,
            ];
        }

        if (!$this->mailer->isConfigured()) {
            return [
                'sent' => false,
                'skipped' => false,
                'reason' => 'Gmail SMTP is not configured. Add GMAIL_USER and GMAIL_APP_PASSWORD to your .env file.',
                'email' => $email,
            ];
        }

        $result = $this->mailer->send($email, $subject, $html, $text);

        if ($result['success']) {
            return [
                'sent' => true,
                'skipped' => false,
                'reason' => '',
                'email' => $email,
            ];
        }

        return [
            'sent' => false,
            'skipped' => false,
            'reason' => $this->humanizeMailError($result['error'] ?? 'Unable to send email.'),
            'email' => $email,
        ];
    }

    private function humanizeMailError(string $error): string
    {
        $lower = strtolower($error);
        if (str_contains($lower, 'authenticate') || str_contains($lower, 'username and password')) {
            return 'Gmail authentication failed. Check GMAIL_USER and GMAIL_APP_PASSWORD in .env (use a Google App Password, not your normal password).';
        }
        if (str_contains($lower, 'could not connect')) {
            return 'Could not connect to Gmail SMTP. Check your internet connection and that port 587 is not blocked.';
        }
        return $error;
    }
}

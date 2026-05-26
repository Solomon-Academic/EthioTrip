<?php
namespace Backend\Services\Mail;

use PHPMailer\PHPMailer\Exception as PhpMailerException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Sends email via Gmail SMTP (same credentials you would use with Nodemailer).
 */
class GmailSmtpMailer implements MailerInterface
{
    public function isConfigured(): bool
    {
        return MailConfig::isConfigured();
    }

    /**
     * @return array{success: bool, error: ?string}
     */
    public function send(string $to, string $subject, string $html, ?string $text = null): array
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid recipient email address.'];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'Gmail SMTP is not configured. Set GMAIL_USER and GMAIL_APP_PASSWORD in .env.',
            ];
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = MailConfig::gmailUser();
            $mail->Password = MailConfig::gmailAppPassword();
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;

            $mail->setFrom(MailConfig::fromEmail(), MailConfig::fromName());
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = $text ?? strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $html));

            $mail->send();

            return ['success' => true, 'error' => null];
        } catch (PhpMailerException $e) {
            $message = $mail->ErrorInfo ?: $e->getMessage();
            error_log('Gmail SMTP error: ' . $message);
            return ['success' => false, 'error' => $message];
        }
    }
}

<?php
namespace Backend\Services\Mail;

class EmailTemplates
{
    public function __construct(private array $config = [])
    {
        if ($this->config === []) {
            $this->config = require __DIR__ . '/../../Config/config.php';
        }
    }

    public function paymentApproved(array $booking): array
    {
        $name = $booking['user_name'] ?? 'Traveler';
        $adminNote = $this->extractAdminNote($booking['admin_notes'] ?? '', 'Payment note:');
        $rows = [
            'Booking reference' => '#' . ($booking['id'] ?? ''),
            'Transaction ID' => $booking['transaction_id'] ?? '—',
            'Destination' => $booking['destination'] ?? '—',
            'Travel package' => $booking['package_name'] ?? '—',
            'Travel dates' => ($booking['start_date'] ?? '') . ' to ' . ($booking['end_date'] ?? ''),
            'Travelers' => (string) ($booking['number_of_travelers'] ?? 1),
            'Payment method' => ucfirst($booking['payment_method'] ?? '—'),
            'Payment status' => 'Confirmed',
            'Booking status' => 'Confirmed',
            'Total amount' => '$' . number_format((float) ($booking['final_amount'] ?? 0), 2),
        ];

        $noteBlock = $adminNote !== ''
            ? '<p class="note"><strong>Message from our team:</strong><br>' . nl2br(htmlspecialchars($adminNote)) . '</p>'
            : '';

        $html = $this->wrap(
            'Payment approved — booking confirmed',
            "
            <p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>Thank you for choosing EthioTrip. Your payment has been <strong>verified and approved</strong>, and your booking is now <strong>confirmed</strong>.</p>
            " . $this->detailsTable($rows) . "
            {$noteBlock}
            <p>Please save this email for your records. We look forward to welcoming you to Ethiopia.</p>
            <p><a href='" . $this->appUrl() . "/bookings' class='button'>View my booking</a></p>
            <p class='muted'>" . $this->supportLine() . "</p>
            "
        );

        $text = implode("\n", [
            "Dear {$name},",
            '',
            'Your payment has been approved and your EthioTrip booking is confirmed.',
            '',
            'Booking #' . ($booking['id'] ?? ''),
            'Destination: ' . ($booking['destination'] ?? '—'),
            'Package: ' . ($booking['package_name'] ?? '—'),
            'Dates: ' . ($booking['start_date'] ?? '') . ' to ' . ($booking['end_date'] ?? ''),
            'Status: Confirmed',
            'Total: $' . number_format((float) ($booking['final_amount'] ?? 0), 2),
            '',
            $this->appUrl() . '/bookings',
        ]);

        return [
            'subject' => 'EthioTrip — Payment approved & booking confirmed #' . ($booking['id'] ?? ''),
            'html' => $html,
            'text' => $text,
        ];
    }

    public function bookingConfirmed(array $booking): array
    {
        $name = $booking['user_name'] ?? 'Traveler';
        $adminNote = $this->extractAdminNote($booking['admin_notes'] ?? '', 'Admin note:');
        $rows = [
            'Booking reference' => '#' . ($booking['id'] ?? ''),
            'Destination' => $booking['destination'] ?? '—',
            'Travel package' => $booking['package_name'] ?? '—',
            'Travel dates' => ($booking['start_date'] ?? '') . ' to ' . ($booking['end_date'] ?? ''),
            'Travelers' => (string) ($booking['number_of_travelers'] ?? 1),
            'Booking status' => 'Confirmed',
            'Total amount' => '$' . number_format((float) ($booking['final_amount'] ?? 0), 2),
        ];

        $noteBlock = $adminNote !== ''
            ? '<p class="note"><strong>Message from our team:</strong><br>' . nl2br(htmlspecialchars($adminNote)) . '</p>'
            : '';

        $html = $this->wrap(
            'Your booking is confirmed',
            "
            <p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>Your EthioTrip reservation has been reviewed and <strong>confirmed</strong>. You are all set for your upcoming journey.</p>
            " . $this->detailsTable($rows) . "
            {$noteBlock}
            <p><a href='" . $this->appUrl() . "/bookings' class='button'>View my booking</a></p>
            <p class='muted'>" . $this->supportLine() . "</p>
            "
        );

        $text = "Dear {$name},\n\nYour EthioTrip booking #" . ($booking['id'] ?? '') . " is confirmed.\n\n" . $this->appUrl() . '/bookings';

        return [
            'subject' => 'EthioTrip — Booking confirmed #' . ($booking['id'] ?? ''),
            'html' => $html,
            'text' => $text,
        ];
    }

    public function paymentRejected(array $booking): array
    {
        $name = $booking['user_name'] ?? 'Traveler';
        $reason = $this->extractAdminNote($booking['admin_notes'] ?? '', 'Rejection reason:')
            ?: 'We could not verify the payment details provided.';
        $rows = [
            'Booking reference' => '#' . ($booking['id'] ?? ''),
            'Destination' => $booking['destination'] ?? '—',
            'Travel package' => $booking['package_name'] ?? '—',
            'Payment status' => 'Not approved',
        ];

        $html = $this->wrap(
            'Payment could not be approved',
            "
            <p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>We reviewed your payment for the booking below. Unfortunately, we were unable to approve it at this time.</p>
            " . $this->detailsTable($rows) . "
            <p class='note'><strong>Reason:</strong><br>" . nl2br(htmlspecialchars($reason)) . "</p>
            <p>Please contact our team or submit a new booking with updated payment information.</p>
            <p><a href='" . $this->appUrl() . "/destination' class='button'>Browse destinations</a></p>
            <p class='muted'>" . $this->supportLine() . "</p>
            "
        );

        $text = "Dear {$name},\n\nYour payment for booking #" . ($booking['id'] ?? '') . " could not be approved.\n\nReason: {$reason}";

        return [
            'subject' => 'EthioTrip — Payment update for booking #' . ($booking['id'] ?? ''),
            'html' => $html,
            'text' => $text,
        ];
    }

    private function appUrl(): string
    {
        return rtrim($this->config['app_url'] ?? '', '/');
    }

    private function supportLine(): string
    {
        $email = $this->config['support_email'] ?? MailConfig::fromEmail();
        return 'Questions? Reply to this email or contact <a href="mailto:' . htmlspecialchars($email) . '">' . htmlspecialchars($email) . '</a>.';
    }

    private function detailsTable(array $rows): string
    {
        $html = '<table class="details-table" cellpadding="0" cellspacing="0">';
        foreach ($rows as $label => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            $html .= '<tr><td class="label">' . htmlspecialchars($label) . '</td><td>' . htmlspecialchars((string) $value) . '</td></tr>';
        }
        return $html . '</table>';
    }

    private function extractAdminNote(string $notes, string $prefix): string
    {
        $notes = trim($notes);
        if ($notes === '') {
            return '';
        }
        $pattern = '/' . preg_quote(rtrim($prefix, ':'), '/') . ':\s*(.+?)(?:\n\[|$)/s';
        if (preg_match($pattern, $notes, $m)) {
            return trim($m[1]);
        }
        return '';
    }

    private function wrap(string $headline, string $bodyHtml): string
    {
        $year = date('Y');
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #2d3436; margin: 0; background: #f0f2f5; }
                .wrapper { max-width: 600px; margin: 24px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
                .header { background: linear-gradient(135deg, #2d3436 0%, #3d4f51 100%); padding: 28px 24px; text-align: center; color: #fff; }
                .header h1 { margin: 0 0 6px; font-size: 1.5rem; font-weight: 600; }
                .header .tagline { margin: 0; font-size: 0.85rem; color: #d4af37; letter-spacing: 1px; text-transform: uppercase; }
                .headline { background: #d4af37; color: #2d3436; padding: 14px 24px; font-size: 1.05rem; font-weight: 700; text-align: center; margin: 0; }
                .content { padding: 28px 24px; }
                .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 0.9rem; }
                .details-table td { padding: 10px 12px; border-bottom: 1px solid #eee; vertical-align: top; }
                .details-table .label { color: #636e72; font-weight: 600; width: 42%; }
                .note { background: #fff9e8; border-left: 4px solid #d4af37; padding: 12px 16px; margin: 16px 0; font-size: 0.9rem; }
                .button { display: inline-block; background: #2d3436; color: #fff !important; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: 600; margin-top: 8px; }
                .muted { font-size: 0.85rem; color: #636e72; margin-top: 24px; }
                .footer { background: #f8f9fa; padding: 20px 24px; text-align: center; font-size: 0.75rem; color: #95a5a6; border-top: 1px solid #eee; }
            </style>
        </head>
        <body>
            <div class="wrapper">
                <div class="header">
                    <p class="tagline">EthioTrip</p>
                    <h1>Authentic journeys across Ethiopia</h1>
                </div>
                <p class="headline">{$headline}</p>
                <div class="content">{$bodyHtml}</div>
                <div class="footer">&copy; {$year} EthioTrip Ethiopia. All rights reserved.</div>
            </div>
        </body>
        </html>
        HTML;
    }
}

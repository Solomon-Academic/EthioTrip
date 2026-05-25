<?php
namespace Backend\Services;

class BookingEmailService
{
    private ResendMailer $mailer;

    public function __construct(?ResendMailer $mailer = null)
    {
        $this->mailer = $mailer ?? new ResendMailer();
    }

    /**
     * Send confirmation only after admin approval (payment or booking).
     */
    public function sendApprovalConfirmation(array $booking): bool
    {
        $email = $booking['user_email'] ?? '';
        $name = $booking['user_name'] ?? 'Traveler';
        $bookingId = $booking['id'] ?? '';
        $destination = $booking['destination'] ?? 'Not specified';
        $package = $booking['package_name'] ?? '';
        $start = $booking['start_date'] ?? '';
        $end = $booking['end_date'] ?? '';
        $status = ucfirst($booking['admin_approval_status'] ?? 'approved');
        $total = number_format((float) ($booking['final_amount'] ?? 0), 2);

        $config = require __DIR__ . '/../Config/config.php';
        $appUrl = rtrim($config['app_url'] ?? '', '/');

        $subject = "EthioTrip Booking Confirmed #{$bookingId}";

        $html = $this->wrapTemplate(
            'Your booking is approved!',
            "
            <p>Dear <strong>" . htmlspecialchars($name) . "</strong>,</p>
            <p>Great news — your EthioTrip booking has been <strong style='color:#27ae60;'>APPROVED</strong>.</p>
            <div class='details'>
                <p><strong>Booking ID:</strong> #{$bookingId}</p>
                <p><strong>Destination:</strong> " . htmlspecialchars($destination) . "</p>
                <p><strong>Package:</strong> " . htmlspecialchars($package) . "</p>
                <p><strong>Travel dates:</strong> {$start} to {$end}</p>
                <p><strong>Status:</strong> {$status}</p>
                <p><strong>Total:</strong> \${$total}</p>
            </div>
            <p><a href='{$appUrl}/bookings' class='button'>View My Bookings</a></p>
            "
        );

        $text = implode("\n", [
            "Hello {$name},",
            '',
            'Your EthioTrip booking has been approved.',
            '',
            "Booking ID: #{$bookingId}",
            "Destination: {$destination}",
            "Package: {$package}",
            "Travel dates: {$start} to {$end}",
            "Status: {$status}",
            "Total: \${$total}",
            '',
            "View bookings: {$appUrl}/bookings",
        ]);

        return $this->mailer->send($email, $subject, $html, $text);
    }

    private function wrapTemplate(string $headline, string $bodyHtml): string
    {
        return "
        <html><head><style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #d4af37; padding: 20px; text-align: center; color: #2d3436; }
            .content { padding: 30px; background: #f9f9f9; }
            .details { background: white; padding: 15px; border-radius: 8px; margin: 15px 0; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #777; }
            .button { background: #d4af37; color: #2d3436; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
        </style></head>
        <body>
            <div class='container'>
                <div class='header'><h2>EthioTrip</h2><h3>" . htmlspecialchars($headline) . "</h3></div>
                <div class='content'>{$bodyHtml}</div>
                <div class='footer'><p>&copy; " . date('Y') . " EthioTrip Ethiopia.</p></div>
            </div>
        </body></html>";
    }
}

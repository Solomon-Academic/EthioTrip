<?php
namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\DiscountTier;
use Backend\Models\User;
use Backend\Models\Booking;

class AdminController extends Controller
{
    private DiscountTier $tierModel;
    private User $userModel;
    private Booking $bookingModel;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->tierModel = new DiscountTier();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
    }

    protected function requireAdmin(): array
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            Session::set('return_url', $_SERVER['REQUEST_URI']);
            $this->redirect('/login');
        }

        $user = $this->userModel->findById((int) $userId);
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            $this->redirect('/dashboard');
        }

        return $user;
    }

    public function discountsAction(): void
    {
        $this->requireAdmin();
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireValidCsrf();
            if (isset($_POST['update_tier'])) {
                $this->tierModel->updateTier([
                    'id' => intval($_POST['id']),
                    'min_trips' => intval($_POST['min_trips']),
                    'max_trips' => $_POST['max_trips'] !== '' ? intval($_POST['max_trips']) : null,
                    'discount_percent' => floatval($_POST['discount_percent']),
                    'tier_name' => trim($_POST['tier_name'] ?? ''),
                    'is_active' => intval($_POST['is_active'] ?? 0),
                ]);
                $message = 'Discount tier updated successfully!';
            }

            if (isset($_POST['add_tier'])) {
                $this->tierModel->addTier([
                    'min_trips' => intval($_POST['min_trips']),
                    'max_trips' => $_POST['max_trips'] !== '' ? intval($_POST['max_trips']) : null,
                    'discount_percent' => floatval($_POST['discount_percent']),
                    'tier_name' => trim($_POST['tier_name'] ?? ''),
                ]);
                $message = 'New tier added successfully!';
            }

            if (isset($_POST['adjustment'])) {
                $adjustment = floatval($_POST['adjustment']);
                if ($adjustment !== 0.0) {
                    $this->tierModel->adjustAll($adjustment);
                    $message = sprintf('All active discounts adjusted by %+.1f%%.', $adjustment);
                }
            }
        }

        $tiers = $this->tierModel->findAll();
        $this->render('admin.discounts', [
            'message' => $message,
            'tiers' => $tiers,
        ]);
    }

    // New method: Manage all bookings for admin
    public function bookingsAction(): void
    {
        $this->requireAdmin();
        $message = '';
        
        // Handle approval/rejection
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireValidCsrf();
            $bookingId = intval($_POST['booking_id'] ?? 0);
            $action = $_POST['action'] ?? '';
            $adminNotes = trim($_POST['admin_notes'] ?? '');
            $adminId = Session::getUserId();
            
            if ($bookingId && $action) {
                if ($action === 'approve') {
                    $result = $this->bookingModel->approveBooking($bookingId, $adminId, $adminNotes);
                    $message = $result ? 'Booking approved successfully!' : 'Failed to approve booking.';
                } elseif ($action === 'reject') {
                    $result = $this->bookingModel->rejectBooking($bookingId, $adminId, $adminNotes);
                    $message = $result ? 'Booking rejected successfully!' : 'Failed to reject booking.';
                } elseif ($action === 'approve_payment') {
                    $result = $this->bookingModel->approvePayment($bookingId, $adminId, $adminNotes);
                    if ($result) {
                        $booking = $this->bookingModel->getBookingWithDetails($bookingId);
                        $this->sendPaymentApprovalEmail($booking ?: []);
                    }
                    $message = $result ? 'Payment approved successfully!' : 'Failed to approve payment.';
                } elseif ($action === 'fail_payment') {
                    $result = $this->bookingModel->markPaymentFailed($bookingId, $adminId, $adminNotes);
                    $message = $result ? 'Payment marked as failed.' : 'Failed to update payment.';
                }
            }
        }
        
        // Get filter parameters
        $filter = $_GET['filter'] ?? 'payment_pending';
        $search = $_GET['search'] ?? '';
        
        $bookings = $this->bookingModel->getAllBookingsForAdmin($filter, $search);
        $stats = $this->bookingModel->getBookingStats();
        
        $this->render('admin.bookings', [
            'message' => $message,
            'bookings' => $bookings,
            'stats' => $stats,
            'currentFilter' => $filter,
            'searchTerm' => $search,
        ]);
    }
    
    // View single booking details
    public function viewBookingAction(): void
    {
        $this->requireAdmin();
        $bookingId = intval($_GET['id'] ?? 0);
        
        if (!$bookingId) {
            $this->redirect('/admin/bookings');
        }
        
        $booking = $this->bookingModel->getBookingWithDetails($bookingId);
        
        if (!$booking) {
            $this->redirect('/admin/bookings');
        }
        
        $this->render('admin.booking_view', [
            'booking' => $booking,
        ]);
    }
    
    // Dashboard stats for admin
    public function dashboardAction(): void
    {
        $this->requireAdmin();
        
        $stats = [
            'total_bookings' => $this->bookingModel->getTotalBookingsCount(),
            'pending_approvals' => $this->bookingModel->getPendingApprovalsCount(),
            'total_users' => $this->userModel->getTotalUsersCount(),
            'total_revenue' => $this->bookingModel->getTotalRevenue(),
            'recent_bookings' => $this->bookingModel->getRecentBookings(5),
        ];
        
        $this->render('admin.dashboard', ['stats' => $stats]);
    }

    private function sendPaymentApprovalEmail(array $booking): bool
    {
        $email = $booking['user_email'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $name = $booking['user_name'] ?? 'Traveler';
        $subject = 'EthioTrip payment approved for booking #' . ($booking['id'] ?? '');
        $message = implode("\n", [
            'Hello ' . $name . ',',
            '',
            'Your payment has been approved and your EthioTrip booking is now confirmed.',
            '',
            'Booking ID: #' . ($booking['id'] ?? ''),
            'Package: ' . ($booking['package_name'] ?? ''),
            'Destination: ' . ($booking['destination'] ?? 'Not specified'),
            'Travel Dates: ' . ($booking['start_date'] ?? '') . ' to ' . ($booking['end_date'] ?? ''),
            'Total: $' . number_format(floatval($booking['final_amount'] ?? 0), 2),
            '',
            'Thank you for choosing EthioTrip.',
            '',
            'EthioTrip Team',
        ]);

        $headers = implode("\r\n", [
            'From: EthioTrip <no-reply@ethiotrip.local>',
            'Reply-To: no-reply@ethiotrip.local',
            'Content-Type: text/plain; charset=UTF-8',
        ]);

        return @mail($email, $subject, $message, $headers);
    }
}

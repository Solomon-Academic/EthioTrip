<?php
namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\DiscountTier;
use Backend\Models\User;
use Backend\Models\Booking;
use Backend\Models\Destination;
use Backend\Models\Package;
use Backend\Services\BookingEmailService;

class AdminController extends Controller
{
    private DiscountTier $tierModel;
    private User $userModel;
    private Booking $bookingModel;
    private Destination $destinationModel;
    private Package $packageModel;
    private BookingEmailService $emailService;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->tierModel = new DiscountTier();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
        $this->destinationModel = new Destination();
        $this->packageModel = new Package();
        $this->emailService = new BookingEmailService();
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

    // SINGLE UNIFIED DASHBOARD
    public function dashboardAction(): void
    {
        $this->requireAdmin();
        
        // Get all stats for admin dashboard
        $totalBookings = $this->bookingModel->getTotalBookingsCount();
        $pendingApprovals = $this->bookingModel->getPendingApprovalsCount();
        $pendingPayments = $this->bookingModel->getPendingPaymentsCount();
        $totalUsers = $this->userModel->getTotalUsersCount();
        $totalRevenue = $this->bookingModel->getTotalRevenue();
        $recentBookings = $this->bookingModel->getRecentBookings(10);
        
        // Get destination and package counts
        $totalDestinations = $this->destinationModel->getTotalCount();
        $totalPackages = $this->packageModel->getTotalCount();
        
        $stats = $this->bookingModel->getBookingStats();
        
        $this->render('admin.dashboard', [
            'totalBookings' => $totalBookings,
            'pendingApprovals' => $pendingApprovals,
            'pendingPayments' => $pendingPayments,
            'totalUsers' => $totalUsers,
            'totalRevenue' => $totalRevenue,
            'totalDestinations' => $totalDestinations,
            'totalPackages' => $totalPackages,
            'recentBookings' => $recentBookings,
            'stats' => $stats,
        ]);
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

    public function bookingsAction(): void
    {
        $this->requireAdmin();
        $message = '';
        $messageType = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireValidCsrf();
            $bookingId = intval($_POST['booking_id'] ?? 0);
            $action = $_POST['action'] ?? '';
            $adminNotes = trim($_POST['admin_notes'] ?? '');
            $adminId = Session::getUserId();
            
            if ($bookingId && $action) {
                if ($action === 'approve') {
                    $result = $this->bookingModel->approveBooking($bookingId, $adminId, $adminNotes);
                    if ($result) {
                        $booking = $this->bookingModel->getBookingWithDetails($bookingId);
                        [$message, $messageType] = $this->notifyAfterApproval(
                            $bookingId,
                            $booking ?: [],
                            $this->emailService->sendBookingApprovedNotification($booking ?: [])
                        );
                    } else {
                        $message = 'Unable to approve this booking. Please try again.';
                        $messageType = 'error';
                    }
                } elseif ($action === 'reject') {
                    $result = $this->bookingModel->rejectBooking($bookingId, $adminId, $adminNotes);
                    $message = $result
                        ? 'Booking has been rejected. The customer can view the updated status in their account.'
                        : 'Unable to reject this booking. Please try again.';
                    $messageType = $result ? 'warning' : 'error';
                } elseif ($action === 'approve_payment') {
                    $result = $this->bookingModel->approvePayment($bookingId, $adminId, $adminNotes);
                    if ($result) {
                        $booking = $this->bookingModel->getBookingWithDetails($bookingId);
                        [$message, $messageType] = $this->notifyAfterApproval(
                            $bookingId,
                            $booking ?: [],
                            $this->emailService->sendPaymentApprovedNotification($booking ?: []),
                            true
                        );
                    } else {
                        $message = 'Unable to approve this payment. Please try again.';
                        $messageType = 'error';
                    }
                } elseif ($action === 'fail_payment') {
                    $result = $this->bookingModel->markPaymentFailed($bookingId, $adminId, $adminNotes);
                    if ($result) {
                        $booking = $this->bookingModel->getBookingWithDetails($bookingId);
                        $notify = $this->emailService->sendPaymentRejectedNotification($booking ?: []);
                        if ($notify['sent']) {
                            $this->bookingModel->markCustomerNotified($bookingId, 'payment_rejected');
                        }
                        [$message, $messageType] = $this->formatNotificationFeedback(
                            $notify,
                            'Payment was not approved. The customer has been notified by email.',
                            'Payment was not approved. Notification email could not be delivered.'
                        );
                    } else {
                        $message = 'Unable to update payment status. Please try again.';
                        $messageType = 'error';
                    }
                }
            }
        }
        
        $filter = $_GET['filter'] ?? 'payment_pending';
        $search = $_GET['search'] ?? '';
        
        $bookings = $this->bookingModel->getAllBookingsForAdmin($filter, $search);
        $stats = $this->bookingModel->getBookingStats();
        
        $this->render('admin.bookings', [
            'message' => $message,
            'messageType' => $messageType,
            'bookings' => $bookings,
            'stats' => $stats,
            'currentFilter' => $filter,
            'searchTerm' => $search,
        ]);
    }
    
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

    /**
     * @param array{sent: bool, skipped: bool, reason: string, email: string} $notify
     * @return array{0: string, 1: string} message and type
     */
    private function notifyAfterApproval(int $bookingId, array $booking, array $notify, bool $isPayment = false): array
    {
        if ($notify['sent']) {
            $this->bookingModel->markCustomerNotified(
                $bookingId,
                $isPayment ? 'payment_approved' : 'booking_approved'
            );
        }

        $successLead = $isPayment
            ? 'Payment approved successfully. The booking is now confirmed.'
            : 'Booking approved successfully.';

        $sentMessage = $successLead . ' A confirmation email was sent to ' . ($notify['email'] ?: 'the customer') . '.';

        return $this->formatNotificationFeedback($notify, $sentMessage, $successLead);
    }

    /**
     * @param array{sent: bool, skipped: bool, reason: string, email: string} $notify
     * @return array{0: string, 1: string}
     */
    private function formatNotificationFeedback(array $notify, string $sentMessage, string $partialMessage): array
    {
        if ($notify['sent']) {
            return [$sentMessage, 'success'];
        }

        if ($notify['skipped']) {
            return [
                $partialMessage . ' No automated email was sent: ' . $notify['reason'],
                'warning',
            ];
        }

        return [
            $partialMessage . ' Email could not be sent: ' . ($notify['reason'] ?: 'Please check Gmail SMTP settings in .env.'),
            'warning',
        ];
    }
}
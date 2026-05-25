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
                        $emailOk = $this->emailService->sendApprovalConfirmation($booking ?: []);
                        $message = $emailOk
                            ? 'Booking approved successfully! Confirmation email sent via Resend.'
                            : 'Booking approved. Email could not be sent (check Resend API key).';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to approve booking.';
                        $messageType = 'error';
                    }
                } elseif ($action === 'reject') {
                    $result = $this->bookingModel->rejectBooking($bookingId, $adminId, $adminNotes);
                    $message = $result ? 'Booking rejected successfully!' : 'Failed to reject booking.';
                    $messageType = $result ? 'warning' : 'error';
                } elseif ($action === 'approve_payment') {
                    $result = $this->bookingModel->approvePayment($bookingId, $adminId, $adminNotes);
                    if ($result) {
                        $booking = $this->bookingModel->getBookingWithDetails($bookingId);
                        $emailOk = $this->emailService->sendApprovalConfirmation($booking ?: []);
                        $message = $emailOk
                            ? 'Payment approved successfully! Confirmation email sent via Resend.'
                            : 'Payment approved. Email could not be sent (check Resend API key).';
                        $messageType = 'success';
                    } else {
                        $message = 'Failed to approve payment.';
                        $messageType = 'error';
                    }
                } elseif ($action === 'fail_payment') {
                    $result = $this->bookingModel->markPaymentFailed($bookingId, $adminId, $adminNotes);
                    $message = $result ? 'Payment marked as failed.' : 'Failed to update payment.';
                    $messageType = $result ? 'warning' : 'error';
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

}
<?php
namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\User;
use Backend\Models\Booking;
use Backend\Models\DiscountTier;
use Backend\Models\Package;
use Backend\Models\UserDestination;

class ApiController extends Controller {
    
    private $userModel;
    private $bookingModel;
    private $discountTierModel;
    private $userDestinationModel;
    private $packageModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
        $this->discountTierModel = new DiscountTier();
        $this->userDestinationModel = new UserDestination();
        $this->packageModel = new Package();
    }
    
    public function checkLogin() {
        $this->json([
            'logged_in' => Session::isLoggedIn(),
            'user_id' => Session::getUserId(),
            'user_name' => Session::getUserName(),
            'user_role' => Session::isAdmin() ? 'admin' : 'user'
        ]);
    }
    
    public function getLoyaltyDiscount() {
        $name = $_GET['name'] ?? '';
        $userId = Session::getUserId();
        
        if ($userId) {
            $user = $this->userModel->find($userId);
        } elseif ($name) {
            $users = $this->userModel->where('name', $name);
            $user = $users[0] ?? null;
        } else {
            $this->json(['success' => false, 'discount_decimal' => 0]);
            return;
        }
        
        if (!$user) {
            $this->json([
                'success' => false,
                'discount_decimal' => 0,
                'discount_percent' => 0,
                'trips_completed' => 0,
                'total_spent' => 0,
                'tier_name' => 'Bronze'
            ]);
            return;
        }
        
        $discountDecimal = floatval($user['loyalty_discount']);
        $discountPercent = $discountDecimal * 100;
        
        $tier = $this->discountTierModel->findActiveTierByTrips($user['trips_completed']);
        
        $this->json([
            'success' => true,
            'user_id' => $user['id'],
            'name' => $user['name'],
            'trips_completed' => $user['trips_completed'],
            'total_spent' => floatval($user['total_spent']),
            'discount_percent' => $discountPercent,
            'discount_decimal' => $discountDecimal,
            'tier_name' => $tier['tier_name'] ?? 'Bronze'
        ]);
    }
    
    public function getNextTier() {
        $trips = intval($_GET['trips'] ?? 0);
        $nextTier = $this->discountTierModel->findNextTier($trips);
        
        if ($nextTier) {
            $this->json([
                'success' => true,
                'trips_needed' => $nextTier['min_trips'] - $trips,
                'tier_name' => $nextTier['tier_name'],
                'discount_percent' => $nextTier['discount_percent']
            ]);
        } else {
            $this->json(['success' => false]);
        }
    }
    
    public function saveBooking() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $this->json(['success' => false, 'message' => 'Invalid booking payload.'], 400);
        }
        
        $packageName = trim($input['package_name'] ?? '');
        $destination = trim($input['destination'] ?? '');
        $startDate = trim($input['start_date'] ?? '');
        $endDate = trim($input['end_date'] ?? '');
        $travelers = intval($input['travelers'] ?? 1);
        $paymentMethod = trim($input['payment_method'] ?? 'cash');
        $userName = trim($input['user_name'] ?? '');

        if ($packageName === '' || $startDate === '' || $endDate === '' || $userName === '') {
            $this->json(['success' => false, 'message' => 'Package, dates, and traveler name are required.'], 422);
        }

        $package = $this->packageModel->findByName($packageName);
        if (!$package) {
            $this->json(['success' => false, 'message' => 'Selected package was not found.'], 422);
        }

        $start = \DateTime::createFromFormat('Y-m-d', $startDate);
        $end = \DateTime::createFromFormat('Y-m-d', $endDate);
        if (!$start || !$end || $start >= $end) {
            $this->json(['success' => false, 'message' => 'End date must be after start date.'], 422);
        }

        $durationDays = (int) $start->diff($end)->days;
        if ($durationDays < 1 || $durationDays > 30) {
            $this->json(['success' => false, 'message' => 'Trip duration must be between 1 and 30 days.'], 422);
        }

        if ($travelers < 1 || $travelers > 20) {
            $this->json(['success' => false, 'message' => 'Travelers must be between 1 and 20.'], 422);
        }

        $packagePrice = floatval($package['price']);
        $transactionId = 'ET-' . date('YmdHis') . '-' . random_int(1000, 9999);
        
        $userId = Session::getUserId();
        if (!$userId) {
            $users = $this->userModel->where('name', $userName);
            if (count($users) > 0) {
                $userId = $users[0]['id'];
            } else {
                $guestEmail = 'guest+' . substr(hash('sha256', $userName . microtime(true)), 0, 16) . '@ethiotrip.local';
                $userId = $this->userModel->create([
                    'name' => $userName,
                    'email' => $guestEmail,
                    'password' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                    'phone' => '0000000000',
                    'role' => 'user'
                ]);
            }
        }
        
        $user = $this->userModel->find($userId);
        $discountRate = floatval($user['loyalty_discount']);
        
        $dailyRate = $packagePrice / 3;
        $subtotal = $dailyRate * $durationDays * $travelers;
        $discountAmount = $subtotal * $discountRate;
        $totalAfterDiscount = $subtotal - $discountAmount;
        $tax = $totalAfterDiscount * 0.10;
        $grandTotal = $totalAfterDiscount + $tax;
        
        $bookingId = $this->bookingModel->create([
            'user_id' => $userId,
            'package_id' => (int) $package['id'],
            'package_name' => $packageName,
            'destination' => $destination,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $durationDays,
            'number_of_travelers' => $travelers,
            'total_amount' => $subtotal,
            'discount_amount' => $discountAmount,
            'final_amount' => $grandTotal,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'payment_status' => 'pending',
            'status' => 'pending',
            'special_requests' => ''
        ]);
        
        if ($bookingId) {
            $this->json([
                'success' => true,
                'booking_id' => $bookingId,
                'transaction_id' => $transactionId,
                'trips_completed' => intval($user['trips_completed']),
                'total_spent' => floatval($user['total_spent']),
                'final_amount' => $grandTotal,
                'discount_percent' => $discountRate * 100,
                'payment_status' => 'pending',
                'status' => 'pending'
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to save booking']);
        }
    }
}
?>

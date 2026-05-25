<?php
namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\User;
use Backend\Models\Booking;
use Backend\Models\DiscountTier;
use Backend\Models\Package;
use Backend\Models\Destination;
use Backend\Models\DestinationHighlight;
use Backend\Models\DestinationAttraction;
use Backend\Models\UserDestination;

class ApiController extends Controller {
    
    private User $userModel;
    private Booking $bookingModel;
    private DiscountTier $discountTierModel;
    private UserDestination $userDestinationModel;
    private Package $packageModel;
    private Destination $destinationModel;
    private DestinationHighlight $highlightModel;
    private DestinationAttraction $attractionModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
        $this->discountTierModel = new DiscountTier();
        $this->userDestinationModel = new UserDestination();
        $this->packageModel = new Package();
        $this->destinationModel = new Destination();
        $this->highlightModel = new DestinationHighlight();
        $this->attractionModel = new DestinationAttraction();
    }
    
    public function checkLogin() {
        $userEmail = Session::getUserEmail();
        $userId = Session::getUserId();

        if ($userId && $userEmail === '') {
            $user = $this->userModel->find($userId);
            $userEmail = $user['email'] ?? '';
            if ($userEmail !== '') {
                Session::set('user_email', $userEmail);
            }
        }

        $this->json([
            'logged_in' => Session::isLoggedIn(),
            'user_id' => $userId,
            'user_name' => Session::getUserName(),
            'user_email' => $userEmail,
            'user_role' => Session::isAdmin() ? 'admin' : 'user'
        ]);
    }

    public function listDestinations(): void {
        $result = $this->destinationModel->findAllActive();
        $items = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $items[] = $this->destinationModel->toListItem($row);
            }
        }
        $this->json(['success' => true, 'destinations' => $items]);
    }

    public function getDestination(): void {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['success' => false, 'message' => 'Destination id is required.'], 422);
        }

        $destination = $this->destinationModel->findActiveById($id);
        if (!$destination) {
            $this->json(['success' => false, 'message' => 'Destination not found.'], 404);
        }

        $highlights = $this->highlightModel->findByDestination($id);
        if (empty($highlights) && !empty($destination['churches'])) {
            foreach (preg_split('/\r?\n/', trim($destination['churches'])) as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $highlights[] = ['title' => $line, 'description' => ''];
                }
            }
        }

        $packages = array_map(
            fn($p) => $this->packageModel->toApiItem($p),
            $this->packageModel->findAllActiveArray()
        );

        $this->json([
            'success' => true,
            'destination' => [
                'id' => (int) $destination['id'],
                'name' => $destination['name'],
                'location' => $destination['location'] ?? '',
                'description' => $destination['description'] ?? '',
                'short_description' => $destination['short_description'] ?? '',
                'travel_guide' => $destination['travel_guide'] ?? '',
                'best_time' => $destination['best_time'] ?? '',
                'price' => (float) ($destination['price'] ?? 0),
                'activities' => $destination['activities'] ?? '',
                'image_url' => $this->destinationModel->resolveImageUrl($destination),
                'highlights' => $highlights,
                'attractions' => $this->attractionModel->findByDestination($id),
                'packages' => $packages,
            ],
        ]);
    }

    public function listPackages(): void {
        $destinationId = (int) ($_GET['destination_id'] ?? 0);
        if ($destinationId <= 0) {
            $this->json(['success' => false, 'message' => 'destination_id is required. Select a destination first.'], 422);
        }

        $destination = $this->destinationModel->findActiveById($destinationId);
        if (!$destination) {
            $this->json(['success' => false, 'message' => 'Destination not found.'], 404);
        }

        // All active packages are available for every destination (destination sets trip context only).
        $packages = array_map(
            fn($p) => $this->packageModel->toApiItem($p),
            $this->packageModel->findAllActiveArray()
        );

        $this->json([
            'success' => true,
            'destination' => [
                'id' => (int) $destination['id'],
                'name' => $destination['name'],
                'location' => $destination['location'] ?? '',
            ],
            'packages' => $packages,
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
        $packageId = (int) ($input['package_id'] ?? 0);
        $destination = trim($input['destination'] ?? '');
        $destinationId = (int) ($input['destination_id'] ?? 0);
        $startDate = trim($input['start_date'] ?? '');
        $endDate = trim($input['end_date'] ?? '');
        $travelers = intval($input['travelers'] ?? 1);
        $paymentMethod = trim($input['payment_method'] ?? 'cash');
        $userName = trim($input['user_name'] ?? '');

        if ($packageName === '' || $startDate === '' || $endDate === '') {
            $this->json(['success' => false, 'message' => 'Package and travel dates are required.'], 422);
        }

        $package = $packageId > 0
            ? $this->packageModel->findById($packageId)
            : $this->packageModel->findByName($packageName);

        if (!$package || !(int) ($package['is_active'] ?? 0)) {
            $this->json(['success' => false, 'message' => 'Selected package was not found.'], 422);
        }

        if ($destinationId > 0) {
            $destRow = $this->destinationModel->findActiveById($destinationId);
            if ($destRow) {
                $destination = $destRow['name'];
            }
        }

        if ($destination === '') {
            $this->json(['success' => false, 'message' => 'Destination is required.'], 422);
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
            if ($userName === '') {
                $this->json(['success' => false, 'message' => 'Please sign in before completing a booking.'], 401);
            }

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
        if (!$user) {
            $this->json(['success' => false, 'message' => 'Signed-in user was not found.'], 401);
        }

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
            'package_name' => $package['name'],
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
            'special_requests' => trim($input['special_requests'] ?? '')
        ]);
        
        if ($bookingId) {
            $this->json([
                'success' => true,
                'booking_id' => $bookingId,
                'transaction_id' => $transactionId,
                'user_email' => $user['email'] ?? '',
                'trips_completed' => intval($user['trips_completed']),
                'total_spent' => floatval($user['total_spent']),
                'final_amount' => $grandTotal,
                'discount_percent' => $discountRate * 100,
                'payment_status' => 'pending',
                'status' => 'pending',
                'message' => 'Booking submitted. You will receive a confirmation email after admin approval.',
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to save booking']);
        }
    }
}

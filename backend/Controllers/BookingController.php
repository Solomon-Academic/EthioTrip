<?php

namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\Booking;
use Backend\Models\Destination;
use Backend\Models\DiscountTier;
use Backend\Models\Package;
use Backend\Models\User;
use Backend\Models\UserDestination;

class BookingController extends Controller{
    private Booking $bookingModel;
    private Destination $destinationModel;
    private Package $packageModel;
    private DiscountTier $tierModel;
    private User $userModel;
    private UserDestination $userDestinationModel;

    public function __construct(){
        parent::__construct();
        Session::start();
        $this->bookingModel = new Booking();
        $this->destinationModel = new Destination();
        $this->packageModel = new Package();
        $this->tierModel = new DiscountTier();
        $this->userModel = new User();
        $this->userDestinationModel = new UserDestination();
    }

    protected function requireLogin(): ?array
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            Session::set('return_url', $_SERVER['REQUEST_URI']);
            $this->redirect('/login');
        }

        return $this->userModel->findById($userId);
    }

    public function indexAction(): void
    {
        $user = $this->requireLogin();
        $isAdmin = ($user['role'] ?? 'user') === 'admin';
        $bookings = $this->bookingModel->findAllByUser((int) $user['id'], $isAdmin);

        $stats = [
            'total_bookings' => 0,
            'total_spent' => 0.0,
            'active_bookings' => 0,
            'destinations' => [],
        ];

        if ($bookings instanceof \mysqli_result && $bookings->num_rows > 0) {
            while ($row = $bookings->fetch_assoc()) {
                $stats['total_bookings']++;
                $stats['total_spent'] += floatval($row['final_amount']);
                if (($row['status'] ?? '') === 'confirmed') {
                    $stats['active_bookings']++;
                }
                if (!empty($row['destination'])) {
                    $stats['destinations'][$row['destination']] = true;
                }
            }
            $bookings->data_seek(0);
        }

        $this->render('bookings.index', [
            'user' => $user,
            'bookings' => $bookings,
            'stats' => $stats,
        ]);
    }

    public function createAction(): void
    {
        $user = $this->requireLogin();
        $errors = [];
        $destinations = $this->destinationModel->findAllActive();
        $packages = $this->packageModel->findAllActive();
        $discountRate = floatval($user['loyalty_discount'] ?? 0);
        $discountPercent = $discountRate * 100;
        $form = [
            'destination_id' => '',
            'package_id' => '',
            'start_date' => '',
            'end_date' => '',
            'travelers' => 1,
            'special_requests' => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireValidCsrf();
            $form['destination_id'] = $_POST['destination_id'] ?? '';
            $form['package_id'] = $_POST['package_id'] ?? '';
            $form['start_date'] = $_POST['start_date'] ?? '';
            $form['end_date'] = $_POST['end_date'] ?? '';
            $form['travelers'] = max(1, min(20, intval($_POST['travelers'] ?? 1)));
            $form['special_requests'] = trim($_POST['special_requests'] ?? '');

            if ($form['destination_id'] === '') {
                $errors['destination'] = 'Please select a destination first.';
            }
            if ($form['package_id'] === '') {
                $errors['package'] = 'Please select a package.';
            }
            if ($form['start_date'] === '') {
                $errors['start_date'] = 'Start date is required.';
            }
            if ($form['end_date'] === '') {
                $errors['end_date'] = 'End date is required.';
            }
            if (!empty($form['start_date']) && !empty($form['end_date'])) {
                $start = strtotime($form['start_date']);
                $end = strtotime($form['end_date']);
                $durationDays = ceil(abs($end - $start) / 86400);
                if ($start >= $end) {
                    $errors['end_date'] = 'End date must be after start date.';
                } elseif ($durationDays < 1) {
                    $errors['end_date'] = 'Trip must be at least 1 day.';
                } elseif ($durationDays > 30) {
                    $errors['end_date'] = 'Trip cannot exceed 30 days.';
                }
            }

            if (empty($errors)) {
                $destination = $this->destinationModel->find(intval($form['destination_id']));
                if (!$destination || empty($destination['is_active'])) {
                    $errors['destination'] = 'Selected destination was not found.';
                }
            }

            if (empty($errors)) {
                $package = $this->packageModel->findById(intval($form['package_id']));
                if (!$package || empty($package['is_active'])) {
                    $errors['package'] = 'Selected package was not found.';
                }
            }

            if (empty($errors)) {
                $durationDays = ceil((strtotime($form['end_date']) - strtotime($form['start_date'])) / 86400);
                $dailyRate = $package['price'] / 3;
                $subtotal = $dailyRate * $durationDays * $form['travelers'];
                $discountAmount = $subtotal * $discountRate;
                $totalAfterDiscount = $subtotal - $discountAmount;
                $tax = $totalAfterDiscount * 0.10;
                $grandTotal = $totalAfterDiscount + $tax;

                $insertData = [
                    'user_id' => (int) $user['id'],
                    'package_id' => (int) $package['id'],
                    'package_name' => $package['name'],
                    'destination' => $destination['name'],
                    'start_date' => $form['start_date'],
                    'end_date' => $form['end_date'],
                    'duration_days' => $durationDays,
                    'number_of_travelers' => $form['travelers'],
                    'total_amount' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'final_amount' => $grandTotal,
                    'payment_method' => 'offline',
                    'transaction_id' => 'TXN-' . time(),
                    'payment_status' => 'pending',
                    'status' => 'pending',
                    'special_requests' => $form['special_requests'],
                ];

                if ($this->bookingModel->create($insertData)) {
                    $this->redirect('/bookings');
                }

                $errors['general'] = 'Failed to create booking. Please try again.';
            }
        }

        $this->render('bookings.create', [
            'user' => $user,
            'destinations' => $destinations,
            'packages' => $packages,
            'discountPercent' => $discountPercent,
            'errors' => $errors,
            'form' => $form,
        ]);
    }

    public function editAction(): void
    {
        $user = $this->requireLogin();
        $bookingId = intval($_GET['id'] ?? 0);
        $booking = $this->bookingModel->findByUserAndId($bookingId, (int) $user['id']);

        if (!$booking) {
            $this->redirect('/bookings');
        }

        $errors = [];
        $form = [
            'start_date' => $booking['start_date'],
            'end_date' => $booking['end_date'],
            'travelers' => $booking['number_of_travelers'],
            'special_requests' => $booking['special_requests'] ?? '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->requireValidCsrf();
            $form['start_date'] = $_POST['start_date'] ?? '';
            $form['end_date'] = $_POST['end_date'] ?? '';
            $form['travelers'] = max(1, min(20, intval($_POST['travelers'] ?? 1)));
            $form['special_requests'] = trim($_POST['special_requests'] ?? '');

            if ($form['start_date'] === '') {
                $errors['start_date'] = 'Start date is required.';
            }
            if ($form['end_date'] === '') {
                $errors['end_date'] = 'End date is required.';
            }
            if (!empty($form['start_date']) && !empty($form['end_date'])) {
                $start = strtotime($form['start_date']);
                $end = strtotime($form['end_date']);
                $durationDays = ceil(abs($end - $start) / 86400);
                if ($start >= $end) {
                    $errors['end_date'] = 'End date must be after start date.';
                } elseif ($durationDays < 1) {
                    $errors['end_date'] = 'Trip must be at least 1 day.';
                } elseif ($durationDays > 30) {
                    $errors['end_date'] = 'Trip cannot exceed 30 days.';
                }
            }

            if ($form['travelers'] < 1 || $form['travelers'] > 20) {
                $errors['travelers'] = 'Number of travelers must be between 1 and 20.';
            }

            if (empty($errors)) {
                $updatedData = [
                    'start_date' => $form['start_date'],
                    'end_date' => $form['end_date'],
                    'duration_days' => $durationDays,
                    'number_of_travelers' => $form['travelers'],
                    'special_requests' => $form['special_requests'],
                    'user_id' => (int) $user['id'],
                ];

                if ($this->bookingModel->update($bookingId, $updatedData)) {
                    $this->redirect('/bookings');
                }

                $errors['general'] = 'Unable to update this booking.';
            }
        }

        $this->render('bookings.edit', [
            'user' => $user,
            'booking' => $booking,
            'errors' => $errors,
            'form' => $form,
        ]);
    }

    public function deleteAction(): void
    {
        $user = $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/bookings');
        }
        $this->requireValidCsrf();
        $bookingId = intval($_GET['id'] ?? 0);

        if ($bookingId > 0) {
            $this->bookingModel->cancel($bookingId, (int) $user['id']);
        }

        $this->redirect('/bookings');
    }

    public function saveBookingAction(): void
    {
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $packageName = trim($input['package_name'] ?? '');
        $packagePrice = floatval($input['package_price'] ?? 0);
        $destination = trim($input['destination'] ?? '');
        $startDate = trim($input['start_date'] ?? '');
        $endDate = trim($input['end_date'] ?? '');
        $travelers = max(1, intval($input['travelers'] ?? 1));
        $paymentMethod = trim($input['payment_method'] ?? 'credit_card');
        $transactionId = trim($input['transaction_id'] ?? 'TXN-' . time());
        $finalAmount = floatval($input['final_amount'] ?? $packagePrice);
        $userName = trim($input['user_name'] ?? '');

        if ($packageName === '') {
            echo json_encode(['success' => false, 'message' => 'Package name is required']);
            return;
        }
        if ($startDate === '' || $endDate === '') {
            echo json_encode(['success' => false, 'message' => 'Travel dates are required']);
            return;
        }

        $userId = null;
        $tripsBefore = 0;
        $totalSpentBefore = 0.0;
        $loyaltyDiscount = 0.0;

        if (Session::get('user_id')) {
            $userId = (int) Session::get('user_id');
            $user = $this->userModel->findById($userId);
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'User not found']);
                return;
            }
            $tripsBefore = intval($user['trips_completed']);
            $totalSpentBefore = floatval($user['total_spent']);
            $userName = $user['name'];
        } else {
            if ($userName === '') {
                echo json_encode(['success' => false, 'message' => 'User name is required']);
                return;
            }

            $user = $this->userModel->findByName($userName);
            if ($user) {
                $userId = intval($user['id']);
                $tripsBefore = intval($user['trips_completed']);
                $totalSpentBefore = floatval($user['total_spent']);
            } else {
                $tempEmail = strtolower(str_replace(' ', '', $userName)) . '@ethiotrip.com';
                $tempPassword = password_hash('changeme123', PASSWORD_DEFAULT);
                $tempPhone = '0000000000';

                $this->userModel->create([
                    'name' => $userName,
                    'email' => $tempEmail,
                    'password' => $tempPassword,
                    'phone' => $tempPhone,
                    'role' => 'user',
                ]);

                $userId = $this->userModel->getLastInsertId();
                $tripsBefore = 0;
                $totalSpentBefore = 0.0;
            }
        }

        $dailyRate = $packagePrice / 3;
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $durationDays = (int) $start->diff($end)->days;
        $subtotal = $dailyRate * $durationDays * $travelers;

        $tier = $this->tierModel->findActiveTierByTrips($tripsBefore);
        $discountRate = $tier ? floatval($tier['discount_percent']) / 100 : 0;
        $discountAmount = $subtotal * $discountRate;
        $totalAfterDiscount = $subtotal - $discountAmount;
        $tax = $totalAfterDiscount * 0.10;
        $grandTotal = $totalAfterDiscount + $tax;

        $bookingData = [
            'user_id' => $userId,
            'package_id' => 0,
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
            'payment_status' => 'completed',
            'status' => 'confirmed',
            'special_requests' => '',
        ];

        if (!$this->bookingModel->create($bookingData)) {
            echo json_encode(['success' => false, 'message' => 'Failed to save booking']);
            return;
        }

        $newTrips = $tripsBefore + 1;
        $newTotalSpent = $totalSpentBefore + $grandTotal;
        $this->userModel->updateStats($userId, $newTrips, $newTotalSpent);

        $newTier = $this->tierModel->findActiveTierByTrips($newTrips);
        $newDiscount = $newTier ? floatval($newTier['discount_percent']) / 100 : 0;
        $this->userModel->updateLoyaltyDiscount($userId, $newDiscount);

        if ($destination !== '') {
            $existing = $this->userDestinationModel->findByUserAndDestination($userId, $destination);
            if ($existing) {
                $this->userDestinationModel->updateVisit($existing['id'], intval($existing['visit_count']) + 1, $startDate);
            } else {
                $this->userDestinationModel->create($userId, $destination, $startDate);
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Booking saved successfully!',
            'trips_completed' => $newTrips,
            'total_spent' => $newTotalSpent,
            'final_amount' => $grandTotal,
            'discount_percent' => $discountRate * 100,
        ]);
    }

    public function getLoyaltyDiscountAction(): void
    {
        header('Content-Type: application/json');
        $name = trim($_GET['name'] ?? '');

        $user = null;
        if (Session::get('user_id')) {
            $user = $this->userModel->findById((int) Session::get('user_id'));
        } elseif ($name !== '') {
            $user = $this->userModel->findByName($name);
        }

        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'New user - no trips completed yet',
                'discount_decimal' => 0,
                'discount_percent' => 0,
                'trips_completed' => 0,
                'total_spent' => 0,
                'tier_name' => 'Bronze'
            ]);
            return;
        }

        $trips = intval($user['trips_completed']);
        $discountDecimal = floatval($user['loyalty_discount']);
        $discountPercent = $discountDecimal * 100;
        $tier = $this->tierModel->findActiveTierByTrips($trips);

        echo json_encode([
            'success' => true,
            'user_id' => $user['id'],
            'name' => $user['name'],
            'trips_completed' => $trips,
            'total_spent' => floatval($user['total_spent']),
            'discount_percent' => $discountPercent,
            'discount_decimal' => $discountDecimal,
            'tier_name' => $tier['tier_name'] ?? 'Bronze'
        ]);
    }

    public function getNextTierAction(): void
    {
        header('Content-Type: application/json');
        $trips = intval($_GET['trips'] ?? 0);
        $next = $this->tierModel->findNextTier($trips);

        if (!$next) {
            echo json_encode(['success' => false, 'message' => 'No next tier available']);
            return;
        }

        echo json_encode([
            'success' => true,
            'trips_needed' => intval($next['min_trips']) - $trips,
            'tier_name' => $next['tier_name'],
            'discount_percent' => floatval($next['discount_percent'])
        ]);
    }
}

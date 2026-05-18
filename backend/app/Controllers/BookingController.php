<?php
namespace App\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Models\Database;

class BookingController {

    public static function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['status' => 'idle'];
        }

        AuthController::requireLogin();

        // Validate CSRF
        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return [
                'status' => 'error',
                'message' => 'Security validation failed'
            ];
        }

        $user_id = $_SESSION['user_id'];
        $errors = [];

        $package_id = intval($_POST['package_id'] ?? 0);
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $travelers = intval($_POST['travelers'] ?? 1);
        $requests = $_POST['special_requests'] ?? '';

        // Validate
        if (empty($package_id)) {
            $errors['package'] = 'Please select a package';
        }

        $today = date('Y-m-d');
        if (empty($start_date) || strtotime($start_date) < strtotime($today)) {
            $errors['start_date'] = 'Start date must be today or later';
        }

        if (empty($end_date) || strtotime($end_date) < strtotime($start_date)) {
            $errors['end_date'] = 'End date must be after start date';
        }

        if ($travelers < 1 || $travelers > 20) {
            $errors['travelers'] = 'Travelers must be between 1 and 20';
        }

        if (!empty($errors)) {
            return [
                'status' => 'validation_error',
                'errors' => $errors
            ];
        }

        // Calculate duration and pricing
        if (!empty($start_date) && !empty($end_date)) {
            $start = new \DateTime($start_date);
            $end = new \DateTime($end_date);
            $duration = $start->diff($end)->days;

            // Get package and user discount
            $pkgResult = Database::query(
                "SELECT price FROM packages WHERE id = ?",
                "i",
                [$package_id]
            );
            $package = mysqli_fetch_assoc($pkgResult);

            $user = User::findById($user_id);
            $discount = $user->getLoyaltyDiscount();

            // Calculate pricing
            $daily_rate = $package['price'] / 3;
            $subtotal = $daily_rate * $duration * $travelers;
            $discount_amt = $subtotal * $discount;
            $total = ($subtotal - $discount_amt) * 1.10; // Include 10% tax

            // Create booking
            $result = Database::query(
                "SELECT name FROM packages WHERE id = ?",
                "i",
                [$package_id]
            );
            $pkg = mysqli_fetch_assoc($result);

            if (Booking::create($user_id, $package_id, $pkg['name'], $start_date, $end_date,
                              $duration, $travelers, $subtotal, $discount_amt, $total, $requests)) {
                return [
                    'status' => 'success',
                    'message' => 'Booking created successfully!',
                    'redirect' => 'bookings.php'
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'Failed to create booking'
        ];
    }

    public static function update($booking_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['status' => 'idle'];
        }

        AuthController::requireLogin();

        if (!isset($_POST['csrf_token']) || !\verifyCSRFToken($_POST['csrf_token'])) {
            return ['status' => 'error', 'message' => 'Security validation failed'];
        }

        $user_id = $_SESSION['user_id'];
        $booking = Booking::findById($booking_id, $user_id);

        if (!$booking) {
            return ['status' => 'error', 'message' => 'Booking not found'];
        }

        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $travelers = intval($_POST['travelers'] ?? 1);
        $requests = $_POST['special_requests'] ?? '';

        $errors = [];
        $today = date('Y-m-d');

        if (empty($start_date) || strtotime($start_date) < strtotime($today)) {
            $errors['start_date'] = 'Invalid start date';
        }

        if (empty($end_date) || strtotime($end_date) < strtotime($start_date)) {
            $errors['end_date'] = 'Invalid end date';
        }

        if ($travelers < 1 || $travelers > 20) {
            $errors['travelers'] = 'Invalid number of travelers';
        }

        if (!empty($errors)) {
            return ['status' => 'validation_error', 'errors' => $errors];
        }

        $duration = (new \DateTime($start_date))->diff(new \DateTime($end_date))->days;

        if ($booking->update($start_date, $end_date, $duration, $travelers, $requests)) {
            return [
                'status' => 'success',
                'message' => 'Booking updated successfully!',
                'redirect' => 'bookings.php'
            ];
        }

        return ['status' => 'error', 'message' => 'Failed to update booking'];
    }

    public static function delete($booking_id) {
        AuthController::requireLogin();

        $user_id = $_SESSION['user_id'];
        $booking = Booking::findById($booking_id, $user_id);

        if (!$booking) {
            return ['status' => 'error', 'message' => 'Booking not found'];
        }

        if ($booking->delete()) {
            return [
                'status' => 'success',
                'message' => 'Booking deleted successfully!',
                'redirect' => 'bookings.php'
            ];
        }

        return ['status' => 'error', 'message' => 'Failed to delete booking'];
    }

    public static function getUserBookings($user_id) {
        $result = Booking::getUserBookings($user_id);
        $bookings = [];

        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $bookings[] = new Booking($row);
            }
        }

        return $bookings;
    }
}
?>

<?php

namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\Booking;
use Backend\Models\DiscountTier;
use Backend\Models\User;

class DashboardController extends Controller
{
    private User $userModel;
    private Booking $bookingModel;
    private DiscountTier $tierModel;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->userModel = new User();
        $this->bookingModel = new Booking();
        $this->tierModel = new DiscountTier();
    }

    public function indexAction(): void
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            Session::set('return_url', $_SERVER['REQUEST_URI']);
            $this->redirect('/login');
        }

        $user = $this->userModel->findById((int) $userId);
        if (!$user) {
            $this->redirect('/login');
        }

        if (($user['role'] ?? '') === 'admin') {
            $this->redirect('/admin/dashboard');
        }

        $bookings = $this->bookingModel->findAllByUser((int) $userId, false);
        $totalBookings = 0;
        $totalSpent = 0.0;

        if ($bookings instanceof \mysqli_result) {
            while ($row = $bookings->fetch_assoc()) {
                if (($row['status'] ?? '') === 'confirmed') {
                    $totalBookings++;
                    $totalSpent += floatval($row['final_amount']);
                }
            }
        }

        $trips = intval($user['trips_completed'] ?? 0);
        $nextTier = $this->tierModel->findNextTier($trips);

        $this->render('dashboard.index', [
            'user' => $user,
            'totalBookings' => $totalBookings,
            'totalSpent' => $totalSpent,
            'discountPercent' => floatval($user['loyalty_discount'] ?? 0) * 100,
            'trips' => $trips,
            'nextTier' => $nextTier,
        ]);
    }
}

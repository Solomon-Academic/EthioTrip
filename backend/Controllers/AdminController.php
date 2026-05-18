<?php

namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\DiscountTier;
use Backend\Models\User;

class AdminController extends Controller
{
    private DiscountTier $tierModel;
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        Session::start();
        $this->tierModel = new DiscountTier();
        $this->userModel = new User();
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

    public function adjustDiscountsAction(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $adjustment = floatval($_POST['adjustment'] ?? 0);
            if ($adjustment !== 0) {
                $this->tierModel->adjustAll($adjustment);
            }
        }

        $this->redirect('/admin/discounts');
    }
}

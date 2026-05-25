<?php
namespace Backend\Controllers;

use Backend\Core\Controller;

class RedirectController extends Controller {
    
    public function toLogin() {
        header('Location: /ethiotrip1/ethiotrip/public/backend.php?page=login');
        exit;
    }
    
    public function toRegister() {
        header('Location: /ethiotrip1/ethiotrip/public/backend.php?page=register');
        exit;
    }
    
    public function toDashboard() {
        header('Location: /ethiotrip1/ethiotrip/public/backend.php?page=dashboard');
        exit;
    }
    
    public function toBookings() {
        header('Location: /ethiotrip1/ethiotrip/public/backend.php?page=bookings');
        exit;
    }
}
?>
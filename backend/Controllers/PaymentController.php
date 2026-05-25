<?php
namespace Backend\Controllers;

use Backend\Core\Controller;

class PaymentController extends Controller {
    public function index() {
        $filePath = __DIR__ . '/../../public/pages/payment.html';
        if (file_exists($filePath)) {
            readfile($filePath);
            return;
        }

        echo "Payment page coming soon!";
    }
}

<?php
namespace Backend\Controllers;

use Backend\Core\Controller;

class HomeController extends Controller {
    public function index() {
        // Serve the home.html file from public/pages
        $filePath = __DIR__ . '/../../public/pages/home.html';
        if (file_exists($filePath)) {
            readfile($filePath);
        } else {
            $this->render('home.index');
        }
    }
    
    public function about() {
        $filePath = __DIR__ . '/../../public/pages/about.html';
        if (file_exists($filePath)) {
            readfile($filePath);
        } else {
            $this->render('home.about');
        }
    }
}

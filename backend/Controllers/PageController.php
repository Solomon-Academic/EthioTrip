<?php
namespace Backend\Controllers;

use Backend\Core\Controller;

class PageController extends Controller {
    public function __construct() {
        parent::__construct();
    }
    
    public function home() {
        $this->serveHtmlPage('home.html');
    }
    
    public function destination() {
        $this->serveHtmlPage('destination.html');
    }
    
    public function packages() {
        $this->serveHtmlPage('packages.html');
    }
    
    public function payment() {
        $this->serveHtmlPage('payment.html');
    }
    
    public function about() {
        $this->serveHtmlPage('about.html');
    }
    public function help() {
        $this->serveHtmlPage('help.html');
    }
    
    public function readMore() {
    $filePath = __DIR__ . '/../../public/pages/read-more.html';
    if (file_exists($filePath)) {
        readfile($filePath);
        exit;
    }
    $this->render('page.readmore');
   }
    
    private function serveHtmlPage($filename) {
        $filePath = __DIR__ . '/../../public/pages/' . $filename;
        if (file_exists($filePath)) {
            readfile($filePath);
            exit;
        }
        
        // Try alternative path
        $altPath = __DIR__ . '/../../public/' . $filename;
        if (file_exists($altPath)) {
            readfile($altPath);
            exit;
        }
        
        echo "<h1>Page Not Found: {$filename}</h1>";
        echo "<p>Tried paths:</p>";
        echo "<ul>";
        echo "<li>" . __DIR__ . '/../../public/pages/' . $filename . "</li>";
        echo "<li>" . __DIR__ . '/../../public/' . $filename . "</li>";
        echo "</ul>";
        exit;
    }
}
?>

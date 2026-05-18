<?php
namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\Destination;

class DestinationController extends Controller {
    private $destinationModel;
    
    public function __construct() {
        parent::__construct();
        $this->destinationModel = new Destination();
    }
    
    public function index() {
        $filePath = __DIR__ . '/../../public/pages/destination.html';
        if (file_exists($filePath)) {
            readfile($filePath);
            return;
        }

        $destinations = $this->destinationModel->all();
        $this->render('destination.index', ['destinations' => $destinations]);
    }
    
    public function show() {
        $id = $_GET['id'] ?? 0;
        $destination = $this->destinationModel->find($id);
        
        if (!$destination) {
            $this->redirect('/destinations');
        }
        
        $this->render('destination.show', ['destination' => $destination]);
    }
    
    public function adminIndex() {
        $this->requireAdmin();
        $destinations = $this->destinationModel->all();
        $message = Session::getFlash('admin_message');
        
        $this->render('admin.destinations', [
            'destinations' => $destinations,
            'message' => $message
        ]);
    }
    
    public function showCreate() {
        $this->requireAdmin();
        $this->render('admin.destination_form', [
            'formAction' => '/ethiotrip1/ethiotrip/public/admin/destinations/create',
            'buttonText' => 'Create Destination',
            'pageTitle' => 'Add New Destination',
            'form' => [],
            'errors' => []
        ]);
    }
    
    public function create() {
        $this->requireAdmin();
        $this->requireValidCsrf();
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'location' => $_POST['location'] ?? '',
            'best_time' => $_POST['best_time'] ?? '',
            'price' => floatval($_POST['price'] ?? 0),
            'description' => $_POST['description'] ?? '',
            'churches' => $_POST['churches'] ?? '',
            'is_active' => intval($_POST['is_active'] ?? 0),
        ];
        $data = array_merge($data, $this->handleUploads());
        
        if (empty($data['name'])) {
            Session::setFlash('admin_message', 'Destination name is required');
            $this->redirect('/admin/destinations/create');
        }
        
        if ($this->destinationModel->create($data)) {
            Session::setFlash('admin_message', 'Destination created successfully');
        } else {
            Session::setFlash('admin_message', 'Failed to create destination');
        }
        
        $this->redirect('/admin/destinations');
    }
    
    public function showEdit() {
        $this->requireAdmin();
        $id = $_GET['id'] ?? 0;
        $destination = $this->destinationModel->find($id);
        
        if (!$destination) {
            $this->redirect('/admin/destinations');
        }
        
        $this->render('admin.destination_form', [
            'formAction' => "/ethiotrip1/ethiotrip/public/admin/destinations/edit?id={$id}",
            'buttonText' => 'Save Changes',
            'pageTitle' => 'Edit Destination',
            'form' => $destination,
            'errors' => []
        ]);
    }
    
    public function update() {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $id = $_GET['id'] ?? 0;
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'location' => $_POST['location'] ?? '',
            'best_time' => $_POST['best_time'] ?? '',
            'price' => floatval($_POST['price'] ?? 0),
            'description' => $_POST['description'] ?? '',
            'churches' => $_POST['churches'] ?? '',
            'is_active' => intval($_POST['is_active'] ?? 0),
        ];
        $data = array_merge($data, $this->handleUploads());
        
        if ($this->destinationModel->update($id, $data)) {
            Session::setFlash('admin_message', 'Destination updated successfully');
        } else {
            Session::setFlash('admin_message', 'Failed to update destination');
        }
        
        $this->redirect('/admin/destinations');
    }
    
    public function delete() {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/destinations');
        }
        $this->requireValidCsrf();
        $id = $_GET['id'] ?? 0;
        
        if ($this->destinationModel->delete($id)) {
            Session::setFlash('admin_message', 'Destination deleted successfully');
        }
        
        $this->redirect('/admin/destinations');
    }

    private function handleUploads(): array {
        $saved = [];
        $uploadDir = __DIR__ . '/../../public/uploads/destinations';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $fields = [
            'image' => 'image_path',
            'attachment' => 'attachment_path',
        ];

        foreach ($fields as $field => $column) {
            if (empty($_FILES[$field]['tmp_name']) || !is_uploaded_file($_FILES[$field]['tmp_name'])) {
                continue;
            }

            $original = basename($_FILES[$field]['name']);
            $extension = pathinfo($original, PATHINFO_EXTENSION);
            $safeName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($original, PATHINFO_FILENAME));
            $filename = trim($safeName, '-') . '-' . time() . ($extension ? '.' . strtolower($extension) : '');
            $target = $uploadDir . '/' . $filename;

            if (move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
                $saved[$column] = 'ethiotrip1/ethiotrip/public/uploads/destinations/' . $filename;
            }
        }

        return $saved;
    }
}

<?php
namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\Destination;
use Backend\Models\DestinationHighlight;
use Backend\Models\DestinationAttraction;

class DestinationController extends Controller {
    private Destination $destinationModel;
    private DestinationHighlight $highlightModel;
    private DestinationAttraction $attractionModel;

    public function __construct() {
        parent::__construct();
        Session::start();
        $this->destinationModel = new Destination();
        $this->highlightModel = new DestinationHighlight();
        $this->attractionModel = new DestinationAttraction();
    }

    protected function requireAdmin(): void {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            $this->redirect('/login');
        }
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
            'form' => [
                'highlights_text' => '',
                'attractions_text' => '',
            ],
            'errors' => []
        ]);
    }
    
    public function create() {
        $this->requireAdmin();
        $this->requireValidCsrf();
        
        $data = $this->collectDestinationData();
        
        if (empty($data['name'])) {
            Session::setFlash('admin_message', 'Destination name is required');
            $this->redirect('/admin/destinations/create');
            return;
        }
        
        $id = (int) $this->destinationModel->createDestination($data);
        
        if ($id > 0) {
            $this->saveRelatedContent($id, $_POST);
            Session::setFlash('admin_message', 'Destination created successfully!');
        } else {
            Session::setFlash('admin_message', 'Failed to create destination');
        }
        
        $this->redirect('/admin/destinations');
    }
    
    public function showEdit() {
        $this->requireAdmin();
        $id = intval($_GET['id'] ?? 0);
        $destination = $this->destinationModel->find($id);
        
        if (!$destination) {
            Session::setFlash('admin_message', 'Destination not found');
            $this->redirect('/admin/destinations');
            return;
        }

        $destination['highlights_text'] = $this->highlightsToText($this->highlightModel->findByDestination($id));
        $destination['attractions_text'] = $this->attractionsToText($this->attractionModel->findByDestination($id));
        
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
        
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            Session::setFlash('admin_message', 'Invalid destination ID');
            $this->redirect('/admin/destinations');
            return;
        }
        
        $data = $this->collectDestinationData();
        
        if (empty($data['name'])) {
            Session::setFlash('admin_message', 'Destination name is required');
            $this->redirect('/admin/destinations/edit?id=' . $id);
            return;
        }
        
        $result = $this->destinationModel->updateDestination($id, $data);
        
        if ($result) {
            $this->saveRelatedContent($id, $_POST);
            Session::setFlash('admin_message', 'Destination updated successfully!');
        } else {
            Session::setFlash('admin_message', 'Failed to update destination');
        }
        
        $this->redirect('/admin/destinations');
    }
    
    public function delete() {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/destinations');
            return;
        }
        
        $this->requireValidCsrf();
        
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            Session::setFlash('admin_message', 'Invalid destination ID');
            $this->redirect('/admin/destinations');
            return;
        }
        
        $result = $this->destinationModel->deleteDestination($id);
        
        Session::setFlash('admin_message', $result ? 'Destination deleted successfully!' : 'Failed to delete destination');
        $this->redirect('/admin/destinations');
    }

    private function collectDestinationData(): array {
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'best_time' => trim($_POST['best_time'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'short_description' => trim($_POST['short_description'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'travel_guide' => trim($_POST['travel_guide'] ?? ''),
            'activities' => trim($_POST['activities'] ?? ''),
            'is_active' => intval($_POST['is_active'] ?? 1),
        ];

        $uploadResult = $this->handleUploads();
        if (!empty($uploadResult['image_path'])) {
            $data['image_path'] = $uploadResult['image_path'];
        }
        if (!empty($uploadResult['attachment_path'])) {
            $data['attachment_path'] = $uploadResult['attachment_path'];
        }

        return $data;
    }

    private function saveRelatedContent(int $destinationId, array $post): void {
        $highlights = Destination::parseHighlightLines($post['highlights_text'] ?? '');
        $attractions = Destination::parseAttractionLines($post['attractions_text'] ?? '');
        $this->highlightModel->replaceForDestination($destinationId, $highlights);
        $this->attractionModel->replaceForDestination($destinationId, $attractions);
    }

    private function highlightsToText(array $rows): string {
        $lines = [];
        foreach ($rows as $row) {
            $title = $row['title'] ?? '';
            $desc = $row['description'] ?? '';
            $lines[] = $desc !== '' ? "{$title}|{$desc}" : $title;
        }
        return implode("\n", $lines);
    }

    private function attractionsToText(array $rows): string {
        $lines = [];
        foreach ($rows as $row) {
            $name = $row['name'] ?? '';
            $desc = $row['description'] ?? '';
            $lines[] = $desc !== '' ? "{$name}|{$desc}" : $name;
        }
        return implode("\n", $lines);
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

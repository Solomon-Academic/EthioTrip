<?php
namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\Package;
use Backend\Models\Destination;

class PackageController extends Controller {
    private Package $packageModel;
    private Destination $destinationModel;

    public function __construct() {
        parent::__construct();
        Session::start();
        $this->packageModel = new Package();
        $this->destinationModel = new Destination();
    }

    protected function requireAdmin(): void {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            $this->redirect('/login');
        }
    }

    private function getDestinationsList(): array {
        $result = $this->destinationModel->all();
        $list = [];
        if ($result instanceof \mysqli_result) {
            while ($row = $result->fetch_assoc()) {
                $list[] = $row;
            }
        }
        return $list;
    }

    public function adminIndex(): void {
        $this->requireAdmin();
        $packages = $this->packageModel->findAll();
        $message = Session::getFlash('admin_message');
        
        $this->render('admin.packages', [
            'packages' => $packages,
            'message' => $message,
        ]);
    }

    public function showCreate(): void {
        $this->requireAdmin();
        $this->render('admin.package_form', [
            'formAction' => '/ethiotrip1/ethiotrip/public/admin/packages/create',
            'pageTitle' => 'Add New Package',
            'buttonText' => 'Create Package',
            'form' => [],
            'destinations' => $this->getDestinationsList(),
            'errors' => [],
        ]);
    }

    public function create(): void {
        $this->requireAdmin();
        $this->requireValidCsrf();

        $data = $this->collectPackageData();

        if (empty($data['name'])) {
            Session::setFlash('admin_message', 'Package name is required');
            $this->redirect('/admin/packages/create');
            return;
        }

        if ($data['price'] <= 0) {
            Session::setFlash('admin_message', 'Price must be greater than 0');
            $this->redirect('/admin/packages/create');
            return;
        }

        if (empty($data['destination_id'])) {
            Session::setFlash('admin_message', 'Please select a destination for this package');
            $this->redirect('/admin/packages/create');
            return;
        }

        $result = $this->packageModel->createPackage($data);
        
        Session::setFlash('admin_message', $result ? 'Package created successfully!' : 'Failed to create package');
        $this->redirect('/admin/packages');
    }

    public function showEdit(): void {
        $this->requireAdmin();
        $id = intval($_GET['id'] ?? 0);
        $package = $this->packageModel->findById($id);
        
        if (!$package) {
            Session::setFlash('admin_message', 'Package not found');
            $this->redirect('/admin/packages');
            return;
        }

        $this->render('admin.package_form', [
            'formAction' => '/ethiotrip1/ethiotrip/public/admin/packages/edit?id=' . $id,
            'pageTitle' => 'Edit Package',
            'buttonText' => 'Update Package',
            'form' => $package,
            'destinations' => $this->getDestinationsList(),
            'errors' => [],
        ]);
    }

    public function update(): void {
        $this->requireAdmin();
        $this->requireValidCsrf();
        
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            Session::setFlash('admin_message', 'Invalid package ID');
            $this->redirect('/admin/packages');
            return;
        }

        $data = $this->collectPackageData();

        if (empty($data['name'])) {
            Session::setFlash('admin_message', 'Package name is required');
            $this->redirect('/admin/packages/edit?id=' . $id);
            return;
        }

        if (empty($data['destination_id'])) {
            Session::setFlash('admin_message', 'Please select a destination for this package');
            $this->redirect('/admin/packages/edit?id=' . $id);
            return;
        }

        $result = $this->packageModel->updatePackage($id, $data);
        
        Session::setFlash('admin_message', $result ? 'Package updated successfully!' : 'Failed to update package');
        $this->redirect('/admin/packages');
    }

    public function delete(): void {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/packages');
            return;
        }
        
        $this->requireValidCsrf();
        
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            Session::setFlash('admin_message', 'Invalid package ID');
            $this->redirect('/admin/packages');
            return;
        }

        $result = $this->packageModel->deletePackage($id);
        
        Session::setFlash('admin_message', $result ? 'Package deleted successfully!' : 'Failed to delete package');
        $this->redirect('/admin/packages');
    }

    private function collectPackageData(): array {
        $features = trim($_POST['features'] ?? '');
        if ($features !== '' && !str_starts_with($features, '[')) {
            $lines = array_filter(array_map('trim', preg_split('/\r?\n/', $features)));
            $features = json_encode($lines);
        }

        return [
            'name' => trim($_POST['name'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'duration' => trim($_POST['duration'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'features' => $features,
            'category' => trim($_POST['category'] ?? ''),
            'destination_id' => intval($_POST['destination_id'] ?? 0),
            'is_active' => intval($_POST['is_active'] ?? 1),
        ];
    }
}

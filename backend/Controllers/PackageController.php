<?php
namespace Backend\Controllers;

use Backend\Core\Controller;
use Backend\Core\Session;
use Backend\Models\Package;

class PackageController extends Controller {
    private Package $packageModel;

    public function __construct() {
        parent::__construct();
        Session::start();
        $this->packageModel = new Package();
    }

    public function adminIndex(): void {
        $this->requireAdmin();
        $this->render('admin.packages', [
            'packages' => $this->packageModel->findAll(),
            'message' => Session::getFlash('admin_message'),
        ]);
    }

    public function showCreate(): void {
        $this->requireAdmin();
        $this->render('admin.package_form', [
            'formAction' => $this->basePath . '/admin/packages/create',
            'pageTitle' => 'Add New Package',
            'buttonText' => 'Create Package',
            'form' => [],
            'errors' => [],
        ]);
    }

    public function create(): void {
        $this->requireAdmin();
        $this->requireValidCsrf();

        $data = $this->packageDataFromPost();
        if ($data['name'] === '' || $data['price'] <= 0) {
            $this->render('admin.package_form', [
                'formAction' => $this->basePath . '/admin/packages/create',
                'pageTitle' => 'Add New Package',
                'buttonText' => 'Create Package',
                'form' => $data,
                'errors' => ['general' => 'Package name and a positive price are required.'],
            ]);
            return;
        }

        Session::setFlash(
            'admin_message',
            $this->packageModel->create($data) ? 'Package created successfully.' : 'Failed to create package.'
        );
        $this->redirect('/admin/packages');
    }

    public function showEdit(): void {
        $this->requireAdmin();
        $id = intval($_GET['id'] ?? 0);
        $package = $this->packageModel->findById($id);
        if (!$package) {
            $this->redirect('/admin/packages');
        }

        $this->render('admin.package_form', [
            'formAction' => $this->basePath . '/admin/packages/edit?id=' . $id,
            'pageTitle' => 'Edit Package',
            'buttonText' => 'Save Changes',
            'form' => $package,
            'errors' => [],
        ]);
    }

    public function update(): void {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $id = intval($_GET['id'] ?? 0);
        $data = $this->packageDataFromPost();

        if ($id <= 0 || $data['name'] === '' || $data['price'] <= 0) {
            Session::setFlash('admin_message', 'Package name and a positive price are required.');
            $this->redirect('/admin/packages');
        }

        Session::setFlash(
            'admin_message',
            $this->packageModel->update($id, $data) ? 'Package updated successfully.' : 'Failed to update package.'
        );
        $this->redirect('/admin/packages');
    }

    public function delete(): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/packages');
        }
        $this->requireValidCsrf();
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) {
            $this->packageModel->delete($id);
            Session::setFlash('admin_message', 'Package deleted successfully.');
        }
        $this->redirect('/admin/packages');
    }

    private function packageDataFromPost(): array {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'duration' => trim($_POST['duration'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'features' => trim($_POST['features'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'is_active' => intval($_POST['is_active'] ?? 0),
        ];
    }
}
?>

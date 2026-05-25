<?php
namespace Backend\Models;

use Backend\Core\Model;

class Package extends Model {
    protected $table = 'packages';

    public function findAll() {
        return $this->db->query("SELECT * FROM packages ORDER BY is_active DESC, price ASC");
    }

    public function findById($id) {
        return $this->find((int) $id);
    }

    public function findAllActive() {
        return $this->db->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY price ASC");
    }

    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM packages WHERE LOWER(name) = LOWER(?) AND is_active = 1 LIMIT 1");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function createPackage($data) {
        return $this->create($data);
    }
    
    public function updatePackage($id, $data) {
        return $this->update($id, $data);
    }
    
    public function deletePackage($id) {
        // Check if package has any bookings
        $checkStmt = $this->db->prepare("SELECT COUNT(*) as count FROM bookings WHERE package_id = ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            // Instead of deleting, just deactivate
            return $this->update($id, ['is_active' => 0]);
        }
        
        return $this->delete($id);
    }
    
    public function getPackageWithBookings($id) {
        $stmt = $this->db->prepare("SELECT p.*, 
            (SELECT COUNT(*) FROM bookings WHERE package_id = p.id) as booking_count 
            FROM packages p WHERE p.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
<?php
namespace Backend\Models;

use Backend\Core\Model;

class Destination extends Model {
    protected $table = 'destinations';

    public function findAllActive() {
        return $this->db->query("SELECT * FROM destinations WHERE is_active = 1 ORDER BY name ASC");
    }
    
    public function createDestination($data) {
        return $this->create($data);
    }
    
    public function updateDestination($id, $data) {
        return $this->update($id, $data);
    }
    
    public function deleteDestination($id) {
        // First check if destination has any bookings
        $checkStmt = $this->db->prepare("SELECT COUNT(*) as count FROM bookings WHERE destination = (SELECT name FROM destinations WHERE id = ?)");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            // Instead of deleting, just deactivate
            return $this->update($id, ['is_active' => 0]);
        }
        
        return $this->delete($id);
    }
    
    public function getDestinationWithBookings($id) {
        $stmt = $this->db->prepare("SELECT d.*, 
            (SELECT COUNT(*) FROM bookings WHERE destination = d.name) as booking_count 
            FROM destinations d WHERE d.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
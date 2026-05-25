<?php
namespace Backend\Models;

use Backend\Core\Model;

class User extends Model {
    protected $table = 'users';

    public function findById($id) {
        return $this->find((int) $id);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE LOWER(name) = LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getLastInsertId() {
        return $this->db->insert_id;
    }

    public function updateStats($id, $tripsCompleted, $totalSpent) {
        $stmt = $this->db->prepare("UPDATE users SET trips_completed = ?, total_spent = ? WHERE id = ?");
        $stmt->bind_param("idi", $tripsCompleted, $totalSpent, $id);
        return $stmt->execute();
    }

    public function updateLoyaltyDiscount($id, $discount) {
        $stmt = $this->db->prepare("UPDATE users SET loyalty_discount = ? WHERE id = ?");
        $stmt->bind_param("di", $discount, $id);
        return $stmt->execute();
    }
    
    public function getTotalUsersCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM users");
        return $result->fetch_assoc()['count'];
    }
    
    public function findAllUsers($limit = null, $offset = 0) {
        $sql = "SELECT id, name, email, phone, role, trips_completed, total_spent, loyalty_discount, created_at FROM users ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT $offset, $limit";
        }
        return $this->db->query($sql);
    }
}
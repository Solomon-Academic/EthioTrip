<?php
namespace Backend\Models;

use Backend\Core\Model;

class UserDestination extends Model {
    protected $table = 'user_destinations';

    public function findByUserAndDestination($userId, $destination) {
        $stmt = $this->db->prepare("SELECT * FROM user_destinations WHERE user_id = ? AND destination = ? LIMIT 1");
        $stmt->bind_param("is", $userId, $destination);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateVisit($id, $visitCount, $lastVisited) {
        $stmt = $this->db->prepare("UPDATE user_destinations SET visit_count = ?, last_visited = ? WHERE id = ?");
        $stmt->bind_param("isi", $visitCount, $lastVisited, $id);
        return $stmt->execute();
    }

    public function create($userIdOrData, $destination = null, $lastVisited = null) {
        if (is_array($userIdOrData)) {
            return parent::create($userIdOrData);
        }

        $stmt = $this->db->prepare("INSERT INTO user_destinations (user_id, destination, last_visited) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userIdOrData, $destination, $lastVisited);
        return $stmt->execute();
    }
}
?>

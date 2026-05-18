<?php
namespace Backend\Models;

use Backend\Core\Model;

class Booking extends Model {
    protected $table = 'bookings';

    public function findAllByUser($userId, $isAdmin = false) {
        if ($isAdmin) {
            return $this->db->query("SELECT b.*, u.name AS user_name FROM bookings b LEFT JOIN users u ON u.id = b.user_id ORDER BY b.created_at DESC");
        }

        $stmt = $this->db->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function findByUserAndId($bookingId, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->bind_param("ii", $bookingId, $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function cancel($bookingId, $userId) {
        $stmt = $this->db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $bookingId, $userId);
        return $stmt->execute();
    }
}
?>

<?php
namespace Backend\Models;

use Backend\Core\Model;

class Booking extends Model {
    protected $table = 'bookings';

    public function __construct() {
        parent::__construct();
        $this->ensureAdminApprovalColumns();
    }

    private function ensureAdminApprovalColumns(): void {
        $columns = [
            'admin_approval_status' => "ALTER TABLE bookings ADD COLUMN admin_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'",
            'admin_notes' => "ALTER TABLE bookings ADD COLUMN admin_notes TEXT NULL",
            'approved_by' => "ALTER TABLE bookings ADD COLUMN approved_by INT NULL",
            'approved_at' => "ALTER TABLE bookings ADD COLUMN approved_at TIMESTAMP NULL",
        ];

        foreach ($columns as $column => $sql) {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM bookings LIKE ?");
            $stmt->bind_param("s", $column);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                $this->db->query($sql);
            }
        }
    }

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
    
    // Admin approval methods
    public function approveBooking($bookingId, $adminId, $adminNotes = '') {
        $stmt = $this->db->prepare("UPDATE bookings SET admin_approval_status = 'approved', approved_by = ?, approved_at = NOW(), admin_notes = CONCAT(IFNULL(admin_notes, ''), ?, '\\n[Approved by Admin] ', NOW()), status = 'confirmed' WHERE id = ?");
        $notes = $adminNotes ? "Admin note: $adminNotes\\n" : '';
        $stmt->bind_param("isi", $adminId, $notes, $bookingId);
        return $stmt->execute();
    }
    
    public function rejectBooking($bookingId, $adminId, $adminNotes = '') {
        $stmt = $this->db->prepare("UPDATE bookings SET admin_approval_status = 'rejected', approved_by = ?, approved_at = NOW(), admin_notes = CONCAT(IFNULL(admin_notes, ''), ?, '\\n[Rejected by Admin] ', NOW()), status = 'cancelled' WHERE id = ?");
        $notes = $adminNotes ? "Rejection reason: $adminNotes\\n" : '';
        $stmt->bind_param("isi", $adminId, $notes, $bookingId);
        return $stmt->execute();
    }

    public function approvePayment($bookingId, $adminId, $adminNotes = '') {
        $stmt = $this->db->prepare("UPDATE bookings SET payment_status = 'completed', admin_approval_status = 'approved', approved_by = ?, approved_at = NOW(), admin_notes = CONCAT(IFNULL(admin_notes, ''), ?, '\\n[Payment approved by Admin] ', NOW()), status = 'confirmed' WHERE id = ?");
        $notes = $adminNotes ? "Payment note: $adminNotes\\n" : '';
        $stmt->bind_param("isi", $adminId, $notes, $bookingId);
        return $stmt->execute();
    }

    public function markPaymentFailed($bookingId, $adminId, $adminNotes = '') {
        $stmt = $this->db->prepare("UPDATE bookings SET payment_status = 'failed', admin_approval_status = 'rejected', approved_by = ?, approved_at = NOW(), admin_notes = CONCAT(IFNULL(admin_notes, ''), ?, '\\n[Payment rejected by Admin] ', NOW()), status = 'cancelled' WHERE id = ?");
        $notes = $adminNotes ? "Payment rejection reason: $adminNotes\\n" : '';
        $stmt->bind_param("isi", $adminId, $notes, $bookingId);
        return $stmt->execute();
    }
    
    public function getAllBookingsForAdmin($filter = 'all', $search = '') {
        $sql = "SELECT b.*, u.name as user_name, u.email as user_email 
                FROM bookings b 
                LEFT JOIN users u ON u.id = b.user_id";
        
        $conditions = [];
        $params = [];
        $types = "";
        
        if ($filter !== 'all' && in_array($filter, ['pending', 'approved', 'rejected'])) {
            $conditions[] = "b.admin_approval_status = ?";
            $params[] = $filter;
            $types .= "s";
        }

        if ($filter === 'payment_pending') {
            $conditions[] = "b.payment_status = 'pending'";
        }
        
        if (!empty($search)) {
            $conditions[] = "(u.name LIKE ? OR b.package_name LIKE ? OR b.transaction_id LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "sss";
        }
        
        if (count($conditions) > 0) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY b.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        if (count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function getBookingStats() {
        $result = $this->db->query("SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN admin_approval_status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN admin_approval_status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN admin_approval_status = 'rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(final_amount) as total_revenue
            FROM bookings");
        return $result->fetch_assoc();
    }
    
    public function getBookingWithDetails($bookingId) {
        $stmt = $this->db->prepare("SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
                                    FROM bookings b 
                                    LEFT JOIN users u ON u.id = b.user_id 
                                    WHERE b.id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getTotalBookingsCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM bookings");
        return $result->fetch_assoc()['count'];
    }
    
    public function getPendingApprovalsCount() {
        $result = $this->db->query("SELECT COUNT(*) as count FROM bookings WHERE admin_approval_status = 'pending'");
        return $result->fetch_assoc()['count'];
    }
    
    public function getTotalRevenue() {
        $result = $this->db->query("SELECT SUM(final_amount) as total FROM bookings WHERE admin_approval_status = 'approved' AND payment_status = 'completed'");
        return $result->fetch_assoc()['total'] ?? 0;
    }
    
    public function getRecentBookings($limit = 5) {
        $stmt = $this->db->prepare("SELECT b.*, u.name as user_name FROM bookings b LEFT JOIN users u ON u.id = b.user_id ORDER BY b.created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

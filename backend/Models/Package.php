<?php
namespace Backend\Models;

use Backend\Core\Model;

class Package extends Model {
    protected $table = 'packages';

    public function findAll() {
        return $this->db->query(
            "SELECT p.*, d.name AS destination_name
             FROM packages p
             LEFT JOIN destinations d ON d.id = p.destination_id
             ORDER BY p.is_active DESC, p.price ASC"
        );
    }

    public function findById($id) {
        return $this->find((int) $id);
    }

    public function findAllActive() {
        return $this->db->query("SELECT * FROM packages WHERE is_active = 1 ORDER BY price ASC");
    }

    public function findActiveByDestination(int $destinationId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM packages WHERE is_active = 1 AND destination_id = ? ORDER BY price ASC"
        );
        $stmt->bind_param('i', $destinationId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function findByName($name) {
        $stmt = $this->db->prepare("SELECT * FROM packages WHERE LOWER(name) = LOWER(?) AND is_active = 1 LIMIT 1");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getTotalCount(): int {
        $result = $this->db->query("SELECT COUNT(*) as count FROM packages");
        if ($result && $row = $result->fetch_assoc()) {
            return (int) ($row['count'] ?? 0);
        }
        return 0;
    }

    public function createPackage(array $data) {
        return $this->create($data);
    }

    public function updatePackage(int $id, array $data) {
        return $this->update($id, $data);
    }

    public function deletePackage(int $id) {
        $checkStmt = $this->db->prepare("SELECT COUNT(*) as count FROM bookings WHERE package_id = ?");
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result()->fetch_assoc();

        if (($result['count'] ?? 0) > 0) {
            return $this->update($id, ['is_active' => 0]);
        }
        return $this->delete($id);
    }

    public function toApiItem(array $row): array {
        $features = [];
        if (!empty($row['features'])) {
            $decoded = json_decode($row['features'], true);
            if (is_array($decoded)) {
                $features = $decoded;
            } else {
                $features = array_filter(array_map('trim', preg_split('/\r?\n/', $row['features'])));
            }
        }
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'price' => (float) $row['price'],
            'duration' => $row['duration'] ?? '',
            'description' => $row['description'] ?? '',
            'features' => $features,
            'category' => $row['category'] ?? '',
            'destination_id' => isset($row['destination_id']) ? (int) $row['destination_id'] : null,
        ];
    }
}

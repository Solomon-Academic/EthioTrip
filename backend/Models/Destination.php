<?php
namespace Backend\Models;

use Backend\Core\Model;

class Destination extends Model {
    protected $table = 'destinations';

    public function findAllActive(): \mysqli_result|false {
        return $this->db->query(
            "SELECT id, name, location, short_description, description, best_time, price, image_path, image_url, is_active
             FROM destinations WHERE is_active = 1 ORDER BY name ASC"
        );
    }

    public function findActiveById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM destinations WHERE id = ? AND is_active = 1 LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    public function all() {
        return $this->db->query("SELECT * FROM destinations ORDER BY name ASC");
    }

    public function getTotalCount(): int {
        $result = $this->db->query("SELECT COUNT(*) as count FROM destinations");
        if ($result && $row = $result->fetch_assoc()) {
            return (int) ($row['count'] ?? 0);
        }
        return 0;
    }

    public function createDestination(array $data) {
        $allowedColumns = ['name', 'location', 'short_description', 'description', 'travel_guide', 'best_time', 'price', 'activities', 'image_path', 'attachment_path', 'is_active'];
        $filteredData = array_filter($data, function($key) use ($allowedColumns) {
            return in_array($key, $allowedColumns);
        }, ARRAY_FILTER_USE_KEY);
        return $this->create($filteredData);
    }

    public function updateDestination(int $id, array $data) {
        return $this->update($id, $data);
    }

    public function deleteDestination(int $id) {
        $checkStmt = $this->db->prepare(
            "SELECT COUNT(*) as count FROM bookings WHERE destination = (SELECT name FROM destinations WHERE id = ?)"
        );
        $checkStmt->bind_param('i', $id);
        $checkStmt->execute();
        $result = $checkStmt->get_result()->fetch_assoc();

        if (($result['count'] ?? 0) > 0) {
            return $this->update($id, ['is_active' => 0]);
        }
        return $this->delete($id);
    }

    public function getDestinationWithBookings(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT d.*, (SELECT COUNT(*) FROM bookings WHERE destination = d.name) as booking_count
             FROM destinations d WHERE d.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function toListItem(array $row): array {
        $desc = $row['short_description'] ?? $row['description'] ?? '';
        if (strlen($desc) > 160) {
            $desc = substr($desc, 0, 157) . '...';
        }
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'location' => $row['location'] ?? '',
            'short_description' => $desc,
            'best_time' => $row['best_time'] ?? '',
            'price' => (float) ($row['price'] ?? 0),
            'image_url' => $this->resolveImageUrl($row),
        ];
    }

    /**
     * FIXED: Properly resolves image URLs
     */
    public function resolveImageUrl(array $row): string {
        $path = $row['image_path'] ?? $row['image_url'] ?? '';
        
        // If no image path, return default
        if (empty($path)) {
            return '/ethiotrip1/ethiotrip/public/images/dest_images/upscaled_lalibela.jpg';
        }
        
        // If it's already a full URL, return as is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        
        // Clean up the path - remove any duplicate base paths
        $path = ltrim($path, '/');
        $path = str_replace('ethiotrip1/ethiotrip/public/', '', $path);
        $path = str_replace('ethiotrip1/ethiotrip/public', '', $path);
        $path = str_replace('ethiotrip1/', '', $path);
        
        // Build the correct full path
        return '/ethiotrip1/ethiotrip/public/' . $path;
    }

    public static function parseHighlightLines(string $text): array {
        $items = [];
        foreach (preg_split('/\r?\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = explode('|', $line, 2);
            $items[] = [
                'title' => trim($parts[0]),
                'description' => trim($parts[1] ?? ''),
            ];
        }
        return $items;
    }

    public static function parseAttractionLines(string $text): array {
        $items = [];
        foreach (preg_split('/\r?\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = explode('|', $line, 2);
            $items[] = [
                'name' => trim($parts[0]),
                'description' => trim($parts[1] ?? ''),
            ];
        }
        return $items;
    }
}
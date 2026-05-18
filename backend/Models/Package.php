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
}
?>

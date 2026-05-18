<?php
namespace Backend\Models;

use Backend\Core\Model;

class Destination extends Model {
    protected $table = 'destinations';

    public function findAllActive() {
        return $this->db->query("SELECT * FROM destinations WHERE is_active = 1 ORDER BY name ASC");
    }
}
?>

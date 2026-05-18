<?php
namespace Backend\Models;

use Backend\Core\Model;

class DiscountTier extends Model {
    protected $table = 'discount_tiers';

    public function findAll() {
        return $this->db->query("SELECT * FROM discount_tiers ORDER BY min_trips ASC");
    }
    
    public function findActiveTierByTrips($trips) {
        $stmt = $this->db->prepare("SELECT * FROM discount_tiers WHERE is_active = 1 AND min_trips <= ? AND (max_trips IS NULL OR max_trips >= ?) ORDER BY min_trips DESC LIMIT 1");
        $stmt->bind_param("ii", $trips, $trips);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function findNextTier($trips) {
        $stmt = $this->db->prepare("SELECT * FROM discount_tiers WHERE is_active = 1 AND min_trips > ? ORDER BY min_trips ASC LIMIT 1");
        $stmt->bind_param("i", $trips);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateTier($data) {
        $stmt = $this->db->prepare("UPDATE discount_tiers SET min_trips = ?, max_trips = ?, discount_percent = ?, tier_name = ?, is_active = ? WHERE id = ?");
        $maxTrips = $data['max_trips'];
        $stmt->bind_param(
            "iidsii",
            $data['min_trips'],
            $maxTrips,
            $data['discount_percent'],
            $data['tier_name'],
            $data['is_active'],
            $data['id']
        );
        return $stmt->execute();
    }

    public function addTier($data) {
        $stmt = $this->db->prepare("INSERT INTO discount_tiers (min_trips, max_trips, discount_percent, tier_name) VALUES (?, ?, ?, ?)");
        $maxTrips = $data['max_trips'];
        $stmt->bind_param("iids", $data['min_trips'], $maxTrips, $data['discount_percent'], $data['tier_name']);
        return $stmt->execute();
    }

    public function adjustAll($adjustment) {
        $stmt = $this->db->prepare("UPDATE discount_tiers SET discount_percent = GREATEST(0, discount_percent + ?) WHERE is_active = 1");
        $stmt->bind_param("d", $adjustment);
        return $stmt->execute();
    }
}
?>

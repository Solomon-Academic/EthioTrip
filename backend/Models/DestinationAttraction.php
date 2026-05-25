<?php
namespace Backend\Models;

use Backend\Core\Model;

class DestinationAttraction extends Model
{
    protected $table = 'destination_attractions';

    public function findByDestination(int $destinationId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, description, sort_order FROM destination_attractions WHERE destination_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->bind_param('i', $destinationId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function replaceForDestination(int $destinationId, array $items): void
    {
        $del = $this->db->prepare('DELETE FROM destination_attractions WHERE destination_id = ?');
        $del->bind_param('i', $destinationId);
        $del->execute();

        $stmt = $this->db->prepare(
            'INSERT INTO destination_attractions (destination_id, name, description, sort_order) VALUES (?, ?, ?, ?)'
        );
        $order = 0;
        foreach ($items as $item) {
            $name = trim($item['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $description = trim($item['description'] ?? '');
            $order++;
            $stmt->bind_param('issi', $destinationId, $name, $description, $order);
            $stmt->execute();
        }
    }
}

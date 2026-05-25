<?php
namespace Backend\Models;

use Backend\Core\Model;

class DestinationHighlight extends Model
{
    protected $table = 'destination_highlights';

    public function findByDestination(int $destinationId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, title, description, sort_order FROM destination_highlights WHERE destination_id = ? ORDER BY sort_order ASC, id ASC'
        );
        $stmt->bind_param('i', $destinationId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function replaceForDestination(int $destinationId, array $items): void
    {
        $del = $this->db->prepare('DELETE FROM destination_highlights WHERE destination_id = ?');
        $del->bind_param('i', $destinationId);
        $del->execute();

        $stmt = $this->db->prepare(
            'INSERT INTO destination_highlights (destination_id, title, description, sort_order) VALUES (?, ?, ?, ?)'
        );
        $order = 0;
        foreach ($items as $item) {
            $title = trim($item['title'] ?? '');
            if ($title === '') {
                continue;
            }
            $description = trim($item['description'] ?? '');
            $order++;
            $stmt->bind_param('issi', $destinationId, $title, $description, $order);
            $stmt->execute();
        }
    }
}

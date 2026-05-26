<?php
namespace Backend\Core;

class SchemaMigrator
{
    public static function run(Database $db): void
    {
        self::addColumnIfMissing($db, 'destinations', 'short_description', 'VARCHAR(500) NULL');
        self::addColumnIfMissing($db, 'destinations', 'travel_guide', 'TEXT NULL');
        self::addColumnIfMissing($db, 'packages', 'destination_id', 'INT NULL');
        self::addColumnIfMissing($db, 'bookings', 'customer_notified_at', 'TIMESTAMP NULL');
        self::addColumnIfMissing($db, 'bookings', 'last_notification_type', 'VARCHAR(50) NULL');

        $db->query("CREATE TABLE IF NOT EXISTS destination_highlights (
            id INT PRIMARY KEY AUTO_INCREMENT,
            destination_id INT NOT NULL,
            title VARCHAR(150) NOT NULL,
            description TEXT,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
        )");

        $db->query("CREATE TABLE IF NOT EXISTS destination_attractions (
            id INT PRIMARY KEY AUTO_INCREMENT,
            destination_id INT NOT NULL,
            name VARCHAR(150) NOT NULL,
            description TEXT,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
        )");

        self::seedLalibelaHighlights($db);
        self::linkPackagesToDestinations($db);
    }

    private static function seedLalibelaHighlights(Database $db): void
    {
        $res = $db->query("SELECT id FROM destinations WHERE name = 'Lalibela' LIMIT 1");
        if (!$res || !($row = $res->fetch_assoc())) {
            return;
        }
        $destId = (int) $row['id'];
        $check = $db->query("SELECT COUNT(*) AS c FROM destination_highlights WHERE destination_id = {$destId}");
        if ($check && ($c = $check->fetch_assoc()) && (int) $c['c'] > 0) {
            return;
        }

        $highlights = [
            ['Bete Medhane Alem', 'The largest monolithic church, representing the Holy Sepulchre.'],
            ['Bete Maryam', 'Believed to be the oldest church, dedicated to the Virgin Mary.'],
            ['Bete Golgotha Mikael', 'Contains the tomb of King Lalibela and remarkable cruciform pillars.'],
            ['Bete Gabriel-Rufael', 'Accessible only by a narrow rock-hewn trench and bridge.'],
            ['Bete Amanuel', 'Thought to have been the royal family\'s private chapel.'],
            ['Bete Merkorios', 'Linked to ancient religious texts and unique interior art.'],
            ['Bete Abba Libanos', 'Carved from a single rock face with a hanging roof.'],
            ['Bete Lehem', 'Associated with the Nativity and early Christian symbolism.'],
            ['Bete Giyorgis', 'The iconic cross-shaped church, the masterpiece of Lalibela.'],
        ];

        $stmt = $db->prepare('INSERT INTO destination_highlights (destination_id, title, description, sort_order) VALUES (?, ?, ?, ?)');
        $order = 0;
        foreach ($highlights as [$title, $desc]) {
            $order++;
            $stmt->bind_param('issi', $destId, $title, $desc, $order);
            $stmt->execute();
        }
    }

    private static function linkPackagesToDestinations(Database $db): void
    {
        $res = $db->query("SELECT id FROM destinations WHERE name = 'Lalibela' LIMIT 1");
        if (!$res || !($row = $res->fetch_assoc())) {
            return;
        }
        $destId = (int) $row['id'];
        $db->query("UPDATE packages SET destination_id = {$destId} WHERE destination_id IS NULL");
    }

    private static function addColumnIfMissing(Database $db, string $table, string $column, string $definition): void
    {
        $stmt = $db->prepare(
            'SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $stmt->bind_param('ss', $table, $column);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ((int) ($row['cnt'] ?? 0) === 0) {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    }
}

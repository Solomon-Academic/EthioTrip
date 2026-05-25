-- Run on existing ethiotrip_db (safe additive migration)
USE ethiotrip_db;

-- Destinations: richer content
ALTER TABLE destinations
    ADD COLUMN IF NOT EXISTS short_description VARCHAR(500) NULL AFTER description,
    ADD COLUMN IF NOT EXISTS travel_guide TEXT NULL AFTER short_description;

-- Packages linked to destinations
ALTER TABLE packages
    ADD COLUMN IF NOT EXISTS destination_id INT NULL AFTER id;

-- Add FK if not exists (MySQL 8+ may need manual check on older versions)
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = 'ethiotrip_db'
      AND TABLE_NAME = 'packages'
      AND CONSTRAINT_NAME = 'fk_packages_destination'
);
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE packages ADD CONSTRAINT fk_packages_destination FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Structured highlights (e.g. Lalibela churches)
CREATE TABLE IF NOT EXISTS destination_highlights (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
);

-- Related attractions per destination
CREATE TABLE IF NOT EXISTS destination_attractions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
);

-- Seed Lalibela highlights if destination exists
INSERT INTO destination_highlights (destination_id, title, description, sort_order)
SELECT d.id, h.title, h.description, h.sort_order FROM destinations d
JOIN (
    SELECT 1 AS sort_order, 'Bete Medhane Alem' AS title, 'The largest monolithic church, representing the Holy Sepulchre.' AS description UNION ALL
    SELECT 2, 'Bete Maryam', 'Believed to be the oldest church, dedicated to the Virgin Mary.' UNION ALL
    SELECT 3, 'Bete Golgotha Mikael', 'Contains the tomb of King Lalibela and remarkable cruciform pillars.' UNION ALL
    SELECT 4, 'Bete Gabriel-Rufael', 'Accessible only by a narrow rock-hewn trench and bridge.' UNION ALL
    SELECT 5, 'Bete Amanuel', 'Thought to have been the royal family''s private chapel.' UNION ALL
    SELECT 6, 'Bete Merkorios', 'Linked to ancient religious texts and unique interior art.' UNION ALL
    SELECT 7, 'Bete Abba Libanos', 'Carved from a single rock face with a hanging roof.' UNION ALL
    SELECT 8, 'Bete Lehem', 'Associated with the Nativity and early Christian symbolism.' UNION ALL
    SELECT 9, 'Bete Giyorgis', 'The iconic cross-shaped church, the masterpiece of Lalibela.' UNION ALL
    SELECT 10, 'Bete Aregawi', 'Honors Saint Aregawi and local pilgrimage traditions.' UNION ALL
    SELECT 11, 'Bete Emanuel (Annex)', 'Completes the sacred circuit of the 11 rock-hewn churches.'
) h ON d.name = 'Lalibela'
WHERE NOT EXISTS (SELECT 1 FROM destination_highlights WHERE destination_id = d.id);

-- Link existing packages to Lalibela by default (admin can reassign)
UPDATE packages p
JOIN destinations d ON d.name = 'Lalibela'
SET p.destination_id = d.id
WHERE p.destination_id IS NULL;

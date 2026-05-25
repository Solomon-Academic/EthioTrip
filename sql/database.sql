-- ETHIOTRIP DATABASE - COMPLETE SCHEMA (UPDATED & FIXED)

-- Create fresh database
DROP DATABASE IF EXISTS ethiotrip_db;
CREATE DATABASE ethiotrip_db;
USE ethiotrip_db;

-- 1. USERS TABLE
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    trips_completed INT DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0.00,
    loyalty_discount DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. DISCOUNT TIERS TABLE
CREATE TABLE discount_tiers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    min_trips INT NOT NULL,
    max_trips INT NULL,
    discount_percent DECIMAL(5,2) NOT NULL,
    tier_name VARCHAR(50) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 3. DESTINATIONS TABLE
CREATE TABLE destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100),
    short_description VARCHAR(500),
    description TEXT,
    travel_guide TEXT,
    best_time VARCHAR(100),
    price DECIMAL(10,2) DEFAULT 0.00,
    activities TEXT,
    churches TEXT,
    image_url VARCHAR(255),
    image_path VARCHAR(255),
    attachment_path VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4b. DESTINATION HIGHLIGHTS
CREATE TABLE destination_highlights (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
);

-- 4c. DESTINATION ATTRACTIONS
CREATE TABLE destination_attractions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
);

-- 4. PACKAGES TABLE
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50) DEFAULT NULL,
    description TEXT,
    features TEXT,
    category VARCHAR(50) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE SET NULL
);

-- 5. BOOKINGS TABLE (WITH ALL ADMIN APPROVAL FIELDS)
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    package_id INT DEFAULT NULL,
    package_name VARCHAR(100) DEFAULT NULL,
    destination VARCHAR(100) DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    duration_days INT NOT NULL,
    number_of_travelers INT NOT NULL DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    final_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    status ENUM('confirmed', 'pending', 'cancelled', 'completed') DEFAULT 'pending',
    admin_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_notes TEXT NULL,
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    special_requests TEXT,
    transaction_id VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL,
    INDEX idx_admin_approval (admin_approval_status),
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status)
);

-- 6. REVIEWS TABLE
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    package_id INT DEFAULT NULL,
    user_name VARCHAR(100) DEFAULT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
);

-- 7. USER DESTINATIONS TABLE
CREATE TABLE user_destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    destination VARCHAR(100) NOT NULL,
    visit_count INT DEFAULT 1,
    last_visited DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_destination (user_id, destination)
);

-- 8. INSERT DISCOUNT TIERS
INSERT INTO discount_tiers (min_trips, max_trips, discount_percent, tier_name) VALUES
(0, 2, 0.00, 'Bronze'),
(3, 4, 3.00, 'Silver'),
(5, 7, 5.00, 'Gold'),
(8, 10, 8.00, 'Platinum'),
(11, NULL, 12.00, 'Diamond');

-- 9. INSERT DESTINATIONS (FIXED IMAGE PATHS)
INSERT INTO destinations (name, location, description, best_time, price, activities, churches, image_path) VALUES
('Lalibela', 'Amhara Region', 'Famous for its 11 monolithic rock-hewn churches, a UNESCO World Heritage site.', 'Oct - Mar', 350.00, 'Church exploration, hiking, coffee ceremony', 'Bete Medhane Alem\nBete Maryam\nBete Gabriel-Rufael', '/ethiotrip1/ethiotrip/public/images/dest_images/upscaled_lalibela.jpg'),
('Axum', 'Tigray Region', 'Ancient city known for its obelisks and as the supposed home of the Ark of the Covenant.', 'Sep - May', 350.00, 'Obelisk tour, palace visit, museum tour', NULL, '/ethiotrip1/ethiotrip/public/images/dest_images/Axum.jpg'),
('Gondar', 'Amhara Region', 'Known as the "Camelot of Africa" with its royal castles.', 'Oct - Feb', 350.00, 'Castle tour, bath visit, cultural music', NULL, '/ethiotrip1/ethiotrip/public/images/dest_images/Gondar.jpg'),
('Harar', 'Harari Region', 'The walled city of Harar, known for its alleyways and hyena feeding.', 'All Year', 350.00, 'Hyena feeding, alley walk, coffee tasting', NULL, '/ethiotrip1/ethiotrip/public/images/dest_images/upscaled_3x_harar.jpg'),
('Omo Valley', 'Southern Region', 'Home to diverse tribal cultures and traditions.', 'Jun - Aug', 350.00, 'Tribe exchange, body painting, market visit', NULL, '/ethiotrip1/ethiotrip/public/images/dest_images/upscaled_3x_Omo.jpg'),
('Simien Mountains', 'Amhara Region', 'Stunning mountain landscapes with endemic wildlife.', 'Sep - Nov', 350.00, 'Baboon sighting, trekking, camping', NULL, '/ethiotrip1/ethiotrip/public/images/dest_images/upscaled_simen.jpg'),
('Danakil Depression', 'Afar Region', 'One of the hottest places on Earth with active volcanoes.', 'Nov - Jan', 350.00, 'Volcano hike, salt flats, sulfur springs', NULL, '/ethiotrip1/ethiotrip/public/images/dest_images/upscaled_3x_Afar.jpg'),
('Sof Omar Cave', 'Oromia Region', 'One of the largest cave systems in the world.', 'Dec - Apr', 350.00, 'Cave exploration, hiking, photography', NULL, '/ethiotrip1/ethiotrip/public/images/dest_images/Cave.jpg');

-- 10. INSERT PACKAGES
INSERT INTO packages (name, price, duration, description, features, category, is_active) VALUES
('Meskerem Journey', 115.00, 'per day', 'Experience the beauty of Ethiopia with our Meskerem Journey package.', '["Tourist Coaster & Local Travel", "Comfortable 4-star Habesha hospitality", "Historical site guides & entrance fees"]', 'cultural', 1),
('Gojo Expedition', 138.00, 'per day', 'Adventure through Ethiopia\'s hidden gems.', '["Private Transport", "Traditional gear & Habesha cook", "Off-road community permits"]', 'adventure', 1),
('Negus Luxury', 500.00, 'per day', 'Experience Ethiopia in ultimate luxury.', '["Private Air Flight", "Elite Royal Resort & Lodge stay", "VIP private guide", "Sunset dinner"]', 'luxury', 1),
('Gadaa Heritage', 200.00, 'per day', 'Immerse yourself in Ethiopian heritage.', '["Private Land Cruiser", "Authentic Village homestays", "Traditional ceremony participation"]', 'cultural', 1),
('Tizita Express', 200.00, 'per day', 'Quick escape to Ethiopia\'s highlights.', '["Quick Flight & Airport Shuttle", "1 Night premium city stay", "Focused 1-day historical tour"]', 'short_escape', 1),
('Abyssinia Trek', 125.00, 'per day', 'Trek through Ethiopia\'s natural wonders.', '["Local Bus & Mule trekking", "Eco-lodge stay & nature fees", "Endemic wildlife tracking guide"]', 'nature', 1);

UPDATE packages SET destination_id = (SELECT id FROM destinations WHERE name = 'Lalibela' LIMIT 1) WHERE destination_id IS NULL;

-- 11. INSERT ADMIN USER (Password: admin123)
INSERT INTO users (name, email, password, phone, role, trips_completed, total_spent, loyalty_discount) VALUES
('Suheil', 'suheilali777@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0939475495', 'admin', 0, 0.00, 0.00);

-- 12. INSERT SAMPLE USER (Password: user123)
INSERT INTO users (name, email, password, phone, role, trips_completed, total_spent, loyalty_discount) VALUES
('Test User', 'test@ethiotrip.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'user', 2, 450.00, 3.00);

-- ==============================================
-- VERIFICATION QUERIES
-- ==============================================

SELECT '=== DATABASE SETUP COMPLETE ===' AS 'Status';
SELECT '=========================================' AS '';

-- Users
SELECT '✅ USERS:' AS '';
SELECT id, name, email, role, trips_completed, loyalty_discount FROM users;

-- Discount Tiers
SELECT '✅ DISCOUNT TIERS:' AS '';
SELECT tier_name, min_trips, IFNULL(max_trips, '∞') as max_trips, CONCAT(discount_percent, '%') as discount, is_active FROM discount_tiers;

-- Packages
SELECT '✅ PACKAGES:' AS '';
SELECT id, name, CONCAT('$', price) as price, duration, category, is_active FROM packages;

-- Destinations
SELECT '✅ DESTINATIONS:' AS '';
SELECT id, name, location, CONCAT('$', price) as price, is_active FROM destinations;

-- ==============================================
-- LOGIN CREDENTIALS
-- ==============================================

SELECT '=========================================' AS '';
SELECT '🔐 LOGIN CREDENTIALS' AS '';
SELECT '=========================================' AS '';
SELECT 'ADMIN ACCOUNT:' AS '';
SELECT '  Email: suheilali777@gmail.com' AS '';
SELECT '  Password: admin123' AS '';
SELECT '-----------------------------------------' AS '';
SELECT 'TEST USER ACCOUNT:' AS '';
SELECT '  Email: test@ethiotrip.com' AS '';
SELECT '  Password: user123' AS '';
SELECT '=========================================' AS '';

-- ==============================================
-- LOYALTY PROGRAM SUMMARY
-- ==============================================

SELECT '=========================================' AS '';
SELECT '🎖️ LOYALTY PROGRAM' AS '';
SELECT '=========================================' AS '';
SELECT 
    tier_name,
    CONCAT(discount_percent, '%') as discount,
    CASE 
        WHEN min_trips = 0 THEN '0 trips'
        WHEN max_trips IS NULL THEN CONCAT(min_trips, '+ trips')
        ELSE CONCAT(min_trips, ' - ', max_trips, ' trips')
    END as requirement
FROM discount_tiers 
WHERE is_active = 1
ORDER BY min_trips ASC;

-- ==============================================
-- FINAL STATUS
-- ==============================================

SELECT '=========================================' AS '';
SELECT '✅ DATABASE READY!' AS '';
SELECT '=========================================' AS '';
SELECT CONCAT('📊 Total Users: ', (SELECT COUNT(*) FROM users)) AS '';
SELECT CONCAT('📦 Total Packages: ', (SELECT COUNT(*) FROM packages)) AS '';
SELECT CONCAT('📍 Total Destinations: ', (SELECT COUNT(*) FROM destinations)) AS '';
SELECT CONCAT('🏆 Total Discount Tiers: ', (SELECT COUNT(*) FROM discount_tiers)) AS '';
SELECT '=========================================' AS '';
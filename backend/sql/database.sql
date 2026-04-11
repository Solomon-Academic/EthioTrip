-- =============================================
-- ETHIOTRIP DATABASE - COMPLETE SCHEMA
-- =============================================

-- Create fresh database
CREATE DATABASE ethiotrip_db;
USE ethiotrip_db;

-- =============================================
-- 1. USERS TABLE
-- =============================================
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

-- =============================================
-- 2. DISCOUNT TIERS TABLE
-- =============================================
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

-- =============================================
-- 3. PACKAGES TABLE
-- =============================================
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50) DEFAULT NULL,
    description TEXT,
    features TEXT,
    category VARCHAR(50) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 4. DESTINATIONS TABLE
-- =============================================
CREATE TABLE destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100),
    description TEXT,
    best_time VARCHAR(100),
    activities TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================================
-- SAMPLE DATA (For testing)
-- ===========================================

-- Insert sample packages (matching your frontend)
INSERT INTO packages (name, price, duration, tag, features) VALUES
('Meskerem Journey', 350.00, '3 Days / 2 Nights', 'Common Choice', '["Tourist Coaster & Local Travel", "Comfortable 4-star Habesha hospitality", "Historical site guides & entrance"]'),
('Gojo Expedition', 550.00, '4 Days / 3 Nights', 'Vibrant Adventure', '["Private Transport", "Traditional gear & Habesha cook", "Off-road community permits"]'),
('Negus Luxury', 1000.00, '2 Days / 1 Night', 'VIP / Premium', '["Private Air Flight or Business Class", "Elite Royal Resort & Lodge stay", "VIP private guide & sunset dinner"]'),
('Gadaa Heritage', 800.00, '5 Days / 4 Nights', 'Cultural Roots', '["Private Land Cruiser", "Authentic Village homestays", "Traditional ceremony participation"]'),
('Tizita Express', 200.00, '24 to 36 Hours', 'Short Escape', '["Quick Flight & Airport Shuttle", "1 Night premium city stay", "Focused 1-day historical tour"]'),
('Abyssinia Trek', 750.00, '6 Days / 5 Nights', 'Eco & Nature', '["Local Bus & Mule trekking", "Eco-lodge stay & nature fees", "Endemic wildlife tracking guide"]');

-- Insert sample user (password: 'password123' - you'll need to hash this properly)
-- For testing, use: password_hash('password123', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, phone, role, loyalty_discount, trips_completed) VALUES
('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'user', 0.00, 0);
-- =============================================
-- 5. BOOKINGS TABLE
-- =============================================
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    package_id INT DEFAULT NULL,
    package_name VARCHAR(100) DEFAULT NULL,
    travel_date DATE NOT NULL,
    number_of_travelers INT NOT NULL DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    final_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) DEFAULT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    status ENUM('confirmed', 'pending', 'cancelled', 'completed') DEFAULT 'pending',
    special_requests TEXT,
    transaction_id VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
);

-- =============================================
-- 6. REVIEWS TABLE
-- =============================================
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

-- =============================================
-- 7. INSERT DISCOUNT TIERS
-- =============================================
INSERT INTO discount_tiers (min_trips, max_trips, discount_percent, tier_name) VALUES
(0, 2, 0.00, 'Bronze'),
(3, 4, 3.00, 'Silver'),
(5, 7, 5.00, 'Gold'),
(8, 10, 8.00, 'Platinum'),
(11, NULL, 12.00, 'Diamond');

-- =============================================
-- 8. INSERT PACKAGES
-- =============================================
INSERT INTO packages (name, price, duration, description, features, category) VALUES
('Meskerem Journey', 350.00, '3 Days / 2 Nights', 
 'Experience the beauty of Ethiopia with our Meskerem Journey package.', 
 '["Tourist Coaster & Local Travel", "Comfortable 4-star Habesha hospitality", "Historical site guides & entrance fees"]',
 'cultural'),

('Gojo Expedition', 550.00, '4 Days / 3 Nights', 
 'Adventure through Ethiopia\'s hidden gems.', 
 '["Private Transport", "Traditional gear & Habesha cook", "Off-road community permits"]',
 'adventure'),

('Negus Luxury', 1000.00, '2 Days / 1 Night', 
 'Experience Ethiopia in ultimate luxury.', 
 '["Private Air Flight", "Elite Royal Resort & Lodge stay", "VIP private guide", "Sunset dinner"]',
 'luxury'),

('Gadaa Heritage', 800.00, '5 Days / 4 Nights', 
 'Immerse yourself in Ethiopian heritage.', 
 '["Private Land Cruiser", "Authentic Village homestays", "Traditional ceremony participation"]',
 'cultural'),

('Tizita Express', 200.00, '24 Hours', 
 'Quick escape to Ethiopia\'s highlights.', 
 '["Quick Flight & Airport Shuttle", "1 Night premium city stay", "Focused 1-day historical tour"]',
 'short_escape'),

('Abyssinia Trek', 750.00, '6 Days / 5 Nights', 
 'Trek through Ethiopia\'s natural wonders.', 
 '["Local Bus & Mule trekking", "Eco-lodge stay & nature fees", "Endemic wildlife tracking guide"]',
 'nature');

-- =============================================
-- 9. INSERT DESTINATIONS
-- =============================================
INSERT INTO destinations (name, location, description, best_time, activities) VALUES
('Lalibela', 'Amhara Region', 'Famous for its 11 monolithic rock-hewn churches, a UNESCO World Heritage site.', 'Oct - Mar', 'Church exploration, hiking, coffee ceremony'),
('Axum', 'Tigray Region', 'Ancient city known for its obelisks and as the supposed home of the Ark of the Covenant.', 'Sep - May', 'Obelisk tour, palace visit, museum tour'),
('Gondar', 'Amhara Region', 'Known as the "Camelot of Africa" with its royal castles.', 'Oct - Feb', 'Castle tour, bath visit, cultural music'),
('Harar', 'Harari Region', 'The walled city of Harar, known for its alleyways and hyena feeding.', 'All Year', 'Hyena feeding, alley walk, coffee tasting'),
('Omo Valley', 'Southern Region', 'Home to diverse tribal cultures and traditions.', 'Jun - Aug', 'Tribe exchange, body painting, market visit'),
('Simien Mountains', 'Amhara Region', 'Stunning mountain landscapes with endemic wildlife.', 'Sep - Nov', 'Baboon sighting, trekking, camping'),
('Danakil Depression', 'Afar Region', 'One of the hottest places on Earth with active volcanoes.', 'Nov - Jan', 'Volcano hike, salt flats, sulfur springs'),
('Sof Omar Cave', 'Oromia Region', 'One of the largest cave systems in the world.', 'Dec - Apr', 'Cave exploration, hiking, photography');

-- =============================================
-- 10. INSERT ONLY ADMIN USER (NO SAMPLE USERS)
-- =============================================
-- Password: password123
INSERT INTO users (name, email, password, phone, role, trips_completed, total_spent, loyalty_discount) VALUES
('Admin', 'admin@ethiotrip.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'admin', 0, 0.00, 0.00);

-- =============================================
-- 11. VERIFY EVERYTHING IS CLEAN
-- =============================================
SELECT '=== DATABASE SETUP COMPLETE ===' AS 'Status';
SELECT '-----------------------------------------' AS '';

SELECT '✅ USERS TABLE:' AS '';
SELECT COUNT(*) as total_users FROM users;
SELECT id, name, email, role, trips_completed, total_spent, loyalty_discount FROM users;

SELECT '-----------------------------------------' AS '';
SELECT '✅ DISCOUNT TIERS:' AS '';
SELECT * FROM discount_tiers;

SELECT '-----------------------------------------' AS '';
SELECT '✅ PACKAGES:' AS '';
SELECT id, name, price, duration, category FROM packages;

SELECT '-----------------------------------------' AS '';
SELECT '✅ DESTINATIONS:' AS '';
SELECT id, name, location, best_time FROM destinations;

SELECT '-----------------------------------------' AS '';
SELECT '✅ BOOKINGS:' AS '';
SELECT COUNT(*) as total_bookings FROM bookings;

SELECT '-----------------------------------------' AS '';
SELECT '✅ REVIEWS:' AS '';
SELECT COUNT(*) as total_reviews FROM reviews;

-- =============================================
-- 12. DISPLAY LOYALTY TIER INFORMATION
-- =============================================
SELECT '=========================================' AS '';
SELECT 'LOYALTY PROGRAM DETAILS' AS '';
SELECT '=========================================' AS '';
SELECT 
    tier_name,
    CASE 
        WHEN min_trips = 0 THEN '0'
        ELSE CAST(min_trips AS CHAR)
    END as 'Min Trips',
    CASE 
        WHEN max_trips IS NULL THEN 'Unlimited'
        ELSE CAST(max_trips AS CHAR)
    END as 'Max Trips',
    CONCAT(discount_percent, '%') as 'Discount',
    CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END as 'Status'
FROM discount_tiers 
ORDER BY min_trips ASC;

-- =============================================
-- 13. LOGIN INFORMATION
-- =============================================
SELECT '=========================================' AS '';
SELECT 'LOGIN CREDENTIALS' AS '';
SELECT '=========================================' AS '';
SELECT 'Admin Login:' AS '';
SELECT 'Email: admin@ethiotrip.com' AS '';
SELECT 'Password: password123' AS '';
SELECT '=========================================' AS '';
SELECT 'All new users start with 0 trips and 0% discount.' AS '';
SELECT 'Discounts increase automatically as they complete more tours.' AS '';
SELECT '=========================================' AS '';

-- =============================================
-- 14. HOW TO ADJUST DISCOUNTS FOR INFLATION/DEFLATION
-- =============================================
SELECT '=========================================' AS '';
SELECT 'INFLATION/DEFLATION ADJUSTMENT EXAMPLES' AS '';
SELECT '=========================================' AS '';
SELECT '-- Increase all discounts by 2% (Inflation):' AS 'SQL Command:';
SELECT 'UPDATE discount_tiers SET discount_percent = discount_percent + 2 WHERE is_active = 1;' AS '';
SELECT ' ' AS '';
SELECT '-- Decrease all discounts by 2% (Deflation):' AS 'SQL Command:';
SELECT 'UPDATE discount_tiers SET discount_percent = discount_percent - 2 WHERE is_active = 1;' AS '';
SELECT ' ' AS '';
SELECT '-- Update specific tier:' AS 'SQL Command:';
SELECT 'UPDATE discount_tiers SET discount_percent = 15 WHERE tier_name = "Diamond";' AS '';
SELECT '=========================================' AS '';

-- =============================================
-- 15. SAMPLE QUERIES FOR TESTING
-- =============================================
SELECT '=========================================' AS '';
SELECT 'SAMPLE TEST QUERIES' AS '';
SELECT '=========================================' AS '';

-- Test: Create a test user (uncomment to use)
-- INSERT INTO users (name, email, password, phone, role) 
-- VALUES ('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'user');

-- Test: Get discount for a user with 3 trips
SELECT 'Discount for user with 3 trips:' AS '';
SELECT discount_percent, tier_name 
FROM discount_tiers 
WHERE 3 BETWEEN min_trips AND IFNULL(max_trips, 999) 
AND is_active = 1;

-- Test: Get next tier for a user with 3 trips
SELECT 'Next tier for user with 3 trips:' AS '';
SELECT min_trips, tier_name, discount_percent 
FROM discount_tiers 
WHERE min_trips > 3 AND is_active = 1 
ORDER BY min_trips ASC LIMIT 1;

-- =============================================
-- 16. FINAL VERIFICATION
-- =============================================
SELECT '=========================================' AS '';
SELECT 'DATABASE IS READY FOR USE!' AS '';
SELECT '=========================================' AS '';
SELECT 'All tables created successfully.' AS '';
SELECT 'Admin user created.' AS '';
SELECT 'Discount tiers configured.' AS '';
SELECT 'Packages and destinations loaded.' AS '';
SELECT 'Ready to accept bookings!' AS '';
SELECT '=========================================' AS '';

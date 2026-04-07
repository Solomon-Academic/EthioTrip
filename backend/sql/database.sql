-- Drop existing tables if they exist (for clean setup)
DROP DATABASE IF EXISTS ethiotrip_db;
CREATE DATABASE ethiotrip_db;
USE ethiotrip_db;

-- ===========================================
-- USERS TABLE (Complete version)
-- ===========================================
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,           -- Changed from 'full_name' to match PHP code
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),                     -- ADDED: For contact
    role VARCHAR(20) DEFAULT 'user',       -- ADDED: 'user' or 'admin'
    loyalty_discount DECIMAL(3,2) DEFAULT 0.00,  -- ADDED: 0.05, 0.10, 0.15
    trips_completed INT DEFAULT 0,         -- ADDED: Count of completed trips
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ===========================================
-- PACKAGES TABLE (Complete version)
-- ===========================================
CREATE TABLE packages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration VARCHAR(50),
    tag VARCHAR(50),                       -- e.g., 'Common Choice', 'VIP'
    is_active BOOLEAN DEFAULT TRUE,        -- ADDED: To hide/show packages
    features TEXT,                         -- JSON or comma-separated features
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===========================================
-- BOOKINGS TABLE (Complete version)
-- ===========================================
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    package_id INT NOT NULL,
    package_name VARCHAR(100) NOT NULL,    -- Denormalized for quick display
    travel_date DATE NOT NULL,
    number_of_travelers INT DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,   -- Before discount
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    final_amount DECIMAL(10,2) NOT NULL,   -- After discount + tax
    special_requests TEXT,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payment_method VARCHAR(50),            -- ADDED: credit_card, telebirr, paypal, etc.
    transaction_ref VARCHAR(100),          -- ADDED: For payment tracking
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES packages(id)
);

-- ===========================================
-- DESTINATIONS TABLE (Optional - for future use)
-- ===========================================
-- =============================================
-- ETHIOTRIP DATABASE - COMPLETE SCHEMA
-- =============================================

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS ethiotrip_db;
USE ethiotrip_db;

-- =============================================
-- 1. USERS TABLE (with correct columns)
-- =============================================
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS destinations;
DROP TABLE IF EXISTS packages;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    trips_completed INT DEFAULT 0,
    total_spent DECIMAL(10,2) DEFAULT 0.00,
    loyalty_discount DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 2. PACKAGES TABLE
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
-- 3. DESTINATIONS TABLE
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
-- 4. BOOKINGS TABLE
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
-- 5. REVIEWS TABLE
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
-- 6. INSERT SAMPLE PACKAGES
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
-- 7. INSERT SAMPLE DESTINATIONS
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
-- 8. INSERT SAMPLE USERS
-- =============================================
-- Password for all users is: password123
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

INSERT INTO users (name, email, password, phone, role, trips_completed, total_spent, loyalty_discount) VALUES
('Admin User', 'admin@ethiotrip.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'admin', 15, 15000.00, 0.15),
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0912345678', 'user', 5, 2500.00, 0.10),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0987654321', 'user', 2, 700.00, 0.05),
('Test User', 'test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0911223344', 'user', 0, 0.00, 0.00);

-- =============================================
-- 9. INSERT SAMPLE BOOKINGS
-- =============================================
INSERT INTO bookings (user_id, package_id, package_name, travel_date, number_of_travelers, total_amount, discount_amount, final_amount, payment_method, payment_status, status) VALUES
(2, 1, 'Meskerem Journey', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 2, 700.00, 70.00, 630.00, 'credit_card', 'completed', 'confirmed'),
(2, 2, 'Gojo Expedition', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 1, 550.00, 55.00, 495.00, 'telebirr', 'completed', 'confirmed'),
(3, 1, 'Meskerem Journey', DATE_ADD(CURDATE(), INTERVAL 15 DAY), 3, 1050.00, 52.50, 997.50, 'paypal', 'completed', 'confirmed'),
(3, 3, 'Negus Luxury', DATE_ADD(CURDATE(), INTERVAL 60 DAY), 2, 2000.00, 100.00, 1900.00, 'bank_transfer', 'pending', 'pending'),
(4, 5, 'Tizita Express', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 1, 200.00, 0.00, 200.00, 'cash', 'pending', 'pending');

-- =============================================
-- 10. INSERT SAMPLE REVIEWS
-- =============================================
INSERT INTO reviews (user_id, package_id, user_name, rating, comment) VALUES
(2, 1, 'John Doe', 5, 'Amazing experience! The historical sites were breathtaking and the guide was very knowledgeable.'),
(2, 2, 'John Doe', 4, 'Great adventure tour. The off-road experience was thrilling. Would recommend!'),
(3, 1, 'Jane Smith', 5, 'Beautiful landscapes and wonderful hospitality. The coffee ceremony was a highlight.'),
(3, 3, 'Jane Smith', 5, 'Absolutely luxurious! The private flight was incredible. Worth every penny.');

-- =============================================
-- 11. VERIFY ALL TABLES
-- =============================================
SELECT '✅ Users table' AS Status, COUNT(*) AS Count FROM users
UNION ALL
SELECT '✅ Packages table', COUNT(*) FROM packages
UNION ALL
SELECT '✅ Destinations table', COUNT(*) FROM destinations
UNION ALL
SELECT '✅ Bookings table', COUNT(*) FROM bookings
UNION ALL
SELECT '✅ Reviews table', COUNT(*) FROM reviews;

-- =============================================
-- 12. DISPLAY LOGIN INFO
-- =============================================
SELECT '=========================================' AS '';
SELECT 'DATABASE SETUP COMPLETE!' AS '';
SELECT '=========================================' AS '';
SELECT 'Default Login Credentials:' AS '';
SELECT '-----------------------------------------' AS '';
SELECT 'Email: admin@ethiotrip.com | Password: password123 | Role: Admin' AS '';
SELECT 'Email: john@example.com | Password: password123 | Role: User' AS '';
SELECT 'Email: jane@example.com | Password: password123 | Role: User' AS '';
SELECT 'Email: test@example.com | Password: password123 | Role: User' AS '';
SELECT '=========================================' AS '';

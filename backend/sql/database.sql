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
CREATE TABLE destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    best_time VARCHAR(100),
    description TEXT,
    image_path VARCHAR(255),
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
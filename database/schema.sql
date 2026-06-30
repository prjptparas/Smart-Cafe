-- ============================================================
-- Smart Cafe Ordering System - Database Schema
-- Version: 1.0
-- Engine: MySQL 8+ (InnoDB)
-- ============================================================

-- Create the database
CREATE DATABASE IF NOT EXISTS smart_cafe
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE smart_cafe;

-- ============================================================
-- 1. ADMINS TABLE
-- Stores admin/staff login credentials
-- ============================================================
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. CATEGORIES TABLE
-- Food categories (e.g., Beverages, Main Course)
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) DEFAULT NULL,
    icon VARCHAR(50) DEFAULT 'bi-tag',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 3. FOODS TABLE
-- Individual food/drink items linked to a category
-- ============================================================
CREATE TABLE IF NOT EXISTS foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT 'default-food.jpg',
    is_veg TINYINT(1) DEFAULT 1,
    is_available TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    prep_time INT DEFAULT 15,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_available (is_available),
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB;

-- ============================================================
-- 4. ORDERS TABLE
-- Customer orders with status tracking
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    table_number INT NOT NULL,
    customer_name VARCHAR(100) DEFAULT 'Guest',
    customer_phone VARCHAR(20) DEFAULT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('pending','confirmed','preparing','ready','delivered','cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50) DEFAULT 'cash',
    special_instructions TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_order_number (order_number),
    INDEX idx_table (table_number),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- 5. ORDER ITEMS TABLE
-- Individual items within an order (snapshot of food at time of order)
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_id INT DEFAULT NULL,
    food_name VARCHAR(150) NOT NULL,
    food_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
) ENGINE=InnoDB;

-- ============================================================
-- 6. FEEDBACK TABLE
-- Customer ratings and comments
-- ============================================================
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT DEFAULT NULL,
    customer_name VARCHAR(100) DEFAULT 'Anonymous',
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    INDEX idx_rating (rating),
    INDEX idx_read (is_read)
) ENGINE=InnoDB;

-- ============================================================
-- 7. SETTINGS TABLE
-- Application configuration (key-value store)
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ============================================================
-- DEFAULT DATA
-- ============================================================

-- Default admin (password: Admin@123)
INSERT INTO admins (username, email, password, full_name) VALUES
('admin', 'admin@smartcafe.com', '$2y$10$8K1p/a0dN1hZ2JQrXxG0/.ypHKZ.dPqVGNgeXEJBQ3xFpZhBa/C0G', 'Cafe Administrator');

-- Default settings
INSERT INTO settings (setting_key, setting_value) VALUES
('cafe_name', 'Smart Cafe'),
('cafe_tagline', 'Delicious Food, Smart Ordering'),
('cafe_phone', '+91 98765 43210'),
('cafe_email', 'hello@smartcafe.com'),
('cafe_address', '123 Food Street, Tech City, India'),
('tax_rate', '5'),
('currency_symbol', '₹'),
('total_tables', '20'),
('order_prefix', 'SC'),
('min_order_amount', '50');

-- Categories
INSERT INTO categories (name, description, icon, sort_order) VALUES
('Hot Beverages', 'Freshly brewed coffee, tea and more', 'bi-cup-hot', 1),
('Cold Beverages', 'Refreshing cold drinks and shakes', 'bi-cup-straw', 2),
('Starters', 'Appetizers to kick-start your meal', 'bi-fire', 3),
('Main Course', 'Hearty main dishes for a fulfilling meal', 'bi-egg-fried', 4),
('Desserts', 'Sweet treats to end your meal perfectly', 'bi-cake2', 5),
('Quick Bites', 'Light snacks and finger foods', 'bi-lightning', 6);

-- Food items
INSERT INTO foods (category_id, name, description, price, image, is_veg, is_available, is_featured, prep_time) VALUES
-- Hot Beverages
(1, 'Classic Espresso', 'Rich and bold single-shot espresso made from premium Arabica beans', 120.00, 'espresso.jpg', 1, 1, 1, 5),
(1, 'Cappuccino', 'Creamy cappuccino with steamed milk foam and a dusting of cocoa', 180.00, 'cappuccino.jpg', 1, 1, 1, 7),
(1, 'Masala Chai', 'Traditional Indian spiced tea brewed with cardamom and ginger', 80.00, 'masala-chai.jpg', 1, 1, 0, 5),
(1, 'Hot Chocolate', 'Velvety smooth hot chocolate topped with whipped cream', 200.00, 'hot-chocolate.jpg', 1, 1, 0, 8),

-- Cold Beverages
(2, 'Iced Latte', 'Chilled espresso with cold milk over ice cubes', 200.00, 'iced-latte.jpg', 1, 1, 1, 5),
(2, 'Mango Smoothie', 'Fresh Alphonso mango blended with yogurt and honey', 220.00, 'mango-smoothie.jpg', 1, 1, 1, 7),
(2, 'Fresh Lime Soda', 'Tangy lime juice with soda water and a hint of mint', 100.00, 'lime-soda.jpg', 1, 1, 0, 3),
(2, 'Cold Coffee', 'Blended iced coffee with vanilla ice cream', 180.00, 'cold-coffee.jpg', 1, 1, 0, 6),

-- Starters
(3, 'Paneer Tikka', 'Marinated cottage cheese grilled to perfection with spices', 280.00, 'paneer-tikka.jpg', 1, 1, 1, 15),
(3, 'Chicken Wings', 'Crispy fried chicken wings tossed in spicy buffalo sauce', 320.00, 'chicken-wings.jpg', 0, 1, 1, 18),
(3, 'Spring Rolls', 'Crispy vegetable spring rolls served with sweet chili dip', 200.00, 'spring-rolls.jpg', 1, 1, 0, 12),
(3, 'Fish Fingers', 'Golden fried fish fingers served with tartar sauce', 300.00, 'fish-fingers.jpg', 0, 1, 0, 15),

-- Main Course
(4, 'Margherita Pizza', 'Classic pizza with fresh mozzarella, basil and tomato sauce', 350.00, 'margherita-pizza.jpg', 1, 1, 1, 20),
(4, 'Chicken Biryani', 'Aromatic basmati rice layered with tender spiced chicken', 380.00, 'chicken-biryani.jpg', 0, 1, 1, 25),
(4, 'Veg Burger', 'Crunchy veggie patty with lettuce, cheese and special sauce', 220.00, 'veg-burger.jpg', 1, 1, 0, 12),
(4, 'Pasta Alfredo', 'Creamy white sauce pasta with mushrooms and herbs', 300.00, 'pasta-alfredo.jpg', 1, 1, 0, 15),
(4, 'Butter Chicken', 'Tender chicken pieces in a rich, creamy tomato gravy', 360.00, 'butter-chicken.jpg', 0, 1, 1, 20),
(4, 'Paneer Butter Masala', 'Soft paneer cubes in a velvety butter tomato sauce', 320.00, 'paneer-butter-masala.jpg', 1, 1, 0, 18),

-- Desserts
(5, 'Chocolate Brownie', 'Warm fudgy brownie served with vanilla ice cream', 250.00, 'chocolate-brownie.jpg', 1, 1, 1, 5),
(5, 'Gulab Jamun', 'Soft milk dumplings soaked in rose-flavored sugar syrup', 150.00, 'gulab-jamun.jpg', 1, 1, 0, 3),
(5, 'Cheesecake', 'New York style baked cheesecake with berry compote', 280.00, 'cheesecake.jpg', 1, 1, 1, 3),
(5, 'Ice Cream Sundae', 'Three scoops of ice cream with chocolate sauce and nuts', 220.00, 'ice-cream-sundae.jpg', 1, 1, 0, 5),

-- Quick Bites
(6, 'French Fries', 'Crispy golden french fries with ketchup and mayo', 150.00, 'french-fries.jpg', 1, 1, 1, 8),
(6, 'Garlic Bread', 'Toasted bread with garlic butter and herbs', 160.00, 'garlic-bread.jpg', 1, 1, 0, 8),
(6, 'Nachos Supreme', 'Loaded nachos with cheese sauce, jalapenos and salsa', 220.00, 'nachos.jpg', 1, 1, 0, 10),
(6, 'Club Sandwich', 'Triple-layer sandwich with chicken, egg and veggies', 250.00, 'club-sandwich.jpg', 0, 1, 0, 12);

-- Sample orders for dashboard demo
INSERT INTO orders (order_number, table_number, customer_name, customer_phone, subtotal, tax, total, status, payment_method, special_instructions, created_at) VALUES
('SC-20260628-001', 3, 'Rahul Sharma', '9876543210', 730.00, 36.50, 766.50, 'delivered', 'cash', 'No onions please', NOW() - INTERVAL 3 HOUR),
('SC-20260628-002', 7, 'Priya Patel', '9123456780', 480.00, 24.00, 504.00, 'delivered', 'upi', NULL, NOW() - INTERVAL 2 HOUR),
('SC-20260628-003', 1, 'Amit Kumar', '9988776655', 620.00, 31.00, 651.00, 'preparing', 'cash', 'Extra spicy', NOW() - INTERVAL 30 MINUTE),
('SC-20260628-004', 12, 'Sneha Reddy', '8877665544', 350.00, 17.50, 367.50, 'confirmed', 'card', NULL, NOW() - INTERVAL 15 MINUTE),
('SC-20260628-005', 5, 'Guest', NULL, 280.00, 14.00, 294.00, 'pending', 'cash', NULL, NOW() - INTERVAL 5 MINUTE);

-- Sample order items
INSERT INTO order_items (order_id, food_id, food_name, food_price, quantity, subtotal) VALUES
(1, 13, 'Margherita Pizza', 350.00, 1, 350.00),
(1, 14, 'Chicken Biryani', 380.00, 1, 380.00),
(2, 5, 'Iced Latte', 200.00, 2, 400.00),
(2, 3, 'Masala Chai', 80.00, 1, 80.00),
(3, 17, 'Butter Chicken', 360.00, 1, 360.00),
(3, 15, 'Veg Burger', 220.00, 1, 220.00),
(3, 7, 'Fresh Lime Soda', 100.00, 1, 100.00),
-- Adjust: order 3 subtotal should match; keeping simple for demo
(4, 13, 'Margherita Pizza', 350.00, 1, 350.00),
(5, 19, 'Chocolate Brownie', 250.00, 1, 250.00),
(5, 3, 'Masala Chai', 80.00, 1, 80.00);

-- Recalculate order 3 totals to match items (360+220+100=680)
UPDATE orders SET subtotal = 680.00, tax = 34.00, total = 714.00 WHERE id = 3;
-- Recalculate order 5 (250+80=330)
UPDATE orders SET subtotal = 330.00, tax = 16.50, total = 346.50 WHERE id = 5;

-- Sample feedback
INSERT INTO feedback (order_id, customer_name, rating, comment, is_read) VALUES
(1, 'Rahul Sharma', 5, 'Amazing food and quick service! The pizza was perfectly baked.', 1),
(2, 'Priya Patel', 4, 'Good coffee. Would love to see more cold brew options.', 0),
(1, 'Amit Kumar', 5, 'Best biryani I have had in a while. Will definitely come back!', 0);

-- PM Dairy Farm Database Schema
-- MySQL 5.7+ / MariaDB 10.3+

CREATE DATABASE IF NOT EXISTS pm_dairy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pm_dairy;

-- =====================
-- USERS TABLE
-- =====================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL UNIQUE,
    email VARCHAR(150) UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(500) DEFAULT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    otp_code VARCHAR(6) DEFAULT NULL,
    otp_expires_at DATETIME DEFAULT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- =====================
-- USER ADDRESSES TABLE
-- =====================
CREATE TABLE user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(50) DEFAULT 'Home',
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) DEFAULT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) DEFAULT 'Haryana',
    pincode VARCHAR(10) NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB;

-- =====================
-- PRODUCT CATEGORIES
-- =====================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    image VARCHAR(500) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================
-- PRODUCTS TABLE
-- =====================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500),
    nutrition_info TEXT,
    price DECIMAL(10,2) NOT NULL,
    compare_price DECIMAL(10,2) DEFAULT NULL,
    unit VARCHAR(50) NOT NULL DEFAULT 'litre',
    weight VARCHAR(50) DEFAULT NULL,
    stock INT DEFAULT 0,
    sku VARCHAR(50) DEFAULT NULL,
    images JSON DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    is_available TINYINT(1) DEFAULT 1,
    meta_title VARCHAR(200) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_featured (is_featured),
    INDEX idx_available (is_available),
    FULLTEXT idx_search (name, description, short_description)
) ENGINE=InnoDB;

-- =====================
-- ORDERS TABLE
-- =====================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    delivery_charge DECIMAL(10,2) DEFAULT 0.00,
    discount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    delivery_date DATE DEFAULT NULL,
    delivery_time_slot VARCHAR(50) DEFAULT NULL,
    payment_method ENUM('cod', 'online') DEFAULT 'cod',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    cf_order_id VARCHAR(100) DEFAULT NULL,
    cf_payment_id VARCHAR(100) DEFAULT NULL,
    payment_session_id VARCHAR(255) DEFAULT NULL,
    order_status ENUM('pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    stock_reserved TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_status (order_status),
    INDEX idx_payment (payment_status),
    INDEX idx_order_number (order_number),
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

-- =====================
-- ORDER ITEMS TABLE
-- =====================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_order (order_id)
) ENGINE=InnoDB;

-- =====================
-- CART TABLE
-- =====================
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(100) DEFAULT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_session (session_id)
) ENGINE=InnoDB;

-- =====================
-- SUBSCRIPTIONS TABLE
-- =====================
CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_type ENUM('daily', 'weekly', 'monthly') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    delivery_time VARCHAR(50) DEFAULT 'morning',
    total_price DECIMAL(10,2) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    status ENUM('active', 'paused', 'cancelled', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =====================
-- SUBSCRIPTION ITEMS
-- =====================
CREATE TABLE subscription_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (subscription_id) REFERENCES subscriptions(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- =====================
-- BLOGS TABLE
-- =====================
CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(300) NOT NULL,
    slug VARCHAR(320) NOT NULL UNIQUE,
    content LONGTEXT NOT NULL,
    excerpt VARCHAR(500) DEFAULT NULL,
    featured_image VARCHAR(500) DEFAULT NULL,
    category ENUM('health-tips', 'farm-updates', 'dairy-knowledge', 'recipes') DEFAULT 'farm-updates',
    author VARCHAR(100) DEFAULT 'PM Dairy',
    is_published TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    meta_title VARCHAR(200) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_category (category),
    INDEX idx_published (is_published),
    FULLTEXT idx_search (title, content, excerpt)
) ENGINE=InnoDB;

-- =====================
-- FARM VISITS TABLE
-- =====================
CREATE TABLE farm_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    preferred_date DATE NOT NULL,
    preferred_time VARCHAR(50) DEFAULT NULL,
    number_of_people INT DEFAULT 1,
    message TEXT DEFAULT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_date (preferred_date)
) ENGINE=InnoDB;

-- =====================
-- TESTIMONIALS TABLE
-- =====================
CREATE TABLE testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100) DEFAULT 'Customer',
    content TEXT NOT NULL,
    rating TINYINT DEFAULT 5,
    avatar VARCHAR(500) DEFAULT NULL,
    is_featured TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_featured (is_featured)
) ENGINE=InnoDB;

-- =====================
-- CONTACT MESSAGES
-- =====================
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(15) DEFAULT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =====================
-- SITE SETTINGS
-- =====================
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================
-- DEFAULT SITE SETTINGS
-- =====================
INSERT INTO site_settings (setting_key, setting_value) VALUES
('delivery_charge', '50'),
('free_delivery_above', '500'),
('contact_phone', '+91-9876543210'),
('contact_email', 'info@pmdairy.com'),
('whatsapp_number', '919876543210'),
('farm_address', 'PM Dairy Farm, Haryana, India'),
('farm_latitude', '29.0588'),
('farm_longitude', '76.0856');

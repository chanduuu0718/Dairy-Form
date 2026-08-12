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
    visitors INT NOT NULL DEFAULT 1,
    message TEXT DEFAULT NULL,
    status ENUM('pending', 'confirmed', 'rejected', 'completed') DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
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
    customer_name VARCHAR(100) NOT NULL,
    customer_photo VARCHAR(500) DEFAULT NULL,
    customer_location VARCHAR(100) DEFAULT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    review_text TEXT NOT NULL,
    is_approved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_approved (is_approved)
) ENGINE=InnoDB;

-- =====================
-- CONTACTS TABLE
-- =====================
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(15) DEFAULT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read)
) ENGINE=InnoDB;

-- =====================
-- CATTLE/BREEDS TABLE
-- =====================
CREATE TABLE cattle_breeds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    origin VARCHAR(100) DEFAULT NULL,
    image VARCHAR(500) DEFAULT NULL,
    daily_milk_capacity VARCHAR(50) DEFAULT NULL,
    specialty TEXT DEFAULT NULL,
    fun_fact TEXT DEFAULT NULL,
    health_benefits TEXT DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================
-- GALLERY TABLE
-- =====================
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) DEFAULT NULL,
    image VARCHAR(500) NOT NULL,
    category ENUM('farm', 'cattle', 'products', 'team', 'events') DEFAULT 'farm',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB;

-- =====================
-- SITE SETTINGS TABLE
-- =====================
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================
-- SEED DEFAULT DATA
-- =====================

-- Admin user (password: admin123)
INSERT INTO users (name, phone, email, password, role, is_verified) VALUES
('PM Dairy Admin', '9876543210', 'admin@pmdairy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Categories
INSERT INTO categories (name, slug, description, sort_order) VALUES
('Milk', 'milk', 'Fresh farm milk - cow and buffalo', 1),
('Ghee', 'ghee', 'Pure desi ghee made from fresh cream', 2),
('Paneer', 'paneer', 'Soft and fresh homemade paneer', 3),
('Dahi', 'dahi', 'Thick and creamy natural dahi', 4),
('Chhach', 'chhach', 'Refreshing traditional buttermilk', 5),
('Cream', 'cream', 'Fresh malai cream', 6),
('Khoya', 'khoya', 'Pure khoya/mawa for sweets', 7),
('Butter', 'butter', 'Fresh white butter (makhan)', 8);

-- Products
INSERT INTO products (category_id, name, slug, description, short_description, nutrition_info, price, compare_price, unit, weight, stock, is_featured, is_available) VALUES
(1, 'Farm Fresh Cow Milk', 'farm-fresh-cow-milk', 'Pure A2 cow milk from our Gir and Sahiwal cows. No preservatives, no added water. Delivered fresh every morning from our farm to your doorstep.', 'Pure A2 cow milk, farm fresh daily', '{"calories":"67 kcal","protein":"3.2g","fat":"4.1g","carbs":"4.7g","calcium":"120mg"}', 70.00, 80.00, 'litre', '1 Litre', 500, 1, 1),
(1, 'Buffalo Milk (Full Cream)', 'buffalo-milk-full-cream', 'Rich and creamy buffalo milk from Murrah breed. High in fat content, perfect for making paneer, khoya and sweets at home.', 'Rich Murrah buffalo milk, full cream', '{"calories":"97 kcal","protein":"3.7g","fat":"6.5g","carbs":"5.2g","calcium":"210mg"}', 80.00, 90.00, 'litre', '1 Litre', 500, 1, 1),
(2, 'Pure Desi Cow Ghee', 'pure-desi-cow-ghee', 'Bilona method desi ghee made from A2 cow milk. Golden color with rich aroma. Made using traditional hand-churned method for maximum nutrition.', 'A2 cow ghee, bilona method', '{"calories":"900 kcal","protein":"0g","fat":"99.8g","carbs":"0g","vitamin_a":"840mcg"}', 2200.00, 2500.00, 'kg', '1 Kg', 100, 1, 1),
(2, 'Buffalo Ghee', 'buffalo-ghee', 'Pure buffalo ghee with white creamy texture. Perfect for cooking, tadka, and making sweets. Rich in nutrients and full of flavor.', 'Pure buffalo ghee, traditional method', '{"calories":"897 kcal","protein":"0g","fat":"99.5g","carbs":"0g","vitamin_a":"684mcg"}', 1800.00, 2000.00, 'kg', '1 Kg', 80, 0, 1),
(3, 'Fresh Paneer', 'fresh-paneer', 'Soft, fresh paneer made from full cream milk. No preservatives or chemicals. Perfect for all your paneer dishes - butter paneer, palak paneer, paneer tikka and more.', 'Fresh homemade paneer, soft & creamy', '{"calories":"265 kcal","protein":"18.3g","fat":"20.8g","carbs":"1.2g","calcium":"208mg"}', 400.00, 450.00, 'kg', '1 Kg', 50, 1, 1),
(4, 'Set Dahi (Curd)', 'set-dahi-curd', 'Thick, creamy set dahi made from full cream milk. Natural fermentation, no artificial cultures. Rich in probiotics for gut health.', 'Natural set dahi, thick & creamy', '{"calories":"98 kcal","protein":"11g","fat":"3.1g","carbs":"4.7g","calcium":"150mg"}', 60.00, 70.00, 'kg', '500g', 200, 1, 1),
(5, 'Traditional Chhach', 'traditional-chhach', 'Refreshing traditional buttermilk (chhach) with cumin, mint and a hint of salt. Perfect summer drink made from fresh dahi.', 'Traditional spiced buttermilk', '{"calories":"40 kcal","protein":"3.1g","fat":"0.9g","carbs":"4.8g","calcium":"116mg"}', 30.00, NULL, 'litre', '1 Litre', 300, 0, 1),
(6, 'Fresh Malai (Cream)', 'fresh-malai-cream', 'Thick fresh cream (malai) collected from full cream milk. Use for desserts, coffee, or cooking. Pure and unadulterated.', 'Fresh milk cream, thick & pure', '{"calories":"195 kcal","protein":"2.5g","fat":"20g","carbs":"3.4g","calcium":"65mg"}', 500.00, 550.00, 'kg', '500g', 40, 0, 1),
(7, 'Pure Khoya (Mawa)', 'pure-khoya-mawa', 'Freshly made khoya from pure milk. Ideal for making gulab jamun, barfi, peda and other traditional sweets. Made daily on order.', 'Fresh khoya for sweets', '{"calories":"458 kcal","protein":"20g","fat":"30.5g","carbs":"25.7g","calcium":"650mg"}', 600.00, 650.00, 'kg', '1 Kg', 30, 0, 1),
(8, 'Fresh White Butter (Makhan)', 'fresh-white-butter', 'Hand-churned fresh white butter (makhan) from dahi. No salt or preservatives. The desi makhan loved by Lord Krishna!', 'Hand-churned fresh white butter', '{"calories":"729 kcal","protein":"0.9g","fat":"81g","carbs":"0.1g","vitamin_a":"684mcg"}', 800.00, 900.00, 'kg', '500g', 40, 1, 1);

-- Cattle breeds
INSERT INTO cattle_breeds (name, slug, origin, daily_milk_capacity, specialty, fun_fact, health_benefits, sort_order) VALUES
('Gir Cow', 'gir-cow', 'Gujarat, India', '12-15 Litres', 'Known for A2 protein milk. Gir cows are one of the principal Zebu breeds originating in India. They are prized for heat tolerance and disease resistance.', 'Gir cows can recognize up to 100 different humans and remember them for years!', 'A2 milk from Gir cows contains beta-casein protein that is easier to digest and may reduce inflammation, improve heart health, and boost immunity.', 1),
('Sahiwal', 'sahiwal', 'Punjab, Pakistan/India', '10-16 Litres', 'Best indigenous dairy breed with highest milk production among Zebu breeds. Known for calm temperament and adaptability to hot climates.', 'Sahiwal cows were the breed of choice for Australian dairy farmers in tropical regions!', 'Sahiwal milk is rich in A2 protein and has higher fat content, making it ideal for ghee production. The milk is believed to boost brain development in children.', 2),
('Holstein Friesian (HF)', 'holstein-friesian', 'Netherlands', '25-40 Litres', 'Highest milk-producing breed globally. Recognizable by black and white spotted coat. Adapted worldwide for commercial dairy farming.', 'A single HF cow can produce enough milk in her lifetime to fill a swimming pool!', 'HF milk is high in protein and calcium. While it contains A1 protein, it is excellent for making cheese, yogurt, and other dairy products.', 3),
('Jersey', 'jersey', 'Jersey Island, UK', '15-25 Litres', 'Known for rich, creamy milk with highest butterfat content. Smaller breed, gentle temperament. Efficient feed-to-milk conversion.', 'Jersey milk has 18% more protein and 20% more calcium than average milk!', 'Jersey milk has the highest butterfat and protein content of any breed, making it superior for ghee, butter, and cheese production. Rich in beta-carotene giving it a golden color.', 4),
('Murrah Buffalo', 'murrah-buffalo', 'Haryana, India', '15-20 Litres', 'India\'s pride - the jet black Murrah buffalo is the highest milk-producing buffalo breed. Known for distinctive curled horns and docile nature.', 'Murrah buffaloes are called "Black Gold" in Haryana because of their immense economic value!', 'Murrah buffalo milk has 7-8% fat content, making it the richest milk available. High in calcium, iron, and phosphorus. Ideal for growing children and making premium quality paneer and khoya.', 5);

-- Testimonials
INSERT INTO testimonials (customer_name, customer_location, rating, review_text, is_approved) VALUES
('Rajesh Kumar', 'Kurukshetra', 5, 'We have been ordering milk from PM Dairy for over 2 years now. The quality is consistently excellent. You can actually taste the difference between their fresh farm milk and packaged milk from the market.', 1),
('Sunita Devi', 'Pehowa', 5, 'The ghee from PM Dairy is just like my grandmother used to make. Pure bilona method, amazing aroma. My family refuses to eat food made with any other ghee now!', 1),
('Dr. Amit Sharma', 'Karnal', 5, 'As a doctor, I recommend PM Dairy products to my patients. Their A2 cow milk is genuine and lab-tested. My lactose-intolerant patients have reported much better digestion with their milk.', 1),
('Priya Gupta', 'Ambala', 4, 'Their paneer is so soft and fresh! I ordered for my daughter\'s birthday party and everyone was asking where I got such amazing paneer from. Delivery was on time too.', 1),
('Harinder Singh', 'Kurukshetra', 5, 'I visited their farm with my family. It was an eye-opening experience. The cattle are so well maintained, clean sheds, and the whole process is hygienic. Truly impressed!', 1),
('Meena Rani', 'Pehowa', 5, 'Their subscription plan is very convenient. Fresh milk at my doorstep every morning at 6 AM. Never missed a single delivery in 8 months. Highly recommended!', 1);

-- Blog posts
INSERT INTO blogs (title, slug, content, excerpt, category, author, is_published, published_at) VALUES
('Why A2 Milk is Better for Your Health', 'why-a2-milk-is-better-for-health', '<p>A2 milk has been gaining popularity across India, and for good reason. Unlike regular milk that contains both A1 and A2 beta-casein proteins, A2 milk contains only the A2 protein, which is naturally produced by indigenous cow breeds like Gir and Sahiwal.</p><h3>What Makes A2 Milk Different?</h3><p>The key difference lies in the protein structure. A1 protein can break down into BCM-7, a peptide linked to digestive discomfort in some people. A2 milk avoids this issue entirely.</p><h3>Health Benefits</h3><ul><li>Easier to digest, even for lactose-sensitive individuals</li><li>May reduce inflammation in the gut</li><li>Rich in Omega-3 fatty acids</li><li>Contains higher levels of Vitamin A and antioxidants</li><li>Supports better brain function</li></ul><p>At PM Dairy, all our cow milk comes from Gir and Sahiwal breeds that naturally produce A2 milk. We never mix breeds or add any preservatives.</p>', 'Discover why A2 milk from indigenous cow breeds is gaining popularity and how it benefits your health compared to regular A1 milk.', 'health-tips', 'PM Dairy', 1, NOW()),
('The Traditional Bilona Method of Making Ghee', 'traditional-bilona-method-ghee', '<p>In the age of industrial dairy processing, we at PM Dairy still follow the ancient Bilona method to make our ghee. This traditional technique has been used in Indian households for thousands of years.</p><h3>The Process</h3><p>Step 1: Fresh milk is boiled and cooled. Step 2: A spoonful of previous day''s dahi is added as culture. Step 3: Milk sets into thick dahi overnight. Step 4: Dahi is hand-churned (bilona) using a wooden churner. Step 5: Butter (makhan) is separated from buttermilk. Step 6: Butter is slowly heated on low flame until golden ghee forms.</p><h3>Why Bilona Ghee is Superior</h3><p>Unlike commercial ghee made directly from cream, Bilona ghee retains all the beneficial bacteria cultures from the dahi stage. It has a richer aroma, deeper color, and better nutrition profile.</p>', 'Learn about the traditional Bilona method of making ghee that has been practiced in India for thousands of years and why it produces superior quality ghee.', 'dairy-knowledge', 'PM Dairy', 1, NOW()),
('5 Easy Paneer Recipes for Everyday Cooking', 'easy-paneer-recipes-everyday', '<p>Paneer is one of the most versatile ingredients in Indian cooking. Here are 5 simple recipes you can make with PM Dairy''s fresh paneer.</p><h3>1. Paneer Bhurji</h3><p>Crumble fresh paneer and cook with onions, tomatoes, green chilies, and spices. Ready in 10 minutes!</p><h3>2. Palak Paneer</h3><p>A classic North Indian dish combining spinach puree with soft paneer cubes in a creamy gravy.</p><h3>3. Paneer Tikka</h3><p>Marinate paneer cubes in yogurt and spices, then grill or cook in tandoor for smoky flavor.</p><h3>4. Paneer Paratha</h3><p>Stuff grated paneer with spices into wheat dough for a filling breakfast option.</p><h3>5. Paneer Butter Masala</h3><p>The king of paneer dishes - rich tomato-cream gravy with golden fried paneer cubes.</p>', 'Discover 5 easy and delicious paneer recipes that you can make at home with fresh farm paneer for everyday meals.', 'recipes', 'PM Dairy', 1, NOW());

-- Site Settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('total_cattle', '150+'),
('daily_production', '2000+'),
('years_experience', '15+'),
('happy_customers', '5000+'),
('farm_area', '50 Acres'),
('delivery_charge', '0'),
('min_order_amount', '100'),
('delivery_time_slots', '["6:00 AM - 8:00 AM", "8:00 AM - 10:00 AM", "4:00 PM - 6:00 PM", "6:00 PM - 8:00 PM"]');

-- Database Schema for Petroleum Gas Website
-- This replaces WordPress database structure with custom tables

CREATE DATABASE IF NOT EXISTS petroleum_gas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE petroleum_gas_db;

-- Site Options Table (replaces wp_options)
CREATE TABLE site_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    option_name VARCHAR(191) UNIQUE NOT NULL,
    option_value LONGTEXT,
    autoload VARCHAR(20) DEFAULT 'yes',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Pages Table (replaces wp_posts for pages)
CREATE TABLE pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    status ENUM('draft', 'published', 'private') DEFAULT 'draft',
    meta_title VARCHAR(255),
    meta_description TEXT,
    featured_image VARCHAR(255),
    page_template VARCHAR(100) DEFAULT 'default',
    parent_id INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_slug (slug),
    INDEX idx_parent (parent_id)
);

-- Posts/Blog Table
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content LONGTEXT,
    excerpt TEXT,
    status ENUM('draft', 'published', 'private') DEFAULT 'draft',
    featured_image VARCHAR(255),
    category_id INT,
    author_id INT,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_slug (slug),
    INDEX idx_category (category_id)
);

-- Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu Items Table (replaces WordPress menus)
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    url VARCHAR(500) NOT NULL,
    menu_location VARCHAR(50) DEFAULT 'primary',
    parent_id INT DEFAULT 0,
    sort_order INT DEFAULT 0,
    has_children BOOLEAN DEFAULT FALSE,
    css_class VARCHAR(255),
    target VARCHAR(20) DEFAULT '_self',
    icon_class VARCHAR(100),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_menu_location (menu_location),
    INDEX idx_parent (parent_id),
    INDEX idx_sort_order (sort_order)
);

-- Products Table (for e-commerce functionality)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description LONGTEXT,
    short_description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2),
    sku VARCHAR(100) UNIQUE,
    stock_quantity INT DEFAULT 0,
    manage_stock BOOLEAN DEFAULT TRUE,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    image_url VARCHAR(500),
    gallery_images JSON,
    weight DECIMAL(8,2),
    dimensions VARCHAR(100),
    category_id INT,
    views INT DEFAULT 0,
    sales_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_category (category_id),
    INDEX idx_sku (sku)
);

-- Product Categories Table
CREATE TABLE product_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    parent_id INT DEFAULT 0,
    image_url VARCHAR(500),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    display_name VARCHAR(250),
    role ENUM('admin', 'editor', 'customer') DEFAULT 'customer',
    status ENUM('active', 'inactive') DEFAULT 'active',
    email_verified BOOLEAN DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
);

-- Contact Form Submissions
CREATE TABLE contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message LONGTEXT NOT NULL,
    phone VARCHAR(50),
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_email (email)
);

-- Settings/Configuration
CREATE TABLE configurations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) UNIQUE NOT NULL,
    config_value LONGTEXT,
    config_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Custom Fields/Meta Data
CREATE TABLE meta_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    object_type ENUM('page', 'post', 'product', 'user') NOT NULL,
    object_id INT NOT NULL,
    meta_key VARCHAR(255) NOT NULL,
    meta_value LONGTEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_object (object_type, object_id),
    INDEX idx_meta_key (meta_key)
);

-- SEO Data
CREATE TABLE seo_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    object_type ENUM('page', 'post', 'product') NOT NULL,
    object_id INT NOT NULL,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords VARCHAR(500),
    og_title VARCHAR(255),
    og_description TEXT,
    og_image VARCHAR(500),
    canonical_url VARCHAR(500),
    robots_meta VARCHAR(100) DEFAULT 'index,follow',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_object (object_type, object_id)
);

-- Insert Default Data
-- Site Options
INSERT INTO site_options (option_name, option_value) VALUES
('site_title', 'Lake Energies'),
('site_description', 'Petroleum, Gas and Oil Industry Solutions'),
('logo_url', 'uploads/Lake-Logos-ALL-36-768x443.png'),
('logo_sticky_url', 'uploads/Lake-Logos-ALL-36-768x443.png'),
('site_email', 'info@petroleumgas.com'),
('site_phone', '+1-555-0123'),
('address', '123 Industry Street, Oil City, TX 12345'),
('google_maps_api_key', 'your-google-maps-api-key'),
('analytics_code', 'UA-XXXXXXXX-X');

-- Default Pages
INSERT INTO pages (title, slug, content, status, meta_title, meta_description) VALUES
('Home', 'home', '<h1>Welcome to Petroleum and Gas Solutions</h1><p>Your trusted partner in oil and gas industry solutions.</p>', 'published', 'Home - Petroleum and Gas Solutions', 'Leading provider of oil and gas industry solutions and services.'),
('About Us', 'about', '<h1>About Our Company</h1><p>We are a leading company in the petroleum and gas industry.</p>', 'published', 'About Us - Our Story', 'Learn more about our company history and mission in the oil and gas industry.'),
('Services', 'services', '<h1>Our Services</h1><p>We offer comprehensive services for the oil and gas industry.</p>', 'published', 'Services - What We Offer', 'Comprehensive oil and gas industry services including exploration, drilling, and refining.'),
('Contact', 'contact', '<h1>Contact Us</h1><p>Get in touch with our team for your petroleum and gas needs.</p>', 'published', 'Contact Us', 'Contact our petroleum and gas experts for consultation and services.');

-- Menu Items
INSERT INTO menu_items (title, url, menu_location, parent_id, sort_order, css_class) VALUES
('Home', '/', 'primary', 0, 1, 'active_menu color-2'),
('About Us', '/about', 'primary', 0, 2, 'color-3'),
('Services', '/services', 'primary', 0, 3, 'color-4'),
('Contact', '/contact', 'primary', 0, 4, 'color-5');

-- Product Categories
INSERT INTO product_categories (name, slug, description) VALUES
('Drilling Equipment', 'drilling-equipment', 'Professional drilling equipment and tools'),
('Safety Equipment', 'safety-equipment', 'Safety gear and protective equipment'),
('Refining Tools', 'refining-tools', 'Equipment for oil and gas refining processes'),
('Measurement Devices', 'measurement-devices', 'Precision measurement and monitoring devices');

-- Sample Products
INSERT INTO products (name, slug, description, price, sku, status, featured, image_url, category_id) VALUES
('Professional Drill Bit Set', 'professional-drill-bit-set', 'High-quality drill bits for oil and gas exploration', 299.99, 'DBS-001', 'active', TRUE, 'uploads/photolubes_8-1-1536x1023.jpg', 1),
('Safety Helmet with Light', 'safety-helmet-light', 'Professional safety helmet with built-in LED lighting', 89.99, 'SHL-001', 'active', TRUE, 'uploads/IXER3494-1024x768.jpg', 2),
('Pressure Gauge Monitor', 'pressure-gauge-monitor', 'Digital pressure monitoring system for pipelines', 449.99, 'PGM-001', 'active', TRUE, 'uploads/ORGG4289.jpg', 4),
('Industrial Filter System', 'industrial-filter-system', 'Heavy-duty filtration system for refining processes', 1299.99, 'IFS-001', 'active', TRUE, 'uploads/SSZG7600-1024x768.jpg', 3);

-- Admin User (password: admin123 - change in production!)
INSERT INTO users (username, email, password_hash, first_name, last_name, display_name, role, email_verified) VALUES
('admin', 'admin@petroleumgas.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'Administrator', 'admin', TRUE);

-- Configuration Settings
INSERT INTO configurations (config_key, config_value, config_type, description) VALUES
('site_maintenance', 'false', 'boolean', 'Enable/disable maintenance mode'),
('products_per_page', '12', 'integer', 'Number of products to display per page'),
('enable_registration', 'true', 'boolean', 'Allow new user registrations'),
('currency_symbol', '$', 'string', 'Currency symbol for prices'),
('timezone', 'America/New_York', 'string', 'Site timezone');
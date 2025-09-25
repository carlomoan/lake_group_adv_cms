-- Create Lake Group Database and Tables
-- Run this script to set up the database structure

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS lake_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE lake_db;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS content_sections;
DROP TABLE IF EXISTS site_content;
DROP TABLE IF EXISTS media_files;
DROP TABLE IF EXISTS navigation_menu;

-- Main content table to store all website content
CREATE TABLE site_content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    content_key VARCHAR(100) NOT NULL UNIQUE,
    content_data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    version INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_content_key (content_key),
    INDEX idx_updated_at (updated_at)
);

-- Table for individual content sections (more granular control)
CREATE TABLE content_sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_name VARCHAR(100) NOT NULL,
    section_type ENUM('general', 'navigation', 'hero', 'services', 'about', 'features', 'companies', 'statistics', 'contact') NOT NULL,
    content_data JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_section_name (section_name),
    INDEX idx_section_type (section_type),
    INDEX idx_updated_at (updated_at)
);

-- Media files table for uploaded images and assets
CREATE TABLE media_files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    alt_text VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_in JSON,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_filename (filename),
    INDEX idx_uploaded_at (uploaded_at)
);

-- Navigation menu structure (separate table for better management)
CREATE TABLE navigation_menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    menu_id VARCHAR(50) NOT NULL,
    parent_id INT NULL,
    title VARCHAR(100) NOT NULL,
    url VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    has_submenu BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES navigation_menu(id) ON DELETE CASCADE,
    INDEX idx_menu_id (menu_id),
    INDEX idx_parent_id (parent_id),
    INDEX idx_sort_order (sort_order)
);

-- Insert initial content structure
INSERT INTO site_content (content_key, content_data) VALUES
('main_content', '{}'),
('site_settings', JSON_OBJECT(
    'siteTitle', 'Lake Group - Eastern & Central Africa',
    'logo', 'wp-content/uploads/logo/lake-group-logo.png',
    'primaryColor', '#FFD200',
    'secondaryColor', '#484939'
));

-- Insert initial navigation menu
INSERT INTO navigation_menu (menu_id, title, url, sort_order, has_submenu) VALUES
('home', 'Home', '#home', 1, FALSE),
('about', 'About Us', '#about', 2, TRUE),
('companies', 'Our Companies', '#companies', 3, TRUE),
('services', 'Services', '#services', 4, TRUE),
('locations', 'Our Presence', '#locations', 5, FALSE),
('contact', 'Contact', '#contact', 6, FALSE);

-- Insert submenu items for About Us
INSERT INTO navigation_menu (menu_id, parent_id, title, url, sort_order) VALUES
('about-story', (SELECT id FROM navigation_menu WHERE menu_id = 'about'), 'Our Story', '#our-story', 1),
('about-leadership', (SELECT id FROM navigation_menu WHERE menu_id = 'about'), 'Leadership Team', '#leadership', 2),
('about-mission', (SELECT id FROM navigation_menu WHERE menu_id = 'about'), 'Mission & Vision', '#mission', 3);

-- Insert content sections with initial data
INSERT INTO content_sections (section_name, section_type, content_data) VALUES
('hero_slider', 'hero', JSON_OBJECT(
    'slides', JSON_ARRAY(
        JSON_OBJECT(
            'title', 'Lake Oil providing an end to end solution',
            'subtitle', 'ENERGY EXCELLENCE',
            'description', 'Leading petroleum distribution with 38 Million Liters storage capacity, 300 tankers, and 85 retail stations across East and Central Africa',
            'image', 'wp-content/uploads/revslider/home-one-slider/oil.jpg',
            'buttons', JSON_ARRAY(
                JSON_OBJECT('text', 'Our Services', 'url', '#services', 'style', 'primary'),
                JSON_OBJECT('text', 'Learn More', 'url', '#about', 'style', 'secondary')
            )
        ),
        JSON_OBJECT(
            'title', 'Committed to Quality - Lake Lubes Limited',
            'subtitle', 'PREMIUM LUBRICANTS',
            'description', 'Manufacturing and distributing premium quality lubricants and greases since 2014 using world-class raw materials',
            'image', 'wp-content/uploads/revslider/home-one-slider/glass-building.jpg',
            'buttons', JSON_ARRAY(
                JSON_OBJECT('text', 'View Products', 'url', '#products', 'style', 'primary'),
                JSON_OBJECT('text', 'Contact Us', 'url', '#contact', 'style', 'secondary')
            )
        )
    )
)),

('services_section', 'services', JSON_OBJECT(
    'sectionTitle', 'Our Core Services',
    'sectionSubtitle', 'Comprehensive solutions across energy, logistics, and manufacturing sectors',
    'items', JSON_ARRAY(
        JSON_OBJECT(
            'id', 'service1',
            'title', 'OIL & GAS DISTRIBUTION',
            'description', 'End-to-end petroleum solutions with extensive storage and distribution network across East and Central Africa',
            'image', 'wp-content/uploads/2016/01/11.jpg',
            'features', JSON_ARRAY('38 Million Liters Storage Capacity', '300 Tanker Fleet', '85 Retail Stations', 'Multi-country Operations')
        ),
        JSON_OBJECT(
            'id', 'service2',
            'title', 'LOGISTICS & TRANSPORT',
            'description', 'Comprehensive transportation, container handling, and supply chain solutions for businesses across the region',
            'image', 'wp-content/uploads/2016/01/21.jpg',
            'features', JSON_ARRAY('Container Depot Services', 'Freight Consolidation', 'Cross-border Transport', 'Agricultural Logistics')
        ),
        JSON_OBJECT(
            'id', 'service3',
            'title', 'MANUFACTURING SOLUTIONS',
            'description', 'Steel production, construction materials, and industrial manufacturing serving the growing infrastructure needs',
            'image', 'wp-content/uploads/2016/01/31.jpg',
            'features', JSON_ARRAY('Steel Fabrication', 'Ready-Mix Concrete', 'Pipe Manufacturing', 'Building Materials')
        )
    )
)),

('statistics', 'statistics', JSON_OBJECT(
    'countries', 10,
    'companies', '50+',
    'customers', '3,472+',
    'employees', '2,184+',
    'storage_capacity', '38 Million Liters',
    'tankers', 300,
    'retail_stations', 85
));

-- Create user for application (optional, for better security)
-- CREATE USER IF NOT EXISTS 'lake_app'@'localhost' IDENTIFIED BY 'lake_app_password';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON lake_db.* TO 'lake_app'@'localhost';
-- FLUSH PRIVILEGES;

-- Show created tables
SHOW TABLES;

-- Display table structures
DESCRIBE site_content;
DESCRIBE content_sections;
DESCRIBE media_files;
DESCRIBE navigation_menu;
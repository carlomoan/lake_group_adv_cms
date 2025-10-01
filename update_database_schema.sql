-- ============================================================================
-- Database Schema Updates for Petroleum & Gas CMS
-- Adds missing columns to match admin form fields
-- ============================================================================

-- Site Settings Table Updates
ALTER TABLE `site_settings`
ADD COLUMN IF NOT EXISTS `description` TEXT AFTER `tagline`,
ADD COLUMN IF NOT EXISTS `language` VARCHAR(10) DEFAULT 'en-US' AFTER `description`,
ADD COLUMN IF NOT EXISTS `timezone` VARCHAR(50) DEFAULT 'UTC' AFTER `language`,
ADD COLUMN IF NOT EXISTS `apple_touch_icon` VARCHAR(500) AFTER `favicon_url`,
ADD COLUMN IF NOT EXISTS `title_template` VARCHAR(255) DEFAULT '{{page}} - {{site}}' AFTER `apple_touch_icon`;

-- Navbar Settings - Create if doesn't exist
CREATE TABLE IF NOT EXISTS `navbar_settings` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `position` VARCHAR(20) DEFAULT 'fixed',
    `height` INT DEFAULT 70,
    `background_color` VARCHAR(7) DEFAULT '#ffffff',
    `transparency` INT DEFAULT 0,
    `text_color` VARCHAR(7) DEFAULT '#333333',
    `hover_color` VARCHAR(7) DEFAULT '#FFD200',
    `use_transparent_logo` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default navbar settings if empty
INSERT IGNORE INTO `navbar_settings` (`id`, `position`, `height`, `background_color`, `transparency`, `text_color`, `hover_color`)
VALUES (1, 'fixed', 70, '#ffffff', 0, '#333333', '#FFD200');

-- Dropdown Settings - Add missing columns
ALTER TABLE `dropdown_settings`
ADD COLUMN IF NOT EXISTS `background_image` VARCHAR(500) AFTER `background_color`,
ADD COLUMN IF NOT EXISTS `background_position` VARCHAR(50) DEFAULT 'center' AFTER `background_image`;

-- Social Media - Create if doesn't exist
CREATE TABLE IF NOT EXISTS `social_media` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `facebook` VARCHAR(500),
    `twitter` VARCHAR(500),
    `instagram` VARCHAR(500),
    `linkedin` VARCHAR(500),
    `youtube` VARCHAR(500),
    `google_plus` VARCHAR(500),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default social media if empty
INSERT IGNORE INTO `social_media` (`id`) VALUES (1);

-- Hero Settings - Create if doesn't exist
CREATE TABLE IF NOT EXISTS `hero_settings` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `autoplay` TINYINT(1) DEFAULT 1,
    `duration` INT DEFAULT 5,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default hero settings if empty
INSERT IGNORE INTO `hero_settings` (`id`, `autoplay`, `duration`) VALUES (1, 1, 5);

-- Hero Slides - Ensure all columns exist
ALTER TABLE `hero_slides`
ADD COLUMN IF NOT EXISTS `subtitle_color` VARCHAR(7) DEFAULT '#FFD200' AFTER `subtitle`,
ADD COLUMN IF NOT EXISTS `button1_text` VARCHAR(100) AFTER `description`,
ADD COLUMN IF NOT EXISTS `button1_url` VARCHAR(500) AFTER `button1_text`,
ADD COLUMN IF NOT EXISTS `button2_text` VARCHAR(100) AFTER `button1_url`,
ADD COLUMN IF NOT EXISTS `button2_url` VARCHAR(500) AFTER `button2_text`,
ADD COLUMN IF NOT EXISTS `slide_order` INT DEFAULT 0 AFTER `button2_url`,
ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) DEFAULT 1 AFTER `slide_order`;

-- Services Settings - Create if doesn't exist
CREATE TABLE IF NOT EXISTS `services_settings` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `section_title` VARCHAR(255) DEFAULT 'Our Core Services',
    `section_subtitle` VARCHAR(255) DEFAULT 'Comprehensive solutions across energy sectors',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default services settings if empty
INSERT IGNORE INTO `services_settings` (`id`) VALUES (1);

-- Services - Ensure columns
ALTER TABLE `services`
ADD COLUMN IF NOT EXISTS `image_url` VARCHAR(500) AFTER `title`,
ADD COLUMN IF NOT EXISTS `url` VARCHAR(500) AFTER `description`,
ADD COLUMN IF NOT EXISTS `sort_order` INT DEFAULT 0 AFTER `url`,
ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) DEFAULT 1 AFTER `sort_order`;

-- About Section - Ensure all columns
ALTER TABLE `about_section`
ADD COLUMN IF NOT EXISTS `background_color` VARCHAR(7) DEFAULT '#484939' AFTER `image`,
ADD COLUMN IF NOT EXISTS `text_color` VARCHAR(7) DEFAULT '#ffffff' AFTER `background_color`;

-- Features Settings - Create if doesn't exist
CREATE TABLE IF NOT EXISTS `features_settings` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) DEFAULT 'Why Choose Us',
    `subtitle` VARCHAR(255) DEFAULT 'Excellence in every aspect',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default features settings if empty
INSERT IGNORE INTO `features_settings` (`id`) VALUES (1);

-- Features - Ensure columns
ALTER TABLE `features`
ADD COLUMN IF NOT EXISTS `icon` VARCHAR(500) AFTER `title`,
ADD COLUMN IF NOT EXISTS `sort_order` INT DEFAULT 0 AFTER `description`,
ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) DEFAULT 1 AFTER `sort_order`;

-- Projects Settings - Create if doesn't exist
CREATE TABLE IF NOT EXISTS `projects_settings` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `section_title` VARCHAR(255) DEFAULT 'Our Projects',
    `section_subtitle` VARCHAR(255) DEFAULT 'Showcasing our best work',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default projects settings if empty
INSERT IGNORE INTO `projects_settings` (`id`) VALUES (1);

-- Projects - Ensure columns
ALTER TABLE `projects`
ADD COLUMN IF NOT EXISTS `image_url` VARCHAR(500) AFTER `title`,
ADD COLUMN IF NOT EXISTS `category` VARCHAR(100) AFTER `description`,
ADD COLUMN IF NOT EXISTS `url` VARCHAR(500) AFTER `category`,
ADD COLUMN IF NOT EXISTS `sort_order` INT DEFAULT 0 AFTER `url`,
ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) DEFAULT 1 AFTER `sort_order`;

-- News Settings - Create if doesn't exist
CREATE TABLE IF NOT EXISTS `news_settings` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `section_title` VARCHAR(255) DEFAULT 'Latest News',
    `section_subtitle` VARCHAR(255) DEFAULT 'Stay updated with our latest updates',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default news settings if empty
INSERT IGNORE INTO `news_settings` (`id`) VALUES (1);

-- News Articles - Ensure columns
ALTER TABLE `news_articles`
ADD COLUMN IF NOT EXISTS `image` VARCHAR(500) AFTER `title`,
ADD COLUMN IF NOT EXISTS `excerpt` TEXT AFTER `image`,
ADD COLUMN IF NOT EXISTS `author` VARCHAR(255) AFTER `date`,
ADD COLUMN IF NOT EXISTS `link` VARCHAR(500) AFTER `author`,
ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) DEFAULT 1 AFTER `link`;

-- Footer Settings - Ensure all columns
ALTER TABLE `footer_settings`
ADD COLUMN IF NOT EXISTS `layout` VARCHAR(50) DEFAULT 'three-column' AFTER `id`,
ADD COLUMN IF NOT EXISTS `background_type` VARCHAR(20) DEFAULT 'image' AFTER `layout`,
ADD COLUMN IF NOT EXISTS `background_color` VARCHAR(7) DEFAULT '#2c3e50' AFTER `background_type`,
ADD COLUMN IF NOT EXISTS `background_image` VARCHAR(500) AFTER `background_color`,
ADD COLUMN IF NOT EXISTS `background_size` VARCHAR(20) DEFAULT 'cover' AFTER `background_image`,
ADD COLUMN IF NOT EXISTS `gradient_start` VARCHAR(7) DEFAULT '#2c3e50' AFTER `background_size`,
ADD COLUMN IF NOT EXISTS `gradient_end` VARCHAR(7) DEFAULT '#34495e' AFTER `gradient_start`,
ADD COLUMN IF NOT EXISTS `text_color` VARCHAR(7) DEFAULT '#ffffff' AFTER `gradient_end`,
ADD COLUMN IF NOT EXISTS `link_color` VARCHAR(7) DEFAULT '#FFD200' AFTER `text_color`,
ADD COLUMN IF NOT EXISTS `link_hover_color` VARCHAR(7) DEFAULT '#ffffff' AFTER `link_color`,
ADD COLUMN IF NOT EXISTS `copyright_text` TEXT AFTER `link_hover_color`,
ADD COLUMN IF NOT EXISTS `show_social_media` TINYINT(1) DEFAULT 1 AFTER `copyright_text`,
ADD COLUMN IF NOT EXISTS `social_media_position` VARCHAR(20) DEFAULT 'bottom' AFTER `show_social_media`;

-- SEO Settings - Ensure columns
ALTER TABLE `seo_settings`
ADD COLUMN IF NOT EXISTS `meta_description` TEXT AFTER `id`,
ADD COLUMN IF NOT EXISTS `meta_keywords` TEXT AFTER `meta_description`,
ADD COLUMN IF NOT EXISTS `og_title` VARCHAR(255) AFTER `meta_keywords`,
ADD COLUMN IF NOT EXISTS `og_description` TEXT AFTER `og_title`,
ADD COLUMN IF NOT EXISTS `og_image` VARCHAR(500) AFTER `og_description`,
ADD COLUMN IF NOT EXISTS `twitter_card` VARCHAR(50) DEFAULT 'summary_large_image' AFTER `og_image`;

-- Components Visibility - Create if doesn't exist
CREATE TABLE IF NOT EXISTS `component_visibility` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `hero` TINYINT(1) DEFAULT 1,
    `services` TINYINT(1) DEFAULT 1,
    `about` TINYINT(1) DEFAULT 1,
    `features` TINYINT(1) DEFAULT 1,
    `projects` TINYINT(1) DEFAULT 1,
    `news` TINYINT(1) DEFAULT 1,
    `footer` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default component visibility if empty
INSERT IGNORE INTO `component_visibility` (`id`) VALUES (1);

-- ============================================================================
-- Verification Queries
-- ============================================================================

-- Run these after executing the ALTER statements to verify:
-- SHOW COLUMNS FROM site_settings;
-- SHOW COLUMNS FROM navbar_settings;
-- SHOW COLUMNS FROM dropdown_settings;
-- SHOW COLUMNS FROM social_media;
-- SHOW COLUMNS FROM hero_settings;
-- SHOW COLUMNS FROM hero_slides;
-- SHOW COLUMNS FROM services_settings;
-- SHOW COLUMNS FROM services;
-- SHOW COLUMNS FROM about_section;
-- SHOW COLUMNS FROM features_settings;
-- SHOW COLUMNS FROM features;
-- SHOW COLUMNS FROM projects_settings;
-- SHOW COLUMNS FROM projects;
-- SHOW COLUMNS FROM news_settings;
-- SHOW COLUMNS FROM news_articles;
-- SHOW COLUMNS FROM footer_settings;
-- SHOW COLUMNS FROM seo_settings;
-- SHOW COLUMNS FROM component_visibility;

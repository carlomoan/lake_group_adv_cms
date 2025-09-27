-- Additional Database Tables for Complete Admin Dashboard Support
-- This file contains all missing tables needed for the admin dashboard functionality

-- ==================================================
-- NAVBAR AND DROPDOWN SETTINGS
-- ==================================================

-- Enhanced navbar settings table
CREATE TABLE IF NOT EXISTS `navbar_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `position` enum('fixed','sticky','static','relative') DEFAULT 'fixed',
  `height` int DEFAULT 70,
  `background_color` varchar(7) DEFAULT '#ffffff',
  `transparency` int DEFAULT 0,
  `text_color` varchar(7) DEFAULT '#333333',
  `hover_color` varchar(7) DEFAULT '#FFD200',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dropdown menu settings table
CREATE TABLE IF NOT EXISTS `dropdown_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `layout_type` enum('standard','mega-menu-2col','mega-menu-3col','mega-menu-4col','full-width') DEFAULT 'standard',
  `background_type` enum('color','gradient','image','transparent') DEFAULT 'color',
  `background_color` varchar(7) DEFAULT '#ffffff',
  `background_image` varchar(500) DEFAULT NULL,
  `background_position` varchar(20) DEFAULT 'center',
  `gradient_start` varchar(7) DEFAULT '#ffffff',
  `gradient_end` varchar(7) DEFAULT '#f8f9fa',
  `text_color` varchar(7) DEFAULT '#333333',
  `hover_text_color` varchar(7) DEFAULT '#FFD200',
  `border_radius` int DEFAULT 4,
  `shadow_intensity` enum('none','light','medium','heavy') DEFAULT 'medium',
  `animation` enum('fade','slide','bounce','none') DEFAULT 'fade',
  `width` int DEFAULT 250,
  `font_size` int DEFAULT 14,
  `line_height` decimal(3,1) DEFAULT 1.5,
  `item_padding` int DEFAULT 12,
  `border_style` enum('none','solid','dashed','dotted') DEFAULT 'none',
  `enable_multi_level` tinyint(1) DEFAULT 1,
  `arrow_style` enum('chevron','arrow','plus','none') DEFAULT 'chevron',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- HERO SLIDER
-- ==================================================

CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slide_order` int DEFAULT 0,
  `image_url` varchar(500) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `subtitle_color` varchar(7) DEFAULT '#FFD200',
  `title` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `button1_text` varchar(100) DEFAULT NULL,
  `button1_url` varchar(255) DEFAULT NULL,
  `button2_text` varchar(100) DEFAULT NULL,
  `button2_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slide_order` (`slide_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hero_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `autoplay` tinyint(1) DEFAULT 1,
  `duration` int DEFAULT 5,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- SERVICES
-- ==================================================

CREATE TABLE IF NOT EXISTS `services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `services_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_title` varchar(255) DEFAULT NULL,
  `section_subtitle` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- ABOUT SECTION
-- ==================================================

CREATE TABLE IF NOT EXISTS `about_section` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `background_color` varchar(7) DEFAULT '#484939',
  `text_color` varchar(7) DEFAULT '#ffffff',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- FEATURES
-- ==================================================

CREATE TABLE IF NOT EXISTS `features` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `icon_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `features_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_title` varchar(255) DEFAULT NULL,
  `section_subtitle` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- PROJECTS
-- ==================================================

CREATE TABLE IF NOT EXISTS `projects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `client` varchar(255) DEFAULT NULL,
  `project_date` date DEFAULT NULL,
  `status` enum('completed','in-progress','planned') DEFAULT 'completed',
  `sort_order` int DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `projects_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_title` varchar(255) DEFAULT NULL,
  `section_subtitle` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- NEWS/ARTICLES
-- ==================================================

CREATE TABLE IF NOT EXISTS `news_articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `publication_date` date NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `category` enum('industry-news','company-updates','project-spotlight','technology','sustainability') DEFAULT 'industry-news',
  `author` varchar(100) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `sort_order` int DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_publication_date` (`publication_date`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `news_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_title` varchar(255) DEFAULT NULL,
  `section_subtitle` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- LAYOUT SETTINGS
-- ==================================================

CREATE TABLE IF NOT EXISTS `layout_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `container_width` varchar(20) DEFAULT '1200px',
  `mobile_breakpoint` int DEFAULT 768,
  `tablet_breakpoint` int DEFAULT 1024,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `layout_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_name` varchar(100) NOT NULL,
  `section_id` varchar(50) NOT NULL,
  `sort_order` int DEFAULT 0,
  `is_visible` tinyint(1) DEFAULT 1,
  `background_type` enum('none','color','gradient','image') DEFAULT 'none',
  `background_color` varchar(7) DEFAULT '#ffffff',
  `background_image` varchar(500) DEFAULT NULL,
  `padding` int DEFAULT 80,
  `margin` int DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`),
  KEY `idx_section_id` (`section_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- COMPONENTS SETTINGS
-- ==================================================

CREATE TABLE IF NOT EXISTS `components` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_enabled` tinyint(1) DEFAULT 1,
  `is_configurable` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `settings` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `custom_code` (
  `id` int NOT NULL AUTO_INCREMENT,
  `custom_css` longtext DEFAULT NULL,
  `custom_js` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- ENHANCED FOOTER SETTINGS
-- ==================================================

CREATE TABLE IF NOT EXISTS `footer_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `layout` enum('single','two-column','three-column','four-column') DEFAULT 'three-column',
  `background_type` enum('color','image','gradient') DEFAULT 'color',
  `background_color` varchar(7) DEFAULT '#2c3e50',
  `background_image` varchar(500) DEFAULT NULL,
  `background_size` enum('cover','contain','repeat') DEFAULT 'cover',
  `gradient_start` varchar(7) DEFAULT '#2c3e50',
  `gradient_end` varchar(7) DEFAULT '#34495e',
  `text_color` varchar(7) DEFAULT '#ffffff',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `footer_columns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `column_number` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content_type` enum('company_info','contact','newsletter','links') DEFAULT 'company_info',
  `content_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_column_number` (`column_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `footer_menu` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `sort_order` int DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- SOCIAL MEDIA SETTINGS
-- ==================================================

CREATE TABLE IF NOT EXISTS `social_media` (
  `id` int NOT NULL AUTO_INCREMENT,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `youtube` varchar(255) DEFAULT NULL,
  `googleplus` varchar(255) DEFAULT NULL,
  `show_in_header` tinyint(1) DEFAULT 1,
  `show_in_footer` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================================================
-- ENHANCED SEO SETTINGS
-- ==================================================

CREATE TABLE IF NOT EXISTS `seo_settings_extended` (
  `id` int NOT NULL AUTO_INCREMENT,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `google_analytics` varchar(100) DEFAULT NULL,
  `google_search_console` varchar(255) DEFAULT NULL,
  `facebook_pixel` varchar(100) DEFAULT NULL,
  `custom_head_code` longtext DEFAULT NULL,
  `custom_body_code` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
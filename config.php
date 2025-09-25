<?php
/**
 * Configuration file for Petroleum Gas Website
 * Replace WordPress configuration with custom settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'petroleum_gas_db');
define('DB_USER', 'root');
define('DB_PASS', '123456');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', 'http://localhost/petroleum-gas');
define('SITE_PATH', __DIR__);
define('ASSETS_URL', SITE_URL . '/assets');
define('UPLOADS_URL', SITE_URL . '/uploads');
define('UPLOADS_PATH', SITE_PATH . '/uploads');

// Security Settings
define('AUTH_KEY', 'your-unique-auth-key-here');
define('SECURE_AUTH_KEY', 'your-unique-secure-auth-key-here');
define('LOGGED_IN_KEY', 'your-unique-logged-in-key-here');
define('NONCE_KEY', 'your-unique-nonce-key-here');

// Session Configuration
define('SESSION_LIFETIME', 3600); // 1 hour
define('COOKIE_DOMAIN', '');
define('COOKIE_PATH', '/');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
define('FROM_EMAIL', 'noreply@petroleumgas.com');
define('FROM_NAME', 'Petroleum Gas Website');

// File Upload Settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_FILE_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);

// Cache Settings
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour
define('CACHE_PATH', SITE_PATH . '/cache');

// Debug Settings
define('DEBUG_MODE', false);
define('LOG_ERRORS', true);
define('LOG_PATH', SITE_PATH . '/logs');

// Pagination Settings
define('POSTS_PER_PAGE', 10);
define('PRODUCTS_PER_PAGE', 12);

// API Keys
define('GOOGLE_MAPS_API_KEY', 'your-google-maps-api-key');
define('GOOGLE_ANALYTICS_ID', 'UA-XXXXXXXX-X');
define('RECAPTCHA_SITE_KEY', 'your-recaptcha-site-key');
define('RECAPTCHA_SECRET_KEY', 'your-recaptcha-secret-key');

// Social Media Settings
define('FACEBOOK_URL', 'https://facebook.com/yourpage');
define('TWITTER_URL', 'https://twitter.com/yourhandle');
define('LINKEDIN_URL', 'https://linkedin.com/company/yourcompany');
define('INSTAGRAM_URL', 'https://instagram.com/yourhandle');

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('America/New_York');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Create necessary directories
$required_dirs = [
    UPLOADS_PATH,
    CACHE_PATH,
    LOG_PATH,
    UPLOADS_PATH . '/images',
    UPLOADS_PATH . '/documents'
];

foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Autoload function for classes
spl_autoload_register(function ($class) {
    $class_file = SITE_PATH . '/classes/' . $class . '.php';
    if (file_exists($class_file)) {
        require_once $class_file;
    }
});

// Include utility functions
require_once SITE_PATH . '/includes/functions.php';
require_once SITE_PATH . '/includes/security.php';
require_once SITE_PATH . '/includes/database.php';
?>
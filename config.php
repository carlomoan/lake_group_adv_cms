<?php
/**
 * Environment-based Database Configuration
 * Automatically detects dev vs production environment
 */

function getDatabaseConfig() {
    // Check if we're in development environment
    $isDev = (
        $_SERVER['HTTP_HOST'] === 'localhost' ||
        $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
        strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0 ||
        $_SERVER['SERVER_NAME'] === 'localhost' ||
        file_exists(__DIR__ . '/.dev-environment')
    );

    if ($isDev) {
        // Development environment
        return [
            'host' => 'localhost',
            'dbname' => 'lake_db',
            'username' => 'root',
            'password' => '123456',
            'environment' => 'development'
        ];
    } else {
        // Production environment
        return [
            'host' => 'localhost',
            'dbname' => 'cateeccx_lake_db',
            'username' => 'cateeccx_lake_admin',
            'password' => 'Lake@2025',
            'environment' => 'production'
        ];
    }
}

// Get current configuration
$dbConfig = getDatabaseConfig();

// Extract variables for backward compatibility
$host = $dbConfig['host'];
$dbname = $dbConfig['dbname'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];

// Optional: Log which environment is being used
if (function_exists('error_log')) {
    error_log("Database config loaded for: " . $dbConfig['environment'] . " environment");
}
?>
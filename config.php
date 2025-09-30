<?php
/**
 * Environment-based Database Configuration
 * Automatically detects dev vs production environment
 */

function getDatabaseConfig() {
    // Check if we're in development environment
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';

    // Multiple ways to detect development environment
    $isDev = (
        // Local development indicators
        $httpHost === 'localhost' ||
        $httpHost === '127.0.0.1' ||
        strpos($httpHost, 'localhost:') === 0 ||
        $serverName === 'localhost' ||

        // Explicit environment files
        file_exists(__DIR__ . '/.dev-environment') ||

        // Development paths (common local development setups)
        strpos($documentRoot, '/var/www/html') === 0 ||
        strpos(__DIR__, '/home/') === 0 ||

        // Default to development if no explicit production flag
        !file_exists(__DIR__ . '/.production-environment')
    );

    // Override: If production file exists, force production mode
    if (file_exists(__DIR__ . '/.production-environment')) {
        $isDev = false;
    }

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
    error_log("Database credentials: host=" . $dbConfig['host'] . ", db=" . $dbConfig['dbname'] . ", user=" . $dbConfig['username']);
}
?>
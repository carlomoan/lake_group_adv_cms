<?php
/**
 * Production Environment Diagnostic Script
 * Upload this to your production server at /admin/check_production.php
 * Access it at: http://testing.catehotel.co.tz/admin/check_production.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== PRODUCTION ENVIRONMENT DIAGNOSTIC ===\n\n";

// 1. Check PHP version
echo "1. PHP Version: " . phpversion() . "\n";

// 2. Check if config.php exists and is readable
$configPath = __DIR__ . '/../config.php';
echo "\n2. Config File Check:\n";
echo "   Path: $configPath\n";
echo "   Exists: " . (file_exists($configPath) ? 'YES' : 'NO') . "\n";
echo "   Readable: " . (is_readable($configPath) ? 'YES' : 'NO') . "\n";

// 3. Load config and check environment detection
try {
    require_once $configPath;
    echo "\n3. Configuration Loaded:\n";
    echo "   Environment: " . ($dbConfig['environment'] ?? 'UNKNOWN') . "\n";
    echo "   Host: " . ($host ?? 'NOT SET') . "\n";
    echo "   Database: " . ($dbname ?? 'NOT SET') . "\n";
    echo "   Username: " . ($username ?? 'NOT SET') . "\n";
    echo "   Password: " . (isset($password) && $password !== '' ? 'SET (' . strlen($password) . ' chars)' : 'NOT SET') . "\n";
} catch (Exception $e) {
    echo "\n3. ERROR Loading Config: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Test database connection
echo "\n4. Database Connection Test:\n";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   Status: ✅ CONNECTED\n";

    // Check if tables exist
    $tables = ['site_settings', 'hero_slides', 'services', 'projects', 'media_library'];
    echo "\n5. Database Tables Check:\n";
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "   $table: ✅ EXISTS (Rows: " . $result['count'] . ")\n";
        } catch (PDOException $e) {
            echo "   $table: ❌ MISSING or ERROR\n";
        }
    }

    // Check site_settings content
    echo "\n6. Site Settings Content:\n";
    try {
        $stmt = $pdo->query("SELECT site_title, primary_color FROM site_settings WHERE id = 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($settings) {
            echo "   Site Title: " . ($settings['site_title'] ?? 'NOT SET') . "\n";
            echo "   Primary Color: " . ($settings['primary_color'] ?? 'NOT SET') . "\n";
        } else {
            echo "   ❌ No settings found (id=1 missing)\n";
        }
    } catch (PDOException $e) {
        echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    }

} catch (PDOException $e) {
    echo "   Status: ❌ FAILED\n";
    echo "   Error: " . $e->getMessage() . "\n";
    echo "\n   SOLUTION:\n";
    echo "   - Check database credentials in config.php\n";
    echo "   - Ensure database '$dbname' exists\n";
    echo "   - Ensure user '$username' has privileges\n";
    exit(1);
}

// 7. Check file permissions
echo "\n7. File Permissions:\n";
$checkFiles = [
    __DIR__ . '/save_content.php' => 'save_content.php',
    __DIR__ . '/../generate_site.php' => 'generate_site.php',
    __DIR__ . '/../index_generated.html' => 'index_generated.html'
];

foreach ($checkFiles as $path => $name) {
    if (file_exists($path)) {
        echo "   $name: ✅ EXISTS (Permissions: " . substr(sprintf('%o', fileperms($path)), -4) . ")\n";
    } else {
        echo "   $name: ❌ MISSING\n";
    }
}

// 8. Check PHP extensions
echo "\n8. Required PHP Extensions:\n";
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    echo "   $ext: " . (extension_loaded($ext) ? '✅ LOADED' : '❌ MISSING') . "\n";
}

// 9. Check error reporting
echo "\n9. PHP Error Reporting:\n";
echo "   Display Errors: " . ini_get('display_errors') . "\n";
echo "   Error Reporting: " . error_reporting() . "\n";
echo "   Log Errors: " . ini_get('log_errors') . "\n";
echo "   Error Log: " . ini_get('error_log') . "\n";

// 10. Check environment file
echo "\n10. Environment Files:\n";
echo "   .production-environment: " . (file_exists(__DIR__ . '/../.production-environment') ? '✅ EXISTS' : '❌ MISSING') . "\n";
echo "   .dev-environment: " . (file_exists(__DIR__ . '/../.dev-environment') ? '✅ EXISTS' : '❌ MISSING') . "\n";

echo "\n=== DIAGNOSTIC COMPLETE ===\n";
echo "\nIf you see any ❌ marks above, those need to be fixed.\n";
echo "If everything shows ✅, check your server's error logs for more details.\n";
?>
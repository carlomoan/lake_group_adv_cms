<?php
// Comprehensive Production Diagnostic Script
header('Content-Type: text/html; charset=utf-8');
echo "<h1>Production Environment Diagnostic</h1>";
echo "<style>body{font-family:Arial;margin:20px} .success{color:green} .error{color:red} .warning{color:orange} .info{color:blue} pre{background:#f5f5f5;padding:10px;border-radius:5px}</style>";

echo "<h2>1. PHP Environment</h2>";
echo "<div class='info'>PHP Version: " . phpversion() . "</div>";
echo "<div class='info'>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</div>";
echo "<div class='info'>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</div>";
echo "<div class='info'>Current Directory: " . __DIR__ . "</div>";

echo "<h2>2. File System Check</h2>";
$files_to_check = [
    'save_content.php',
    'database-integration.js',
    '../setup_database.php',
    'debug_database.php'
];

foreach ($files_to_check as $file) {
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $perms = substr(sprintf('%o', fileperms($full_path)), -4);
        echo "<div class='success'>✓ $file exists (permissions: $perms)</div>";
    } else {
        echo "<div class='error'>✗ $file missing</div>";
    }
}

echo "<h2>3. Database Configuration Test</h2>";
// Load environment-based database configuration
require_once __DIR__ . '/../config.php';

echo "<div class='info'>Environment: " . $dbConfig['environment'] . "</div>";
echo "<div class='info'>Testing connection to: $username@$host/$dbname</div>";

try {
    // Test basic connection
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✓ Database server connection successful</div>";

    // Test database existence
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "<div class='success'>✓ Database '$dbname' exists</div>";

        // Connect to specific database
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<div class='success'>✓ Connected to database '$dbname'</div>";

        // Check tables
        echo "<h3>Table Check:</h3>";
        $required_tables = ['site_settings', 'navbar_settings', 'services', 'projects', 'news_articles'];
        foreach ($required_tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<div class='success'>✓ Table '$table' exists</div>";
            } else {
                echo "<div class='error'>✗ Table '$table' missing</div>";
            }
        }

    } else {
        echo "<div class='error'>✗ Database '$dbname' does not exist</div>";
    }

} catch (PDOException $e) {
    echo "<div class='error'>✗ Database connection failed: " . $e->getMessage() . "</div>";
}

echo "<h2>4. HTTP Request Test</h2>";
echo "<div class='info'>Testing POST request to save_content.php...</div>";

// Test POST request
$test_data = json_encode(['content' => ['siteSettings' => ['siteTitle' => 'Test']]]);
$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/save_content.php';

echo "<div class='info'>URL: $url</div>";

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $test_data
    ]
]);

$result = @file_get_contents($url, false, $context);
if ($result !== false) {
    echo "<div class='success'>✓ HTTP request successful</div>";
    echo "<div class='info'>Response:</div>";
    echo "<pre>" . htmlspecialchars($result) . "</pre>";
} else {
    echo "<div class='error'>✗ HTTP request failed</div>";
    $error = error_get_last();
    if ($error) {
        echo "<div class='error'>Error: " . $error['message'] . "</div>";
    }
}

echo "<h2>5. JavaScript Integration Test</h2>";
if (file_exists(__DIR__ . '/database-integration.js')) {
    $js_content = file_get_contents(__DIR__ . '/database-integration.js');
    if (strpos($js_content, 'save_content.php') !== false) {
        echo "<div class='success'>✓ JavaScript integration script references save_content.php</div>";
    } else {
        echo "<div class='warning'>! JavaScript integration might not be pointing to correct endpoint</div>";
    }
} else {
    echo "<div class='error'>✗ database-integration.js not found</div>";
}

echo "<h2>6. Quick Fix Recommendations</h2>";
echo "<div class='info'>";
echo "<strong>If database connection failed:</strong><br>";
echo "1. Check if database 'cateeccx_lake_db' exists in cPanel<br>";
echo "2. Verify user 'cateeccx_lake_admin' has proper permissions<br>";
echo "3. Confirm password is correct<br><br>";

echo "<strong>If tables are missing:</strong><br>";
echo "1. Run: php setup_database.php<br>";
echo "2. Or create tables manually using additional_tables.sql<br><br>";

echo "<strong>If HTTP request failed:</strong><br>";
echo "1. Check file permissions on save_content.php<br>";
echo "2. Verify mod_rewrite is enabled<br>";
echo "3. Check for .htaccess blocking rules<br>";
echo "</div>";

?>
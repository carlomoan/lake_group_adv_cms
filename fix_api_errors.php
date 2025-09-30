<?php
/**
 * Fix API HTTP 500 Errors
 * Checks and fixes common issues causing API failures
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix API 500 Errors</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; max-width: 1200px; margin: 0 auto; }
        .success { color: #4ec9b0; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        .warning { color: #dcdcaa; }
        .info { color: #569cd6; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        .box { background: #2d2d2d; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #569cd6; }
        button { background: #0e639c; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #1177bb; }
        h2 { color: #569cd6; }
    </style>
</head>
<body>
    <h1>🔧 Fix API HTTP 500 Errors</h1>

<?php
if (isset($_GET['action']) && $_GET['action'] === 'diagnose') {

    echo "<div class='box'>";
    echo "<h2>🔍 Diagnostic Results</h2>";

    // Step 1: Check config.php
    echo "<h3>1. Config File</h3>";
    if (!file_exists(__DIR__ . '/config.php')) {
        echo "<p class='error'>❌ config.php NOT FOUND</p>";
        echo "</div></body></html>";
        exit;
    }

    require_once __DIR__ . '/config.php';
    echo "<p class='success'>✅ config.php loaded</p>";
    echo "<pre>";
    echo "Environment: " . htmlspecialchars($dbConfig['environment'] ?? 'unknown') . "\n";
    echo "Host: " . htmlspecialchars($host) . "\n";
    echo "Database: " . htmlspecialchars($dbname) . "\n";
    echo "Username: " . htmlspecialchars($username) . "\n";
    echo ".production-environment file: " . (file_exists(__DIR__ . '/.production-environment') ? 'EXISTS' : 'NOT FOUND') . "\n";
    echo "</pre>";

    // Step 2: Test database connection
    echo "<h3>2. Database Connection</h3>";
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p class='success'>✅ Database connection successful</p>";

        // Check tables
        echo "<h3>3. Database Tables</h3>";
        $tables_info = [];

        // Check site_settings
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");
            if ($stmt->rowCount() > 0) {
                $count = $pdo->query("SELECT COUNT(*) FROM site_settings")->fetchColumn();
                echo "<p class='success'>✅ site_settings - $count rows</p>";
                $tables_info['site_settings'] = 'exists';
            } else {
                echo "<p class='error'>❌ site_settings table missing</p>";
                $tables_info['site_settings'] = 'missing';
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ site_settings: " . htmlspecialchars($e->getMessage()) . "</p>";
        }

        // Check media tables (both possible names)
        $media_table = null;
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'media_files'");
            if ($stmt->rowCount() > 0) {
                $count = $pdo->query("SELECT COUNT(*) FROM media_files")->fetchColumn();
                echo "<p class='success'>✅ media_files - $count rows</p>";
                $media_table = 'media_files';
            }
        } catch (Exception $e) {}

        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'media_library'");
            if ($stmt->rowCount() > 0) {
                $count = $pdo->query("SELECT COUNT(*) FROM media_library")->fetchColumn();
                echo "<p class='success'>✅ media_library - $count rows</p>";
                if (!$media_table) $media_table = 'media_library';
            }
        } catch (Exception $e) {}

        if (!$media_table) {
            echo "<p class='warning'>⚠️ No media table found (neither media_files nor media_library)</p>";
            $tables_info['media'] = 'missing';
        } else {
            $tables_info['media'] = $media_table;
        }

        // Step 4: Test API scripts directly
        echo "<h3>4. Testing API Scripts</h3>";

        // Test save_content.php
        echo "<h4>admin/save_content.php (GET)</h4>";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        try {
            include(__DIR__ . '/admin/save_content.php');
            $response = ob_get_clean();

            // Try to parse as JSON
            $json = json_decode($response);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<p class='success'>✅ Returns valid JSON</p>";
                if (isset($json->success) && $json->success) {
                    echo "<p class='success'>✅ Success response</p>";
                } else {
                    echo "<p class='warning'>⚠️ Response indicates failure</p>";
                    echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
                }
            } else {
                echo "<p class='error'>❌ Does NOT return valid JSON</p>";
                echo "<p>First 500 characters:</p>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
            }
        } catch (Exception $e) {
            $output = ob_get_clean();
            echo "<p class='error'>❌ Script error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }

        // Test media-library.php
        echo "<h4>api/media-library.php (GET)</h4>";
        $_SERVER['REQUEST_METHOD'] = 'GET';
        ob_start();
        try {
            include(__DIR__ . '/api/media-library.php');
            $response = ob_get_clean();

            $json = json_decode($response);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<p class='success'>✅ Returns valid JSON</p>";
                if (isset($json->success) && $json->success) {
                    echo "<p class='success'>✅ Success response - Files: " . ($json->count ?? 0) . "</p>";
                } else {
                    echo "<p class='warning'>⚠️ Response indicates failure</p>";
                    echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
                }
            } else {
                echo "<p class='error'>❌ Does NOT return valid JSON</p>";
                echo "<p>First 500 characters:</p>";
                echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
            }
        } catch (Exception $e) {
            $output = ob_get_clean();
            echo "<p class='error'>❌ Script error: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($output) . "</pre>";
        }

        echo "<h3>📋 Summary</h3>";
        echo "<ul>";
        echo "<li>Environment: <strong>" . htmlspecialchars($dbConfig['environment']) . "</strong></li>";
        echo "<li>Database: <strong>" . htmlspecialchars($dbname) . "</strong></li>";
        echo "<li>Connection: <span class='success'>Working</span></li>";
        echo "<li>site_settings: " . ($tables_info['site_settings'] === 'exists' ? "<span class='success'>OK</span>" : "<span class='error'>Missing</span>") . "</li>";
        echo "<li>Media table: " . ($tables_info['media'] !== 'missing' ? "<span class='success'>" . $tables_info['media'] . "</span>" : "<span class='warning'>Missing</span>") . "</li>";
        echo "</ul>";

    } catch (PDOException $e) {
        echo "<p class='error'>❌ Database connection FAILED</p>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";

        echo "<h3>🔧 Possible Solutions</h3>";
        echo "<ol>";
        echo "<li>Verify database credentials in config.php</li>";
        echo "<li>Ensure database '$dbname' exists</li>";
        echo "<li>Check if .production-environment file exists (for production mode)</li>";
        echo "<li>Verify database user has proper permissions</li>";
        echo "</ol>";
    }

    echo "</div>";

} else {
    // Show initial page
    ?>
    <div class="box">
        <h2>About This Tool</h2>
        <p>This tool diagnoses and helps fix HTTP 500 errors on your API endpoints:</p>
        <ul>
            <li><code>admin/save_content.php</code></li>
            <li><code>api/media-library.php</code></li>
        </ul>

        <p class="warning"><strong>Common Causes:</strong></p>
        <ul>
            <li>Database connection failures (wrong credentials)</li>
            <li>Missing .production-environment file</li>
            <li>Database tables don't exist</li>
            <li>Wrong table names (media_files vs media_library)</li>
            <li>PHP syntax errors</li>
        </ul>
    </div>

    <div class="box">
        <h2>🚀 Run Diagnostic</h2>
        <p>Click below to diagnose the issues:</p>
        <form method="get">
            <input type="hidden" name="action" value="diagnose">
            <button type="submit">🔍 RUN DIAGNOSTIC NOW</button>
        </form>
    </div>

    <div class="box">
        <h2>📋 Manual Checks</h2>
        <p>You can also manually check:</p>
        <ul>
            <li><a href="check_production.php" style="color: #4ec9b0;">check_production.php</a> - Environment detection</li>
            <li><a href="test_api_response.php" style="color: #4ec9b0;">test_api_response.php</a> - API response testing</li>
            <li><a href="diagnose_500_errors.php" style="color: #4ec9b0;">diagnose_500_errors.php</a> - Detailed diagnostics</li>
        </ul>
    </div>
    <?php
}
?>

</body>
</html>

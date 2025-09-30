<?php
/**
 * Create .production-environment file
 * This forces the system to use production database credentials
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enable Production Mode</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; max-width: 800px; margin: 0 auto; }
        .success { color: #4ec9b0; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        .warning { color: #dcdcaa; }
        .info { color: #569cd6; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; }
        .box { background: #2d2d2d; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #569cd6; }
        button { background: #0e639c; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #1177bb; }
        h2 { color: #569cd6; }
        .step { background: #252526; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 3px solid #4ec9b0; }
    </style>
</head>
<body>
    <h1>🔧 Enable Production Mode</h1>

<?php
if (isset($_GET['action']) && $_GET['action'] === 'enable') {

    echo "<div class='box'>";
    echo "<h2>Creating .production-environment file...</h2>";

    // Create the file
    $file = __DIR__ . '/.production-environment';
    $content = "# This file indicates production environment\n# Created: " . date('Y-m-d H:i:s') . "\n";

    if (file_put_contents($file, $content)) {
        echo "<p class='success'>✅ .production-environment file created successfully!</p>";

        // Verify it exists
        if (file_exists($file)) {
            echo "<p class='success'>✅ File verified to exist</p>";

            // Test the configuration
            echo "<div class='step'>";
            echo "<h3>Testing Configuration...</h3>";

            require_once __DIR__ . '/config.php';

            echo "<pre>";
            echo "Environment: " . htmlspecialchars($dbConfig['environment']) . "\n";
            echo "Host: " . htmlspecialchars($host) . "\n";
            echo "Database: " . htmlspecialchars($dbname) . "\n";
            echo "Username: " . htmlspecialchars($username) . "\n";
            echo "</pre>";

            if ($dbConfig['environment'] === 'production') {
                echo "<p class='success'>✅ Production mode ENABLED</p>";

                // Try to connect
                try {
                    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    echo "<p class='success'>✅ Database connection successful!</p>";

                    // Test a query
                    $stmt = $pdo->query("SELECT DATABASE() as db");
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo "<p class='info'>Connected to database: <strong>" . htmlspecialchars($result['db']) . "</strong></p>";

                    echo "<h3 class='success'>🎉 Production Mode Enabled Successfully!</h3>";
                    echo "<ol>";
                    echo "<li>The system is now using production database credentials</li>";
                    echo "<li>Database connection is working</li>";
                    echo "<li>Your API endpoints should now work correctly</li>";
                    echo "</ol>";

                    echo "<h3>Next Steps:</h3>";
                    echo "<ol>";
                    echo "<li><a href='fix_api_errors.php?action=diagnose' style='color: #4ec9b0;'>Re-run API Diagnostics</a> to verify APIs work</li>";
                    echo "<li><a href='/' style='color: #4ec9b0;'>Test your website</a> - clear cache first (Ctrl+F5)</li>";
                    echo "<li><a href='admin/' style='color: #4ec9b0;'>Test admin dashboard</a></li>";
                    echo "</ol>";

                } catch (PDOException $e) {
                    echo "<p class='error'>❌ Database connection failed</p>";
                    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";

                    echo "<h3>🔧 Database Issue</h3>";
                    echo "<p>Production mode is enabled but database connection failed. Possible issues:</p>";
                    echo "<ol>";
                    echo "<li>Database '<strong>$dbname</strong>' doesn't exist</li>";
                    echo "<li>User '<strong>$username</strong>' doesn't exist or has wrong password</li>";
                    echo "<li>User doesn't have permissions to access the database</li>";
                    echo "</ol>";

                    echo "<h3>Fix via MySQL/phpMyAdmin:</h3>";
                    echo "<pre>";
                    echo "CREATE DATABASE IF NOT EXISTS $dbname;\n";
                    echo "CREATE USER '$username'@'localhost' IDENTIFIED BY 'Lake@2025';\n";
                    echo "GRANT ALL PRIVILEGES ON $dbname.* TO '$username'@'localhost';\n";
                    echo "FLUSH PRIVILEGES;\n";
                    echo "</pre>";
                }
            } else {
                echo "<p class='error'>❌ Still in development mode!</p>";
                echo "<p>This shouldn't happen. The .production-environment file exists but config.php is still detecting development mode.</p>";
            }

            echo "</div>";
        }

    } else {
        echo "<p class='error'>❌ Failed to create .production-environment file</p>";
        echo "<p>This is likely a permissions issue. Try creating it manually:</p>";
        echo "<ol>";
        echo "<li>SSH into your server</li>";
        echo "<li>Navigate to your website directory</li>";
        echo "<li>Run: <code>touch .production-environment</code></li>";
        echo "<li>Or create an empty file named <code>.production-environment</code> via FTP</li>";
        echo "</ol>";
    }

    echo "</div>";

} else {
    // Show initial page
    ?>
    <div class="box">
        <h2>Current Status</h2>
        <?php
        $prodFile = __DIR__ . '/.production-environment';
        if (file_exists($prodFile)) {
            echo "<p class='success'>✅ .production-environment file EXISTS</p>";
            echo "<p>Production mode should be enabled.</p>";

            // Load config to verify
            require_once __DIR__ . '/config.php';
            echo "<pre>";
            echo "Environment: " . htmlspecialchars($dbConfig['environment']) . "\n";
            echo "Database: " . htmlspecialchars($dbname) . "\n";
            echo "</pre>";

            if ($dbConfig['environment'] === 'production') {
                echo "<p class='success'>✅ System is in PRODUCTION mode</p>";
                echo "<p><a href='fix_api_errors.php?action=diagnose' style='color: #4ec9b0;'><button>Run API Diagnostics</button></a></p>";
            } else {
                echo "<p class='error'>❌ File exists but still in DEVELOPMENT mode</p>";
                echo "<p>Try clicking the button below to force creation:</p>";
            }

        } else {
            echo "<p class='error'>❌ .production-environment file DOES NOT EXIST</p>";
            echo "<p>The system is currently in DEVELOPMENT mode.</p>";
            echo "<pre>";
            echo "This means it's trying to use:\n";
            echo "  Database: lake_db\n";
            echo "  Username: root\n";
            echo "  Password: 123456\n";
            echo "\nInstead of production credentials:\n";
            echo "  Database: cateeccx_lake_db\n";
            echo "  Username: cateeccx_lake_admin\n";
            echo "  Password: Lake@2025\n";
            echo "</pre>";
        }
        ?>
    </div>

    <div class="box">
        <h2>🚀 Enable Production Mode</h2>
        <p>Click the button below to create the .production-environment file and switch to production database credentials:</p>
        <form method="get">
            <input type="hidden" name="action" value="enable">
            <button type="submit">⚡ ENABLE PRODUCTION MODE NOW</button>
        </form>
    </div>

    <div class="box">
        <h2>ℹ️ What This Does</h2>
        <p>This will:</p>
        <ol>
            <li>Create a file named <code>.production-environment</code></li>
            <li>Force config.php to use production database credentials</li>
            <li>Test the database connection</li>
            <li>Show you the results</li>
        </ol>

        <p class="warning"><strong>Note:</strong> Make sure your production database exists and has the correct credentials before enabling production mode.</p>
    </div>
    <?php
}
?>

</body>
</html>

<?php
/**
 * Production Setup Script
 * Upload this to your production server root directory
 * Access: http://testing.catehotel.co.tz/setup_production.php
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Production Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; background: #e8f5e9; padding: 10px; border-left: 4px solid green; margin: 10px 0; }
        .error { color: red; background: #ffebee; padding: 10px; border-left: 4px solid red; margin: 10px 0; }
        .warning { color: orange; background: #fff3e0; padding: 10px; border-left: 4px solid orange; margin: 10px 0; }
        .info { color: blue; background: #e3f2fd; padding: 10px; border-left: 4px solid blue; margin: 10px 0; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer; font-size: 16px; }
        button:hover { background: #45a049; }
        input { padding: 8px; width: 300px; margin: 5px 0; }
    </style>
</head>
<body>
    <h1>🚀 Production Environment Setup</h1>
    <p>This script will configure your production server to use the correct database.</p>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "<h2>Setup Results:</h2>";

        // Step 1: Create production environment file
        try {
            $envFile = __DIR__ . '/.production-environment';
            if (file_put_contents($envFile, 'production')) {
                echo "<div class='success'>✅ Step 1: Production environment file created at: $envFile</div>";
            } else {
                echo "<div class='error'>❌ Step 1 FAILED: Could not create .production-environment file</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Step 1 ERROR: " . $e->getMessage() . "</div>";
        }

        // Step 2: Try to setup database (optional - may fail if no root access)
        if (!empty($_POST['mysql_root_password'])) {
            $rootPassword = $_POST['mysql_root_password'];
            try {
                $pdo = new PDO('mysql:host=localhost', 'root', $rootPassword);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Create database
                $pdo->exec("CREATE DATABASE IF NOT EXISTS cateeccx_lake_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                echo "<div class='success'>✅ Step 2a: Production database 'cateeccx_lake_db' created</div>";

                // Create user
                $pdo->exec("CREATE USER IF NOT EXISTS 'cateeccx_lake_admin'@'localhost' IDENTIFIED BY 'Lake@2025'");
                echo "<div class='success'>✅ Step 2b: Production user 'cateeccx_lake_admin' created</div>";

                // Grant privileges
                $pdo->exec("GRANT ALL PRIVILEGES ON cateeccx_lake_db.* TO 'cateeccx_lake_admin'@'localhost'");
                $pdo->exec("FLUSH PRIVILEGES");
                echo "<div class='success'>✅ Step 2c: Privileges granted to production user</div>";

                // Test connection with new credentials
                $testPdo = new PDO('mysql:host=localhost;dbname=cateeccx_lake_db', 'cateeccx_lake_admin', 'Lake@2025');
                echo "<div class='success'>✅ Step 2d: Production database connection verified</div>";

            } catch (PDOException $e) {
                echo "<div class='warning'>⚠️ Step 2: Database setup skipped - " . $e->getMessage() . "</div>";
                echo "<div class='info'>💡 You may need to create the database manually via cPanel or phpMyAdmin</div>";
            }
        } else {
            echo "<div class='info'>ℹ️ Step 2: Database setup skipped (no MySQL root password provided)</div>";
        }

        // Step 3: Verify configuration
        echo "<h3>Verification:</h3>";
        try {
            require_once __DIR__ . '/config.php';
            echo "<div class='info'>";
            echo "Current Environment: <strong>" . ($dbConfig['environment'] ?? 'unknown') . "</strong><br>";
            echo "Database: <strong>" . ($dbname ?? 'not set') . "</strong><br>";
            echo "Username: <strong>" . ($username ?? 'not set') . "</strong><br>";
            echo "</div>";

            // Try to connect
            if (!empty($dbname) && !empty($username)) {
                try {
                    $pdo = new PDO("mysql:host=localhost;dbname=$dbname", $username, $password);
                    echo "<div class='success'>✅ Database connection successful!</div>";
                } catch (PDOException $e) {
                    echo "<div class='error'>❌ Database connection failed: " . $e->getMessage() . "</div>";
                }
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Config error: " . $e->getMessage() . "</div>";
        }

        echo "<hr>";
        echo "<div class='success'>";
        echo "<h3>Next Steps:</h3>";
        echo "<ol>";
        echo "<li>Run the diagnostic: <a href='admin/check_production.php' target='_blank'>Check Production Environment</a></li>";
        echo "<li>If all checks pass, your admin dashboard should work!</li>";
        echo "<li>If database connection fails, you need to create the database manually via cPanel</li>";
        echo "</ol>";
        echo "</div>";

        echo "<br><a href='admin/'>Go to Admin Dashboard</a>";

    } else {
        // Show form
        ?>
        <form method="POST">
            <h3>Step 1: Create Production Environment File</h3>
            <p>This will tell the system to use production database credentials.</p>

            <h3>Step 2: Setup Production Database (Optional)</h3>
            <p>If you have MySQL root access, enter the password below to automatically create the database.</p>
            <p><strong>Note:</strong> If you don't have root access or prefer to use cPanel, leave this blank and create the database manually.</p>

            <label>MySQL Root Password (optional):</label><br>
            <input type="password" name="mysql_root_password" placeholder="Leave blank if no root access">

            <h3>Database Credentials (for manual setup):</h3>
            <div class='info'>
                If you're creating the database manually, use these credentials:<br>
                <strong>Database Name:</strong> cateeccx_lake_db<br>
                <strong>Username:</strong> cateeccx_lake_admin<br>
                <strong>Password:</strong> Lake@2025<br>
            </div>

            <br>
            <button type="submit">🚀 Setup Production Environment</button>
        </form>
        <?php
    }
    ?>
</body>
</html>
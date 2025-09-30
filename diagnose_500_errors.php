<?php
/**
 * Diagnose HTTP 500 Errors on API Endpoints
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Diagnose 500 Errors</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .success { color: #4ec9b0; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        .warning { color: #dcdcaa; }
        .info { color: #569cd6; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; border-left: 3px solid #569cd6; }
        .box { background: #2d2d2d; padding: 20px; margin: 20px 0; border-radius: 8px; }
        h2 { color: #569cd6; }
    </style>
</head>
<body>
    <h1>🔍 Diagnosing HTTP 500 Errors</h1>

    <div class="box">
        <h2>1. Check config.php</h2>
        <?php
        if (file_exists(__DIR__ . '/config.php')) {
            echo "<p class='success'>✅ config.php exists</p>";

            try {
                require_once __DIR__ . '/config.php';
                echo "<p class='success'>✅ config.php loaded successfully</p>";

                echo "<p class='info'>Database Configuration:</p>";
                echo "<pre>";
                echo "Host: " . htmlspecialchars($host ?? 'NOT SET') . "\n";
                echo "Database: " . htmlspecialchars($dbname ?? 'NOT SET') . "\n";
                echo "Username: " . htmlspecialchars($username ?? 'NOT SET') . "\n";
                echo "Environment: " . htmlspecialchars($environment ?? 'NOT SET') . "\n";
                echo "</pre>";

            } catch (Exception $e) {
                echo "<p class='error'>❌ Error loading config.php: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p class='error'>❌ config.php NOT FOUND</p>";
        }
        ?>
    </div>

    <div class="box">
        <h2>2. Test Database Connection</h2>
        <?php
        if (isset($host, $dbname, $username, $password)) {
            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo "<p class='success'>✅ Database connection successful</p>";

                // Test query
                $stmt = $pdo->query("SELECT DATABASE()");
                $current_db = $stmt->fetchColumn();
                echo "<p class='info'>Connected to database: <strong>$current_db</strong></p>";

            } catch (PDOException $e) {
                echo "<p class='error'>❌ Database connection failed</p>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            }
        } else {
            echo "<p class='error'>❌ Database credentials not available</p>";
        }
        ?>
    </div>

    <div class="box">
        <h2>3. Check Required Tables</h2>
        <?php
        if (isset($pdo)) {
            $required_tables = [
                'site_settings',
                'hero_slides',
                'services',
                'projects',
                'media_library'
            ];

            foreach ($required_tables as $table) {
                try {
                    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                    if ($stmt->rowCount() > 0) {
                        echo "<p class='success'>✅ Table '$table' exists</p>";

                        // Count rows
                        $count_stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                        $count = $count_stmt->fetchColumn();
                        echo "<p style='margin-left: 30px; color: #888;'>Rows: $count</p>";
                    } else {
                        echo "<p class='error'>❌ Table '$table' does NOT exist</p>";
                    }
                } catch (Exception $e) {
                    echo "<p class='error'>❌ Error checking table '$table': " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        }
        ?>
    </div>

    <div class="box">
        <h2>4. Test admin/save_content.php</h2>
        <?php
        if (file_exists(__DIR__ . '/admin/save_content.php')) {
            echo "<p class='success'>✅ admin/save_content.php exists</p>";
            echo "<p class='info'>File size: " . filesize(__DIR__ . '/admin/save_content.php') . " bytes</p>";

            echo "<p class='warning'>Testing GET request...</p>";
            ob_start();
            $_SERVER['REQUEST_METHOD'] = 'GET';
            try {
                include(__DIR__ . '/admin/save_content.php');
                $output = ob_get_clean();
                echo "<p class='success'>✅ Script executed without fatal errors</p>";
                echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . (strlen($output) > 500 ? '...' : '') . "</pre>";
            } catch (Exception $e) {
                $output = ob_get_clean();
                echo "<p class='error'>❌ Script error: " . htmlspecialchars($e->getMessage()) . "</p>";
                if ($output) {
                    echo "<pre>" . htmlspecialchars($output) . "</pre>";
                }
            }
        } else {
            echo "<p class='error'>❌ admin/save_content.php NOT FOUND</p>";
        }
        ?>
    </div>

    <div class="box">
        <h2>5. Test api/media-library.php</h2>
        <?php
        if (file_exists(__DIR__ . '/api/media-library.php')) {
            echo "<p class='success'>✅ api/media-library.php exists</p>";
            echo "<p class='info'>File size: " . filesize(__DIR__ . '/api/media-library.php') . " bytes</p>";

            echo "<p class='warning'>Testing GET request...</p>";
            ob_start();
            $_SERVER['REQUEST_METHOD'] = 'GET';
            try {
                include(__DIR__ . '/api/media-library.php');
                $output = ob_get_clean();
                echo "<p class='success'>✅ Script executed without fatal errors</p>";
                echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . (strlen($output) > 500 ? '...' : '') . "</pre>";
            } catch (Exception $e) {
                $output = ob_get_clean();
                echo "<p class='error'>❌ Script error: " . htmlspecialchars($e->getMessage()) . "</p>";
                if ($output) {
                    echo "<pre>" . htmlspecialchars($output) . "</pre>";
                }
            }
        } else {
            echo "<p class='error'>❌ api/media-library.php NOT FOUND</p>";
        }
        ?>
    </div>

    <div class="box">
        <h2>6. Check PHP Error Log</h2>
        <p class='info'>Common error log locations:</p>
        <ul>
            <li>/var/log/php_errors.log</li>
            <li>/var/log/apache2/error.log</li>
            <li>/var/log/nginx/error.log</li>
            <li>error_log in current directory</li>
        </ul>

        <?php
        $error_log_file = __DIR__ . '/error_log';
        if (file_exists($error_log_file)) {
            echo "<p class='warning'>Found error_log in current directory:</p>";
            $errors = file_get_contents($error_log_file);
            $recent_errors = array_slice(explode("\n", $errors), -20);
            echo "<pre>" . htmlspecialchars(implode("\n", $recent_errors)) . "</pre>";
        } else {
            echo "<p class='info'>No error_log in current directory</p>";
        }
        ?>

        <p class='info'>PHP error_log setting: <?php echo ini_get('error_log') ?: 'not set'; ?></p>
    </div>

    <div class="box">
        <h2>7. Environment Detection</h2>
        <?php
        echo "<pre>";
        echo "Production file exists: " . (file_exists(__DIR__ . '/.production-environment') ? 'YES' : 'NO') . "\n";
        echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";
        echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'NOT SET') . "\n";
        echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET') . "\n";
        echo "</pre>";
        ?>
    </div>

    <div class="box">
        <h2>8. File Permissions</h2>
        <?php
        $files_to_check = [
            'config.php',
            'admin/save_content.php',
            'api/media-library.php',
            'index_generated.html',
            'index.php'
        ];

        foreach ($files_to_check as $file) {
            $path = __DIR__ . '/' . $file;
            if (file_exists($path)) {
                $perms = fileperms($path);
                $perms_str = substr(sprintf('%o', $perms), -4);
                $readable = is_readable($path) ? '✅' : '❌';
                $writable = is_writable($path) ? '✅' : '❌';

                echo "<p>$file: $perms_str | Read: $readable | Write: $writable</p>";
            }
        }
        ?>
    </div>

</body>
</html>

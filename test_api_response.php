<?php
/**
 * Test API Response - Shows what the endpoints actually return
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>API Response Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; max-width: 1400px; margin: 0 auto; }
        .success { color: #4ec9b0; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        .warning { color: #dcdcaa; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; border-left: 3px solid #569cd6; white-space: pre-wrap; word-wrap: break-word; }
        .box { background: #2d2d2d; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #569cd6; }
        h2 { color: #569cd6; }
        button { background: #0e639c; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #1177bb; }
    </style>
</head>
<body>
    <h1>🧪 API Response Test</h1>

    <div class="box">
        <h2>Test 1: admin/save_content.php (GET)</h2>
        <?php
        ob_start();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        echo "<p class='warning'>Executing admin/save_content.php...</p>";

        try {
            // Capture the output
            ob_start();
            include(__DIR__ . '/admin/save_content.php');
            $response = ob_get_clean();

            echo "<p class='success'>✅ Script executed</p>";
            echo "<p>Response length: " . strlen($response) . " bytes</p>";

            // Try to decode as JSON
            $json = json_decode($response);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<p class='success'>✅ Valid JSON response</p>";
                echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo "<p class='error'>❌ NOT valid JSON - Error: " . json_last_error_msg() . "</p>";
                echo "<p class='warning'>Raw response:</p>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            }

        } catch (Exception $e) {
            $response = ob_get_clean();
            echo "<p class='error'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }

        ob_end_clean();
        ?>
    </div>

    <div class="box">
        <h2>Test 2: api/media-library.php (GET)</h2>
        <?php
        ob_start();

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        echo "<p class='warning'>Executing api/media-library.php...</p>";

        try {
            // Capture the output
            ob_start();
            include(__DIR__ . '/api/media-library.php');
            $response = ob_get_clean();

            echo "<p class='success'>✅ Script executed</p>";
            echo "<p>Response length: " . strlen($response) . " bytes</p>";

            // Try to decode as JSON
            $json = json_decode($response);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<p class='success'>✅ Valid JSON response</p>";
                echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo "<p class='error'>❌ NOT valid JSON - Error: " . json_last_error_msg() . "</p>";
                echo "<p class='warning'>Raw response:</p>";
                echo "<pre>" . htmlspecialchars($response) . "</pre>";
            }

        } catch (Exception $e) {
            $response = ob_get_clean();
            echo "<p class='error'>❌ Exception: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($response) . "</pre>";
        }

        ob_end_clean();
        ?>
    </div>

    <div class="box">
        <h2>Test 3: Direct Database Test</h2>
        <?php
        if (file_exists(__DIR__ . '/config.php')) {
            require_once __DIR__ . '/config.php';

            echo "<p class='warning'>Testing database connection...</p>";
            echo "<pre>";
            echo "Environment: " . htmlspecialchars($environment ?? 'unknown') . "\n";
            echo "Host: " . htmlspecialchars($host ?? 'not set') . "\n";
            echo "Database: " . htmlspecialchars($dbname ?? 'not set') . "\n";
            echo "Username: " . htmlspecialchars($username ?? 'not set') . "\n";
            echo "</pre>";

            try {
                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                echo "<p class='success'>✅ Database connected</p>";

                // Test site_settings
                try {
                    $stmt = $pdo->query("SELECT * FROM site_settings WHERE id = 1");
                    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($settings) {
                        echo "<p class='success'>✅ site_settings table OK - Title: " . htmlspecialchars($settings['site_title'] ?? 'N/A') . "</p>";
                    } else {
                        echo "<p class='warning'>⚠️ site_settings table exists but no data (id=1)</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p class='error'>❌ site_settings error: " . htmlspecialchars($e->getMessage()) . "</p>";
                }

                // Test media_library
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM media_library");
                    $count = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo "<p class='success'>✅ media_library table OK - Files: " . $count['count'] . "</p>";
                } catch (PDOException $e) {
                    echo "<p class='error'>❌ media_library error: " . htmlspecialchars($e->getMessage()) . "</p>";
                }

            } catch (PDOException $e) {
                echo "<p class='error'>❌ Database connection failed</p>";
                echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
            }
        } else {
            echo "<p class='error'>❌ config.php not found</p>";
        }
        ?>
    </div>

    <div class="box">
        <h2>Test 4: File Existence Check</h2>
        <?php
        $files = [
            'config.php',
            'admin/save_content.php',
            'api/media-library.php',
            '.production-environment'
        ];

        foreach ($files as $file) {
            $path = __DIR__ . '/' . $file;
            if (file_exists($path)) {
                $size = filesize($path);
                echo "<p class='success'>✅ $file (" . number_format($size) . " bytes)</p>";
            } else {
                echo "<p class='error'>❌ $file - NOT FOUND</p>";
            }
        }
        ?>
    </div>

    <div class="box">
        <h2>Live Test via JavaScript</h2>
        <p>Click to test the actual API endpoints:</p>
        <button onclick="testAPI('admin/save_content.php')">Test save_content.php</button>
        <button onclick="testAPI('api/media-library.php')">Test media-library.php</button>
        <div id="result" style="margin-top: 20px;"></div>
    </div>

    <script>
        async function testAPI(endpoint) {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<p style="color: #dcdcaa;">Testing ' + endpoint + '...</p>';

            try {
                const response = await fetch('/' + endpoint, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                const text = await response.text();

                resultDiv.innerHTML = '<h3 style="color: #569cd6;">Response from ' + endpoint + ':</h3>';
                resultDiv.innerHTML += '<p>Status: ' + response.status + ' ' + response.statusText + '</p>';
                resultDiv.innerHTML += '<p>Response length: ' + text.length + ' bytes</p>';

                try {
                    const json = JSON.parse(text);
                    resultDiv.innerHTML += '<p style="color: #4ec9b0;">✅ Valid JSON</p>';
                    resultDiv.innerHTML += '<pre>' + JSON.stringify(json, null, 2) + '</pre>';
                } catch (e) {
                    resultDiv.innerHTML += '<p style="color: #f48771;">❌ NOT valid JSON</p>';
                    resultDiv.innerHTML += '<p>Parse error: ' + e.message + '</p>';
                    resultDiv.innerHTML += '<p style="color: #dcdcaa;">Raw response (first 1000 chars):</p>';
                    resultDiv.innerHTML += '<pre>' + text.substring(0, 1000).replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</pre>';
                }

            } catch (error) {
                resultDiv.innerHTML = '<p style="color: #f48771;">❌ Request failed: ' + error.message + '</p>';
            }
        }
    </script>

</body>
</html>

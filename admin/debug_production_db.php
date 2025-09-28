<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h1>Production Database Diagnostic</h1>";
echo "<style>body{font-family:Arial;margin:20px} .success{color:green;background:#d4edda;padding:10px;margin:5px 0;border-radius:5px} .error{color:red;background:#f8d7da;padding:10px;margin:5px 0;border-radius:5px} .warning{color:orange;background:#fff3cd;padding:10px;margin:5px 0;border-radius:5px} .info{color:blue;background:#d1ecf1;padding:10px;margin:5px 0;border-radius:5px} pre{background:#f5f5f5;padding:10px;border-radius:5px;overflow:auto}</style>";

// Load configuration
echo "<h2>1. Environment Detection</h2>";
require_once __DIR__ . '/../config.php';

echo "<div class='info'>Environment detected: <strong>" . $dbConfig['environment'] . "</strong></div>";
echo "<div class='info'>Host: <strong>$host</strong></div>";
echo "<div class='info'>Database: <strong>$dbname</strong></div>";
echo "<div class='info'>Username: <strong>$username</strong></div>";
echo "<div class='info'>Password: <strong>" . (empty($password) ? 'NOT SET' : 'SET (' . strlen($password) . ' characters)') . "</strong></div>";

echo "<h2>2. PHP Configuration</h2>";
echo "<div class='info'>PHP Version: " . phpversion() . "</div>";
echo "<div class='info'>PDO MySQL Available: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "</div>";
echo "<div class='info'>MySQL Extension: " . (extension_loaded('mysql') || extension_loaded('mysqli') ? 'YES' : 'NO') . "</div>";

echo "<h2>3. Database Connection Test</h2>";

// Test 1: Basic MySQL connection
echo "<h3>Test 1: Basic MySQL Server Connection</h3>";
try {
    $pdo_basic = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo_basic->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✓ Successfully connected to MySQL server</div>";

    // Get MySQL version
    $version = $pdo_basic->query('SELECT VERSION()')->fetchColumn();
    echo "<div class='info'>MySQL Version: $version</div>";

} catch (PDOException $e) {
    echo "<div class='error'>✗ Failed to connect to MySQL server</div>";
    echo "<div class='error'>Error: " . $e->getMessage() . "</div>";
    echo "<div class='warning'>This means the database server is not accessible or credentials are wrong</div>";
}

// Test 2: Database existence
echo "<h3>Test 2: Database Existence</h3>";
try {
    if (isset($pdo_basic)) {
        $stmt = $pdo_basic->query("SHOW DATABASES LIKE '$dbname'");
        if ($stmt->rowCount() > 0) {
            echo "<div class='success'>✓ Database '$dbname' exists</div>";
        } else {
            echo "<div class='error'>✗ Database '$dbname' does not exist</div>";
            echo "<div class='warning'>You need to create the database first</div>";

            // Show available databases
            echo "<div class='info'>Available databases:</div>";
            $databases = $pdo_basic->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
            echo "<pre>" . implode("\n", $databases) . "</pre>";
        }
    }
} catch (PDOException $e) {
    echo "<div class='error'>✗ Error checking database existence: " . $e->getMessage() . "</div>";
}

// Test 3: Database connection with specific database
echo "<h3>Test 3: Connection to Specific Database</h3>";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✓ Successfully connected to database '$dbname'</div>";
} catch (PDOException $e) {
    echo "<div class='error'>✗ Failed to connect to database '$dbname'</div>";
    echo "<div class='error'>Error: " . $e->getMessage() . "</div>";

    // Check if it's a database not found error
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "<div class='warning'>The database needs to be created. You can:</div>";
        echo "<div class='info'>1. Create it manually in cPanel</div>";
        echo "<div class='info'>2. Or create it via SQL: CREATE DATABASE $dbname;</div>";
    }
}

// Test 4: Table structure check
if (isset($pdo)) {
    echo "<h3>Test 4: Table Structure Check</h3>";

    $required_tables = ['site_settings', 'navbar_settings', 'services', 'projects', 'news_articles'];

    foreach ($required_tables as $table) {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "<div class='success'>✓ Table '$table' exists</div>";

                // Check table structure
                $columns = $pdo->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);
                echo "<div class='info'>Columns in $table: " . count($columns) . "</div>";
            } else {
                echo "<div class='error'>✗ Table '$table' missing</div>";
            }
        } catch (PDOException $e) {
            echo "<div class='error'>✗ Error checking table '$table': " . $e->getMessage() . "</div>";
        }
    }
}

// Test 5: Permissions check
if (isset($pdo)) {
    echo "<h3>Test 5: User Permissions Check</h3>";

    try {
        // Test SELECT
        $stmt = $pdo->query("SELECT 1");
        echo "<div class='success'>✓ SELECT permission works</div>";

        // Test if site_settings exists for INSERT/UPDATE test
        $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");
        if ($stmt->rowCount() > 0) {
            // Test INSERT (if table is empty)
            try {
                $stmt = $pdo->query("SELECT COUNT(*) FROM site_settings");
                $count = $stmt->fetchColumn();

                if ($count == 0) {
                    $pdo->exec("INSERT INTO site_settings (site_title) VALUES ('test')");
                    echo "<div class='success'>✓ INSERT permission works</div>";

                    // Clean up
                    $pdo->exec("DELETE FROM site_settings WHERE site_title = 'test'");
                } else {
                    echo "<div class='info'>Skipping INSERT test (table has data)</div>";
                }

                // Test UPDATE
                $pdo->exec("UPDATE site_settings SET updated_at = NOW() WHERE id = 1");
                echo "<div class='success'>✓ UPDATE permission works</div>";

            } catch (PDOException $e) {
                echo "<div class='error'>✗ INSERT/UPDATE permission failed: " . $e->getMessage() . "</div>";
            }
        }

    } catch (PDOException $e) {
        echo "<div class='error'>✗ Basic permissions test failed: " . $e->getMessage() . "</div>";
    }
}

// Test 6: Simulate actual save operation
if (isset($pdo)) {
    echo "<h3>Test 6: Simulate Save Operation</h3>";

    try {
        // Check if site_settings table exists and has proper structure
        $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");
        if ($stmt->rowCount() > 0) {

            // Simulate the exact save operation from save_content.php
            $test_content = [
                'siteSettings' => [
                    'siteTitle' => 'Test Save ' . date('Y-m-d H:i:s'),
                    'tagline' => 'Test Tagline'
                ]
            ];

            echo "<div class='info'>Testing save operation with data:</div>";
            echo "<pre>" . json_encode($test_content, JSON_PRETTY_PRINT) . "</pre>";

            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                UPDATE site_settings SET
                    site_title = ?,
                    tagline = ?,
                    updated_at = NOW()
                WHERE id = 1
            ");

            $result = $stmt->execute([
                $test_content['siteSettings']['siteTitle'],
                $test_content['siteSettings']['tagline']
            ]);

            $rowsAffected = $stmt->rowCount();

            if ($result && $rowsAffected > 0) {
                echo "<div class='success'>✓ Save operation successful! Rows affected: $rowsAffected</div>";
                $pdo->commit();
            } else {
                echo "<div class='warning'>! Save operation completed but no rows affected. This might mean no record with id=1 exists.</div>";

                // Check if record exists
                $check = $pdo->query("SELECT * FROM site_settings WHERE id = 1")->fetch();
                if (!$check) {
                    echo "<div class='warning'>No record with id=1 found. Creating one...</div>";
                    $pdo->exec("INSERT INTO site_settings (id, site_title, tagline) VALUES (1, 'Default Title', 'Default Tagline')");
                    echo "<div class='success'>✓ Default record created</div>";
                }
                $pdo->commit();
            }

        } else {
            echo "<div class='error'>✗ site_settings table does not exist</div>";
        }

    } catch (PDOException $e) {
        if (isset($pdo)) $pdo->rollBack();
        echo "<div class='error'>✗ Save operation failed: " . $e->getMessage() . "</div>";
        echo "<div class='error'>SQL State: " . $e->getCode() . "</div>";
    }
}

echo "<h2>7. Recommendations</h2>";

if (!extension_loaded('pdo_mysql')) {
    echo "<div class='error'>Install PDO MySQL extension</div>";
}

if (!isset($pdo_basic)) {
    echo "<div class='error'>Fix database server connection - check host, username, password</div>";
}

if (isset($pdo_basic) && !isset($pdo)) {
    echo "<div class='error'>Create database '$dbname' in cPanel or via SQL</div>";
}

if (isset($pdo)) {
    $stmt = $pdo->query("SHOW TABLES");
    $table_count = $stmt->rowCount();
    if ($table_count == 0) {
        echo "<div class='warning'>Run setup_database.php to create tables</div>";
    }
}

echo "<div class='info'>If all tests pass but save still fails, check the browser network tab for the actual error response</div>";

?>
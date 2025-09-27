<?php
// Simple Save Test Script
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Log all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Load environment-based database configuration
require_once __DIR__ . '/../config.php';

$response = [
    'success' => false,
    'message' => '',
    'debug' => []
];

try {
    $response['debug'][] = "Starting connection test...";

    // Test connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $response['debug'][] = "Database connection successful";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $response['debug'][] = "Processing POST request...";

        $input = json_decode(file_get_contents('php://input'), true);
        $response['debug'][] = "Input decoded: " . (is_array($input) ? 'Yes' : 'No');

        if (!$input || !isset($input['content'])) {
            $response['debug'][] = "No content in input";
            throw new Exception('Invalid input data');
        }

        $response['debug'][] = "Content found in input";

        // Check if site_settings table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'site_settings'");
        if ($stmt->rowCount() === 0) {
            $response['debug'][] = "site_settings table does not exist - creating...";

            // Create minimal site_settings table
            $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
                id INT PRIMARY KEY AUTO_INCREMENT,
                site_title VARCHAR(255),
                tagline VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");

            // Insert default record
            $pdo->exec("INSERT IGNORE INTO site_settings (id, site_title, tagline) VALUES (1, 'Default Title', 'Default Tagline')");
            $response['debug'][] = "site_settings table created";
        } else {
            $response['debug'][] = "site_settings table exists";
        }

        // Simple test save
        $content = $input['content'];
        $siteTitle = isset($content['siteSettings']['siteTitle']) ? $content['siteSettings']['siteTitle'] : 'Test Title';
        $tagline = isset($content['siteSettings']['tagline']) ? $content['siteSettings']['tagline'] : 'Test Tagline';

        $stmt = $pdo->prepare("UPDATE site_settings SET site_title = ?, tagline = ? WHERE id = 1");
        $result = $stmt->execute([$siteTitle, $tagline]);

        $response['debug'][] = "Update executed: " . ($result ? 'Success' : 'Failed');
        $response['debug'][] = "Rows affected: " . $stmt->rowCount();

        $response['success'] = true;
        $response['message'] = 'Simple save test successful';

    } else {
        // GET request - return test data
        $stmt = $pdo->query("SELECT * FROM site_settings WHERE id = 1");
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['message'] = 'Data retrieved successfully';
        $response['data'] = $data;
    }

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
    $response['debug'][] = 'PDO Error: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'General error: ' . $e->getMessage();
    $response['debug'][] = 'General Error: ' . $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
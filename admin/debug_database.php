<?php
// Debug script to check database connection and tables
header('Content-Type: application/json');

// Database configuration (same as save_content.php)
$host = 'localhost';
$dbname = 'cateeccx_lake_db';
$username = 'cateeccx_lake_admin';
$password = 'Lake@2025';

$result = [
    'connection_test' => false,
    'database_exists' => false,
    'tables_exist' => [],
    'error' => null
];

try {
    // Test connection
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $result['connection_test'] = true;

    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        $result['database_exists'] = true;

        // Connect to the specific database
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check for required tables
        $required_tables = [
            'site_settings',
            'navbar_settings',
            'dropdown_settings',
            'hero_slides',
            'services',
            'about_section',
            'features',
            'projects',
            'news_articles',
            'layout_settings',
            'components',
            'footer_settings',
            'social_media',
            'seo_settings_extended'
        ];

        foreach ($required_tables as $table) {
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
                $result['tables_exist'][$table] = $stmt->rowCount() > 0;
            } catch (Exception $e) {
                $result['tables_exist'][$table] = false;
            }
        }
    }

} catch (PDOException $e) {
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
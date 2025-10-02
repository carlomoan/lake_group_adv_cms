<?php
require_once 'config.php';

$config = getDatabaseConfig();
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "=== Checking Logo Data in Database ===\n\n";

$stmt = $pdo->query('SELECT logo_main, logo_transparent FROM site_settings LIMIT 1');
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Logo Main:\n";
echo "  Raw value: " . var_export($row['logo_main'], true) . "\n";
echo "  Length: " . strlen($row['logo_main']) . "\n\n";

echo "Logo Transparent:\n";
echo "  Raw value: " . var_export($row['logo_transparent'], true) . "\n";
echo "  Length: " . strlen($row['logo_transparent']) . "\n\n";

// Check if it's JSON encoded
if ($row['logo_main']) {
    $decoded = json_decode($row['logo_main'], true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "⚠️ Logo Main is JSON encoded:\n";
        print_r($decoded);
    }
}

echo "\n=== Checking Full Content from save_content.php ===\n\n";

$stmt = $pdo->query('SELECT * FROM site_settings LIMIT 1');
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

echo "All site settings:\n";
print_r($settings);

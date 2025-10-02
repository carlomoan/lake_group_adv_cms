<?php
require 'config.php';
$config = getDatabaseConfig();
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
    $config['username'],
    $config['password']
);

$stmt = $pdo->query('DESCRIBE site_settings');
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "All columns in site_settings:\n";
foreach ($columns as $col) {
    echo "  - $col\n";
}

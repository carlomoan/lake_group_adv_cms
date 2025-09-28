<?php
header('Content-Type: application/json');

// Load config
require_once __DIR__ . '/../config.php';

$result = [
    'environment' => $dbConfig['environment'],
    'host_detected' => $_SERVER['HTTP_HOST'],
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'not set',
    'dev_file_exists' => file_exists(__DIR__ . '/../.dev-environment'),
    'database_config' => [
        'host' => $host,
        'dbname' => $dbname,
        'username' => $username,
        'password_set' => !empty($password)
    ],
    'php_info' => [
        'version' => phpversion(),
        'pdo_mysql' => extension_loaded('pdo_mysql'),
        'file_get_contents_allowed' => ini_get('allow_url_fopen')
    ]
];

echo json_encode($result, JSON_PRETTY_PRINT);
?>
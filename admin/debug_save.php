<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $debug = [
        'received_input' => $input,
        'siteSettings' => $input['content']['siteSettings'] ?? null,
        'logo_structure' => [
            'has_nested_logo' => isset($input['content']['siteSettings']['logo']),
            'has_flat_logoMain' => isset($input['content']['siteSettings']['logoMain']),
            'logo_value_if_nested' => $input['content']['siteSettings']['logo'] ?? null,
            'logoMain_value_if_flat' => $input['content']['siteSettings']['logoMain'] ?? null,
        ]
    ];

    echo json_encode($debug, JSON_PRETTY_PRINT);
    exit;
}

echo json_encode(['error' => 'Only POST requests accepted']);

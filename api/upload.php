<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests for uploads
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Define upload directory
$uploadDir = __DIR__ . '/../uploads/';
$uploadUrl = '/uploads/';

// Create uploads directory if it doesn't exist
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to create upload directory']);
        exit;
    }
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $error = isset($_FILES['file']) ? $_FILES['file']['error'] : 'No file uploaded';
    echo json_encode(['success' => false, 'error' => 'Upload error: ' . $error]);
    exit;
}

$file = $_FILES['file'];

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
$fileType = $file['type'];

if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, WebP, and SVG files are allowed.']);
    exit;
}

// Validate file size (10MB max)
$maxSize = 10 * 1024 * 1024; // 10MB
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'File too large. Maximum size is 10MB.']);
    exit;
}

// Generate safe filename
$originalName = $file['name'];
$extension = pathinfo($originalName, PATHINFO_EXTENSION);
$safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
$fileName = $safeName . '_' . time() . '.' . $extension;

// Full path for the uploaded file
$uploadPath = $uploadDir . $fileName;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Get file info
    $fileSize = filesize($uploadPath);
    $fileUrl = $uploadUrl . $fileName;

    // Get image dimensions if it's an image
    $dimensions = null;
    if (in_array($fileType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
        $imageInfo = getimagesize($uploadPath);
        if ($imageInfo) {
            $dimensions = [
                'width' => $imageInfo[0],
                'height' => $imageInfo[1]
            ];
        }
    }

    // Save file info to database if available
    try {
        require_once __DIR__ . '/../config.php';
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert file record into existing schema
        $stmt = $pdo->prepare("INSERT INTO media_files (original_name, filename, file_path, mime_type, file_size) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $originalName,
            $fileName,
            $uploadPath,
            $fileType,
            $fileSize
        ]);

        $fileId = $pdo->lastInsertId();

    } catch (Exception $e) {
        // Log error but don't fail the upload
        error_log("Database error in upload.php: " . $e->getMessage());
        $fileId = null;
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'File uploaded successfully',
        'file' => [
            'id' => $fileId,
            'name' => $originalName,
            'fileName' => $fileName,
            'url' => $fileUrl,
            'type' => $fileType,
            'size' => $fileSize,
            'dimensions' => $dimensions
        ]
    ]);

} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file']);
}
?>
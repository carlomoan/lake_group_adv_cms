<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Load environment-based database configuration
require_once __DIR__ . '/../config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Load media files from database
        try {
            $stmt = $pdo->query("SELECT * FROM media_files ORDER BY uploaded_at DESC");
            $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Check if files still exist and add additional info
            $mediaLibrary = [];
            foreach ($files as $file) {
                // Use file_path column for file location
                $filePath = $file['file_path'];

                // Generate URL from filename
                $fileUrl = '/uploads/' . $file['filename'];

                if (file_exists($filePath)) {
                    $mediaLibrary[] = [
                        'id' => (int)$file['id'],
                        'name' => $file['original_name'],
                        'fileName' => $file['filename'],
                        'url' => $fileUrl,
                        'type' => $file['mime_type'],
                        'size' => (int)$file['file_size'],
                        'width' => null, // Not stored in current schema
                        'height' => null, // Not stored in current schema
                        'uploadedAt' => $file['uploaded_at'],
                        'sizeFormatted' => formatBytes((int)$file['file_size'])
                    ];
                } else {
                    // File doesn't exist, remove from database
                    $deleteStmt = $pdo->prepare("DELETE FROM media_files WHERE id = ?");
                    $deleteStmt->execute([$file['id']]);
                }
            }

            echo json_encode([
                'success' => true,
                'files' => $mediaLibrary,
                'count' => count($mediaLibrary)
            ]);

        } catch (PDOException $e) {
            // If media_files table doesn't exist, return empty library
            echo json_encode([
                'success' => true,
                'files' => [],
                'count' => 0
            ]);
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Delete a media file
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File ID is required']);
            exit;
        }

        $fileId = (int)$input['id'];

        try {
            // Get file info first
            $stmt = $pdo->prepare("SELECT * FROM media_files WHERE id = ?");
            $stmt->execute([$fileId]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$file) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'File not found']);
                exit;
            }

            // Delete physical file
            $filePath = $file['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete from database
            $deleteStmt = $pdo->prepare("DELETE FROM media_files WHERE id = ?");
            $deleteStmt->execute([$fileId]);

            echo json_encode([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
}

// Helper function to format file sizes
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');

    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }

    return round($size, $precision) . ' ' . $units[$i];
}
?>
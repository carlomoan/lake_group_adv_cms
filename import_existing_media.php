<?php
/**
 * Import Existing Media Files into Database
 * This script scans the uploads/ directory and adds any files not in database
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Import Existing Media Files</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; max-width: 1200px; margin: 0 auto; }
        .success { color: #4ec9b0; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        .warning { color: #dcdcaa; }
        .info { color: #569cd6; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .box { background: #2d2d2d; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #569cd6; }
        button { background: #0e639c; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #1177bb; }
        h2 { color: #569cd6; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th, table td { padding: 8px; text-align: left; border-bottom: 1px solid #444; }
        table th { background: #252526; }
    </style>
</head>
<body>
    <h1>📥 Import Existing Media Files</h1>

<?php
if (isset($_GET['action']) && $_GET['action'] === 'import') {

    echo "<div class='box'>";
    echo "<h2>🔄 Importing Files...</h2>";

    require_once __DIR__ . '/config.php';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $uploadDir = __DIR__ . '/uploads/';

        if (!file_exists($uploadDir)) {
            echo "<p class='error'>❌ uploads/ directory not found</p>";
            echo "</div></body></html>";
            exit;
        }

        // Get all files from uploads directory
        $files = glob($uploadDir . '*');

        // Filter out non-files (directories, index.html, etc.)
        $files = array_filter($files, function($file) {
            return is_file($file) && basename($file) !== 'index.html' && basename($file) !== '.htaccess';
        });

        echo "<p class='info'>Found <strong>" . count($files) . "</strong> files in uploads/ directory</p>";

        // Get existing filenames from database
        $stmt = $pdo->query("SELECT filename FROM media_files");
        $existingFiles = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p class='info'>Already tracked in database: <strong>" . count($existingFiles) . "</strong> files</p>";

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        echo "<table>";
        echo "<tr><th>File</th><th>Size</th><th>Type</th><th>Status</th></tr>";

        foreach ($files as $filePath) {
            $filename = basename($filePath);

            // Skip if already in database
            if (in_array($filename, $existingFiles)) {
                echo "<tr>";
                echo "<td>$filename</td>";
                echo "<td>-</td>";
                echo "<td>-</td>";
                echo "<td class='warning'>⏭️ Already in database</td>";
                echo "</tr>";
                $skipped++;
                continue;
            }

            // Get file info
            $fileSize = filesize($filePath);
            $mimeType = mime_content_type($filePath);
            $originalName = $filename;

            // Try to clean up the original name if it has timestamp
            if (preg_match('/_(\d{10,13})\.(jpg|jpeg|png|gif|webp|svg)$/i', $filename, $matches)) {
                $originalName = str_replace('_' . $matches[1], '', $filename);
            }

            try {
                $stmt = $pdo->prepare("
                    INSERT INTO media_files
                    (filename, original_name, file_path, file_size, mime_type, uploaded_at, is_active)
                    VALUES (?, ?, ?, ?, ?, NOW(), 1)
                ");

                $stmt->execute([
                    $filename,
                    $originalName,
                    $filePath,
                    $fileSize,
                    $mimeType
                ]);

                $sizeFormatted = formatBytes($fileSize);

                echo "<tr>";
                echo "<td>$filename</td>";
                echo "<td>$sizeFormatted</td>";
                echo "<td>$mimeType</td>";
                echo "<td class='success'>✅ Imported</td>";
                echo "</tr>";

                $imported++;

            } catch (PDOException $e) {
                echo "<tr>";
                echo "<td>$filename</td>";
                echo "<td>-</td>";
                echo "<td>-</td>";
                echo "<td class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</td>";
                echo "</tr>";
                $errors++;
            }
        }

        echo "</table>";

        echo "<h3>📊 Summary</h3>";
        echo "<ul>";
        echo "<li class='success'>✅ Imported: <strong>$imported</strong> files</li>";
        echo "<li class='warning'>⏭️ Skipped: <strong>$skipped</strong> files (already in database)</li>";
        echo "<li class='error'>❌ Errors: <strong>$errors</strong> files</li>";
        echo "</ul>";

        if ($imported > 0) {
            echo "<h3 class='success'>🎉 Import Complete!</h3>";
            echo "<p>Refresh your admin dashboard to see all imported media files.</p>";
            echo "<p><a href='/admin/' style='color: #4ec9b0;'><button>Open Admin Dashboard</button></a></p>";
        }

    } catch (PDOException $e) {
        echo "<p class='error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    echo "</div>";

} else {
    // Show preview
    ?>
    <div class="box">
        <h2>📋 Current Status</h2>
        <?php
        require_once __DIR__ . '/config.php';

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $uploadDir = __DIR__ . '/uploads/';

            // Count files in directory
            $filesInDir = glob($uploadDir . '*');
            $filesInDir = array_filter($filesInDir, function($file) {
                return is_file($file) && basename($file) !== 'index.html';
            });
            $fileCount = count($filesInDir);

            // Count files in database
            $dbCount = $pdo->query("SELECT COUNT(*) FROM media_files")->fetchColumn();

            echo "<table>";
            echo "<tr><th>Location</th><th>Count</th></tr>";
            echo "<tr><td>Files in uploads/ directory</td><td><strong>$fileCount</strong></td></tr>";
            echo "<tr><td>Files tracked in database</td><td><strong>$dbCount</strong></td></tr>";
            echo "<tr><td>Files NOT in database</td><td><strong class='warning'>" . ($fileCount - $dbCount) . "</strong></td></tr>";
            echo "</table>";

            if ($fileCount > $dbCount) {
                echo "<p class='warning'>⚠️ You have " . ($fileCount - $dbCount) . " file(s) in uploads/ that are not tracked in the database.</p>";
                echo "<p>These files won't appear in the Media Library until they're imported.</p>";
            } else if ($fileCount == $dbCount) {
                echo "<p class='success'>✅ All files in uploads/ are tracked in the database!</p>";
            }

        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>

    <div class="box">
        <h2>🚀 Import Files</h2>
        <p>This will scan the uploads/ directory and add any files not already in the database.</p>
        <p><strong>What this does:</strong></p>
        <ul>
            <li>Scans uploads/ directory for all image/media files</li>
            <li>Checks which files are not yet in the database</li>
            <li>Adds them to the media_files table</li>
            <li>Skips files already in the database</li>
        </ul>
        <form method="get">
            <input type="hidden" name="action" value="import">
            <button type="submit">📥 IMPORT EXISTING FILES NOW</button>
        </form>
    </div>

    <div class="box">
        <h2>ℹ️ After Import</h2>
        <p>Once import is complete:</p>
        <ol>
            <li>Go to your admin dashboard</li>
            <li>Click on "Media Library" section</li>
            <li>All imported files will now be visible and selectable</li>
            <li>You can use them in your content (Hero slides, Services, etc.)</li>
        </ol>
    </div>
    <?php
}

// Helper function
function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    return round($size, $precision) . ' ' . $units[$i];
}
?>

</body>
</html>

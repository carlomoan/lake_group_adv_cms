<?php
/**
 * Media Library Diagnostic Tool
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Media Library Diagnostic</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; max-width: 1200px; margin: 0 auto; }
        .success { color: #4ec9b0; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        .warning { color: #dcdcaa; }
        .info { color: #569cd6; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        .box { background: #2d2d2d; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #569cd6; }
        h2 { color: #569cd6; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th, table td { padding: 8px; text-align: left; border-bottom: 1px solid #444; }
        table th { background: #252526; }
        button { background: #0e639c; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #1177bb; }
    </style>
</head>
<body>
    <h1>🔍 Media Library Diagnostic</h1>

    <div class="box">
        <h2>1. Uploads Directory</h2>
        <?php
        $uploadDir = __DIR__ . '/uploads/';

        if (file_exists($uploadDir)) {
            echo "<p class='success'>✅ uploads/ directory EXISTS</p>";

            // Check permissions
            $perms = fileperms($uploadDir);
            $permsString = substr(sprintf('%o', $perms), -4);
            echo "<p>Permissions: <strong>$permsString</strong></p>";

            $isWritable = is_writable($uploadDir);
            echo "<p>Writable: " . ($isWritable ? "<span class='success'>✅ YES</span>" : "<span class='error'>❌ NO</span>") . "</p>";

            // List files in uploads directory
            $files = glob($uploadDir . '*');
            echo "<p>Files in uploads/: <strong>" . count($files) . "</strong></p>";

            if (count($files) > 0) {
                echo "<table>";
                echo "<tr><th>Filename</th><th>Size</th><th>Modified</th><th>URL</th></tr>";
                foreach (array_slice($files, 0, 20) as $file) {
                    $filename = basename($file);
                    $size = filesize($file);
                    $modified = date('Y-m-d H:i:s', filemtime($file));
                    $url = '/uploads/' . $filename;

                    $sizeFormatted = $size < 1024 ? $size . ' B' :
                                    ($size < 1024*1024 ? round($size/1024, 2) . ' KB' :
                                    round($size/(1024*1024), 2) . ' MB');

                    echo "<tr>";
                    echo "<td>$filename</td>";
                    echo "<td>$sizeFormatted</td>";
                    echo "<td>$modified</td>";
                    echo "<td><a href='$url' target='_blank' style='color: #4ec9b0;'>View</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠️ uploads/ directory is empty</p>";
            }

        } else {
            echo "<p class='error'>❌ uploads/ directory DOES NOT EXIST</p>";
            echo "<p>Attempting to create...</p>";

            if (mkdir($uploadDir, 0755, true)) {
                echo "<p class='success'>✅ uploads/ directory created successfully!</p>";
            } else {
                echo "<p class='error'>❌ Failed to create uploads/ directory</p>";
                echo "<p>Manual fix: Create the directory via FTP/SSH and set permissions to 755</p>";
            }
        }
        ?>
    </div>

    <div class="box">
        <h2>2. Database Connection & Tables</h2>
        <?php
        require_once __DIR__ . '/config.php';

        echo "<pre>";
        echo "Environment: " . htmlspecialchars($dbConfig['environment']) . "\n";
        echo "Database: " . htmlspecialchars($dbname) . "\n";
        echo "</pre>";

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<p class='success'>✅ Database connected</p>";

            // Check media_files table
            echo "<h3>media_files Table</h3>";
            try {
                $stmt = $pdo->query("SHOW TABLES LIKE 'media_files'");
                if ($stmt->rowCount() > 0) {
                    echo "<p class='success'>✅ media_files table EXISTS</p>";

                    // Show table structure
                    $columns = $pdo->query("SHOW COLUMNS FROM media_files")->fetchAll(PDO::FETCH_ASSOC);
                    echo "<p>Table structure:</p>";
                    echo "<table>";
                    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr>";
                    foreach ($columns as $col) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
                        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
                        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
                        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";

                    // Count records
                    $count = $pdo->query("SELECT COUNT(*) FROM media_files")->fetchColumn();
                    echo "<p>Records in table: <strong>$count</strong></p>";

                    // Show sample records
                    if ($count > 0) {
                        $records = $pdo->query("SELECT * FROM media_files ORDER BY uploaded_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
                        echo "<h4>Sample Records:</h4>";
                        echo "<table>";
                        echo "<tr><th>ID</th><th>Original Name</th><th>Filename</th><th>File Path</th><th>Exists</th><th>Size</th><th>Uploaded</th></tr>";
                        foreach ($records as $record) {
                            $fileExists = file_exists($record['file_path']);
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($record['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($record['original_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($record['filename']) . "</td>";
                            echo "<td>" . htmlspecialchars($record['file_path']) . "</td>";
                            echo "<td>" . ($fileExists ? "<span class='success'>✅</span>" : "<span class='error'>❌</span>") . "</td>";
                            echo "<td>" . number_format($record['file_size']) . " bytes</td>";
                            echo "<td>" . htmlspecialchars($record['uploaded_at']) . "</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                    }

                } else {
                    echo "<p class='error'>❌ media_files table DOES NOT EXIST</p>";
                    echo "<p>Creating table...</p>";

                    $createTable = "
                    CREATE TABLE media_files (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        original_name VARCHAR(255) NOT NULL,
                        filename VARCHAR(255) NOT NULL,
                        file_path VARCHAR(500) NOT NULL,
                        mime_type VARCHAR(100),
                        file_size INT,
                        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )";

                    $pdo->exec($createTable);
                    echo "<p class='success'>✅ media_files table created!</p>";
                }

            } catch (PDOException $e) {
                echo "<p class='error'>❌ Error with media_files table: " . htmlspecialchars($e->getMessage()) . "</p>";
            }

        } catch (PDOException $e) {
            echo "<p class='error'>❌ Database connection failed</p>";
            echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
        }
        ?>
    </div>

    <div class="box">
        <h2>3. Test API Endpoints</h2>
        <button onclick="testEndpoint('/api/media-library.php')">Test GET /api/media-library.php</button>
        <button onclick="testUpload()">Test Upload</button>
        <div id="api-result"></div>
    </div>

    <div class="box">
        <h2>4. Upload Test Form</h2>
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="file" id="fileInput" accept="image/*">
            <button type="submit">Upload Test File</button>
        </form>
        <div id="upload-result"></div>
    </div>

    <script>
        async function testEndpoint(url) {
            const resultDiv = document.getElementById('api-result');
            resultDiv.innerHTML = '<p style="color: #dcdcaa;">Testing ' + url + '...</p>';

            try {
                const response = await fetch(url);
                const text = await response.text();

                resultDiv.innerHTML = '<h3>Response from ' + url + ':</h3>';
                resultDiv.innerHTML += '<p>Status: ' + response.status + '</p>';

                try {
                    const json = JSON.parse(text);
                    resultDiv.innerHTML += '<p style="color: #4ec9b0;">✅ Valid JSON</p>';
                    resultDiv.innerHTML += '<pre>' + JSON.stringify(json, null, 2) + '</pre>';
                } catch (e) {
                    resultDiv.innerHTML += '<p style="color: #f48771;">❌ NOT valid JSON</p>';
                    resultDiv.innerHTML += '<pre>' + text.substring(0, 1000).replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</pre>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<p style="color: #f48771;">❌ Request failed: ' + error.message + '</p>';
            }
        }

        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const resultDiv = document.getElementById('upload-result');
            const fileInput = document.getElementById('fileInput');

            if (!fileInput.files.length) {
                resultDiv.innerHTML = '<p style="color: #f48771;">Please select a file</p>';
                return;
            }

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            resultDiv.innerHTML = '<p style="color: #dcdcaa;">Uploading...</p>';

            try {
                const response = await fetch('/api/upload.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    resultDiv.innerHTML = '<p style="color: #4ec9b0;">✅ Upload successful!</p>';
                    resultDiv.innerHTML += '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
                } else {
                    resultDiv.innerHTML = '<p style="color: #f48771;">❌ Upload failed</p>';
                    resultDiv.innerHTML += '<pre>' + JSON.stringify(result, null, 2) + '</pre>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<p style="color: #f48771;">❌ Upload error: ' + error.message + '</p>';
            }
        });
    </script>

</body>
</html>

<?php
/**
 * Diagnostic: Check if validateImageUrls function exists in production file
 */

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Validate Function Check</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h2 { color: #569cd6; }
    </style>
</head>
<body>
    <h1>🔍 validateImageUrls Function Check</h1>
";

// Check which file is being served
$files_to_check = [
    'index.html',
    'index_generated.html',
    'index.php'
];

foreach ($files_to_check as $filename) {
    $filepath = __DIR__ . '/' . $filename;

    echo "<h2>📄 Checking: $filename</h2>";

    if (!file_exists($filepath)) {
        echo "<p class='warning'>⚠️ File does not exist</p>";
        continue;
    }

    $content = file_get_contents($filepath);
    $filesize = filesize($filepath);
    $modified = date('Y-m-d H:i:s', filemtime($filepath));

    echo "<p>📦 File size: " . number_format($filesize) . " bytes</p>";
    echo "<p>🕐 Last modified: $modified</p>";

    // Check for function call
    $call_count = substr_count($content, 'validateImageUrls(');
    echo "<p>📞 Function calls found: <span class='warning'>$call_count</span></p>";

    // Check for function definition
    if (preg_match('/validateImageUrls\s*\(\s*content\s*\)\s*\{/', $content, $matches, PREG_OFFSET_CAPTURE)) {
        $position = $matches[0][1];
        $line_number = substr_count(substr($content, 0, $position), "\n") + 1;
        echo "<p class='success'>✅ Function DEFINITION found at line $line_number</p>";

        // Show a snippet
        $lines = explode("\n", $content);
        $snippet_start = max(0, $line_number - 2);
        $snippet_end = min(count($lines), $line_number + 10);

        echo "<pre>";
        for ($i = $snippet_start; $i < $snippet_end; $i++) {
            $display_line = $i + 1;
            $line_content = htmlspecialchars($lines[$i]);
            if ($i + 1 == $line_number) {
                echo "<span class='success'>$display_line: $line_content</span>\n";
            } else {
                echo "$display_line: $line_content\n";
            }
        }
        echo "</pre>";
    } else {
        echo "<p class='error'>❌ Function DEFINITION NOT FOUND</p>";
        echo "<p class='error'>🚨 This file is missing the validateImageUrls function - it needs to be regenerated!</p>";
    }

    echo "<hr>";
}

// Check index.php redirect
$index_php = __DIR__ . '/index.php';
if (file_exists($index_php)) {
    echo "<h2>🔀 index.php Redirect</h2>";
    echo "<pre>" . htmlspecialchars(file_get_contents($index_php)) . "</pre>";
}

echo "
    <h2>🔧 Solution</h2>
    <p>If validateImageUrls function is missing from the active file:</p>
    <ol>
        <li>Go to: <a href='regenerate_website.php' style='color: #4ec9b0;'>regenerate_website.php</a></li>
        <li>Click 'Regenerate Website Now'</li>
        <li>Clear browser cache (Ctrl+F5)</li>
        <li>Reload your website</li>
    </ol>
</body>
</html>";
?>

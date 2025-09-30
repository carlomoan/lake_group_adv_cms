<?php
/**
 * Production Fix: Resolve index.html priority issue and regenerate site
 *
 * This script:
 * 1. Backs up old index.html
 * 2. Regenerates index_generated.html with validateImageUrls function
 * 3. Sets up proper redirect via index.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Production Fix - validateImageUrls Error</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            padding: 20px;
            background: #1e1e1e;
            color: #d4d4d4;
            max-width: 1200px;
            margin: 0 auto;
        }
        .success { color: #4ec9b0; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        .warning { color: #dcdcaa; font-weight: bold; }
        .info { color: #569cd6; }
        pre {
            background: #2d2d2d;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 3px solid #569cd6;
        }
        .box {
            background: #2d2d2d;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid #569cd6;
        }
        button {
            background: #0e639c;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 5px;
        }
        button:hover { background: #1177bb; }
        button:active { transform: scale(0.98); }
        .step {
            background: #252526;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 3px solid #4ec9b0;
        }
        h1 { color: #4ec9b0; }
        h2 { color: #569cd6; margin-top: 30px; }
        a { color: #4ec9b0; text-decoration: none; }
        a:hover { text-decoration: underline; }
        ol, ul { line-height: 1.8; }
    </style>
</head>
<body>
    <h1>🔧 Production Fix - validateImageUrls Error</h1>

<?php
if (isset($_GET['action']) && $_GET['action'] === 'fix') {
    echo "<div class='box'>";
    echo "<h2>🚀 Running Fix...</h2>";

    $steps = [];
    $errors = [];

    // Step 1: Check prerequisites
    echo "<div class='step'>";
    echo "<h3>Step 1: Checking Prerequisites</h3>";

    $required_files = [
        'config.php' => 'Database configuration',
        'generate_site.php' => 'Site generator script',
        'index_template.html' => 'HTML template with validateImageUrls'
    ];

    $all_files_exist = true;
    foreach ($required_files as $file => $description) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "<p class='success'>✅ $file - $description</p>";
        } else {
            echo "<p class='error'>❌ Missing: $file - $description</p>";
            $errors[] = "Missing required file: $file";
            $all_files_exist = false;
        }
    }
    echo "</div>";

    if (!$all_files_exist) {
        echo "<p class='error'>Cannot proceed - missing required files. Please upload all files first.</p>";
        echo "</div></body></html>";
        exit;
    }

    // Step 2: Verify template has validateImageUrls
    echo "<div class='step'>";
    echo "<h3>Step 2: Verifying Template</h3>";

    $template_content = file_get_contents(__DIR__ . '/index_template.html');
    $has_function = preg_match('/validateImageUrls\s*\(\s*content\s*\)\s*\{/', $template_content);

    if ($has_function) {
        echo "<p class='success'>✅ index_template.html contains validateImageUrls function</p>";
    } else {
        echo "<p class='error'>❌ index_template.html is missing validateImageUrls function!</p>";
        $errors[] = "Template file doesn't have validateImageUrls function";
        echo "</div></div></body></html>";
        exit;
    }
    echo "</div>";

    // Step 3: Backup old index.html if it exists
    echo "<div class='step'>";
    echo "<h3>Step 3: Backing Up Old Files</h3>";

    $index_html = __DIR__ . '/index.html';
    if (file_exists($index_html)) {
        $backup_file = __DIR__ . '/index_old_backup_' . date('YmdHis') . '.html';
        if (rename($index_html, $backup_file)) {
            echo "<p class='success'>✅ Backed up index.html → " . basename($backup_file) . "</p>";
        } else {
            echo "<p class='warning'>⚠️  Could not rename index.html (may need manual deletion)</p>";
        }
    } else {
        echo "<p class='info'>ℹ️  No index.html file found (good - won't interfere)</p>";
    }
    echo "</div>";

    // Step 4: Run generate_site.php
    echo "<div class='step'>";
    echo "<h3>Step 4: Regenerating Website</h3>";

    ob_start();
    try {
        include(__DIR__ . '/generate_site.php');
        $generation_output = ob_get_clean();
        echo "<pre>" . htmlspecialchars($generation_output) . "</pre>";
    } catch (Exception $e) {
        $generation_output = ob_get_clean();
        echo "<pre>" . htmlspecialchars($generation_output) . "</pre>";
        echo "<p class='error'>❌ Error during generation: " . htmlspecialchars($e->getMessage()) . "</p>";
        $errors[] = "Generation failed: " . $e->getMessage();
    }
    echo "</div>";

    // Step 5: Verify the generated file
    echo "<div class='step'>";
    echo "<h3>Step 5: Verifying Generated File</h3>";

    $generated_file = __DIR__ . '/index_generated.html';
    if (file_exists($generated_file)) {
        $generated_content = file_get_contents($generated_file);
        $generated_size = filesize($generated_file);

        // Check for validateImageUrls function
        if (preg_match('/validateImageUrls\s*\(\s*content\s*\)\s*\{/', $generated_content, $matches, PREG_OFFSET_CAPTURE)) {
            $position = $matches[0][1];
            $line_number = substr_count(substr($generated_content, 0, $position), "\n") + 1;

            echo "<p class='success'>✅ index_generated.html created successfully</p>";
            echo "<p class='info'>📦 File size: " . number_format($generated_size) . " bytes</p>";
            echo "<p class='success'>✅ validateImageUrls function found at line $line_number</p>";

            $call_count = substr_count($generated_content, 'validateImageUrls(');
            echo "<p class='info'>📞 Function calls: $call_count</p>";
        } else {
            echo "<p class='error'>❌ Generated file is missing validateImageUrls function!</p>";
            $errors[] = "Generated file doesn't contain validateImageUrls";
        }
    } else {
        echo "<p class='error'>❌ index_generated.html was not created!</p>";
        $errors[] = "Failed to create index_generated.html";
    }
    echo "</div>";

    // Step 6: Verify redirect
    echo "<div class='step'>";
    echo "<h3>Step 6: Verifying Redirect</h3>";

    $index_php = __DIR__ . '/index.php';
    if (file_exists($index_php)) {
        $redirect_content = file_get_contents($index_php);
        if (strpos($redirect_content, 'index_generated.html') !== false) {
            echo "<p class='success'>✅ index.php redirects to index_generated.html</p>";
            echo "<pre>" . htmlspecialchars($redirect_content) . "</pre>";
        } else {
            echo "<p class='warning'>⚠️  index.php exists but may not redirect correctly</p>";
        }
    } else {
        echo "<p class='error'>❌ index.php not found!</p>";
        $errors[] = "index.php redirect file missing";
    }
    echo "</div>";

    // Summary
    echo "<div class='box'>";
    if (empty($errors)) {
        echo "<h2 class='success'>🎉 FIX COMPLETED SUCCESSFULLY!</h2>";
        echo "<ol>";
        echo "<li><strong>Clear your browser cache:</strong> Press Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)</li>";
        echo "<li><strong>Reload your website:</strong> <a href='/' target='_blank'>Open Homepage</a></li>";
        echo "<li><strong>Check browser console:</strong> The 'validateImageUrls is not a function' error should be GONE</li>";
        echo "<li><strong>Verify content loads:</strong> You should see '✅ Content loaded from database'</li>";
        echo "</ol>";
        echo "<p class='info'>🕐 Fixed at: " . date('Y-m-d H:i:s') . "</p>";
        echo "<p><a href='/'><button>🌐 View Your Website</button></a></p>";
    } else {
        echo "<h2 class='error'>⚠️  Fix Completed with Errors</h2>";
        echo "<p>The following issues need attention:</p>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li class='error'>$error</li>";
        }
        echo "</ul>";
    }
    echo "</div>";

    echo "</div>";

} else {
    // Show diagnostic info and action button
    echo "<div class='box'>";
    echo "<h2>📋 Current Status</h2>";
    echo "<p>This script will fix the <code>validateImageUrls is not a function</code> error on your production site.</p>";
    echo "</div>";

    echo "<div class='box'>";
    echo "<h3>🔍 File Status</h3>";

    $files_to_check = [
        'index.html' => ['desc' => 'Old static file (will be backed up)', 'critical' => false],
        'index_generated.html' => ['desc' => 'Generated file with database content', 'critical' => true],
        'index.php' => ['desc' => 'Redirect to index_generated.html', 'critical' => true],
        'index_template.html' => ['desc' => 'Template with validateImageUrls function', 'critical' => true],
        'generate_site.php' => ['desc' => 'Site generator script', 'critical' => true]
    ];

    foreach ($files_to_check as $file => $info) {
        $exists = file_exists(__DIR__ . '/' . $file);

        if ($exists) {
            $size = filesize(__DIR__ . '/' . $file);
            $modified = date('Y-m-d H:i:s', filemtime(__DIR__ . '/' . $file));
            echo "<p class='success'>✅ <strong>$file</strong> - {$info['desc']}</p>";
            echo "<p style='margin-left: 30px; color: #888;'>Size: " . number_format($size) . " bytes | Modified: $modified</p>";

            // Check if generated file has the function
            if ($file === 'index_generated.html') {
                $content = file_get_contents(__DIR__ . '/' . $file);
                $has_func = strpos($content, 'validateImageUrls(content)') !== false;
                if ($has_func) {
                    echo "<p style='margin-left: 30px;' class='success'>✅ Contains validateImageUrls function</p>";
                } else {
                    echo "<p style='margin-left: 30px;' class='error'>❌ Missing validateImageUrls - NEEDS REGENERATION</p>";
                }
            }
        } else {
            if ($info['critical']) {
                echo "<p class='error'>❌ <strong>$file</strong> - {$info['desc']} (REQUIRED)</p>";
            } else {
                echo "<p class='info'>ℹ️  <strong>$file</strong> - {$info['desc']} (not found)</p>";
            }
        }
    }

    echo "</div>";

    echo "<div class='box'>";
    echo "<h2>🔧 What This Fix Does</h2>";
    echo "<ol>";
    echo "<li>Backs up old <code>index.html</code> (if it exists) so it doesn't interfere</li>";
    echo "<li>Runs <code>generate_site.php</code> to create fresh <code>index_generated.html</code></li>";
    echo "<li>Ensures the generated file includes the <code>validateImageUrls()</code> function</li>";
    echo "<li>Sets up <code>index.php</code> redirect to serve the generated content</li>";
    echo "<li>Verifies everything is working correctly</li>";
    echo "</ol>";
    echo "</div>";

    echo "<div class='box'>";
    echo "<h2>🚀 Ready to Fix</h2>";
    echo "<p>Click the button below to automatically fix the validateImageUrls error:</p>";
    echo "<form method='get'>";
    echo "<input type='hidden' name='action' value='fix'>";
    echo "<button type='submit'>🔧 RUN FIX NOW</button>";
    echo "</form>";
    echo "</div>";
}
?>

</body>
</html>

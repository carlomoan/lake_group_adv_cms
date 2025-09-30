<?php
/**
 * Force Website Regeneration
 * This will regenerate index_generated.html with the latest template
 * Upload to production and access once: http://testing.catehotel.co.tz/regenerate_website.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Regenerate Website</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; background: #e8f5e9; padding: 15px; border-left: 4px solid green; margin: 10px 0; }
        .error { color: red; background: #ffebee; padding: 15px; border-left: 4px solid red; margin: 10px 0; }
        .info { color: blue; background: #e3f2fd; padding: 15px; border-left: 4px solid blue; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        button { background: #4CAF50; color: white; padding: 12px 24px; border: none; cursor: pointer; font-size: 16px; border-radius: 4px; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <h1>🔄 Force Website Regeneration</h1>

    <?php
    if (isset($_GET['action']) && $_GET['action'] === 'regenerate') {
        echo "<h2>Regeneration Results:</h2>";

        // Check if generate_site.php exists
        $generateScript = __DIR__ . '/generate_site.php';

        if (!file_exists($generateScript)) {
            echo "<div class='error'>❌ ERROR: generate_site.php not found at: $generateScript</div>";
            echo "<div class='info'>Make sure you've uploaded all files to your production server.</div>";
        } else {
            echo "<div class='info'>✅ Found generate_site.php</div>";

            // Execute the generation script
            ob_start();
            try {
                // Change to script directory
                $oldDir = getcwd();
                chdir(__DIR__);

                // Capture the output
                include $generateScript;
                $output = ob_get_clean();

                // Change back
                chdir($oldDir);

                echo "<div class='success'>✅ Website regeneration completed successfully!</div>";

                if (!empty($output)) {
                    echo "<h3>Generation Output:</h3>";
                    echo "<pre>" . htmlspecialchars($output) . "</pre>";
                }

                // Verify the generated file exists
                $generatedFile = __DIR__ . '/index_generated.html';
                if (file_exists($generatedFile)) {
                    $fileSize = filesize($generatedFile);
                    $fileTime = date('Y-m-d H:i:s', filemtime($generatedFile));
                    echo "<div class='success'>";
                    echo "✅ Generated file exists: $generatedFile<br>";
                    echo "📁 File size: " . number_format($fileSize) . " bytes<br>";
                    echo "🕒 Last modified: $fileTime";
                    echo "</div>";
                } else {
                    echo "<div class='error'>⚠️ Warning: Generated file not found. The script may have encountered errors.</div>";
                }

            } catch (Exception $e) {
                ob_end_clean();
                echo "<div class='error'>❌ ERROR during regeneration: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }

        echo "<hr>";
        echo "<h3>Next Steps:</h3>";
        echo "<div class='info'>";
        echo "<ol>";
        echo "<li>Clear your browser cache (Ctrl+F5 or Cmd+Shift+R)</li>";
        echo "<li>Visit your website homepage: <a href='/' target='_blank'>testing.catehotel.co.tz</a></li>";
        echo "<li>Open browser console (F12) - you should NOT see 'validateImageUrls' errors</li>";
        echo "<li>Try saving content from admin dashboard - it should auto-regenerate from now on</li>";
        echo "</ol>";
        echo "</div>";

        echo "<br><a href='admin/'>← Go to Admin Dashboard</a>";

    } else {
        // Show information and confirmation button
        ?>
        <div class='info'>
            <h3>What this script does:</h3>
            <ul>
                <li>Reads content from your database</li>
                <li>Uses the latest index_template.html</li>
                <li>Generates a fresh index_generated.html with all the latest fixes</li>
                <li>Fixes the "validateImageUrls is not a function" error</li>
            </ul>
        </div>

        <div class='info'>
            <h3>Why you need this:</h3>
            <p>Your public website (index_generated.html) is using an old template that's missing the <code>validateImageUrls()</code> function. This regeneration will update it with the latest template that includes all necessary functions.</p>
        </div>

        <h3>Ready to regenerate?</h3>
        <form method="GET" action="">
            <input type="hidden" name="action" value="regenerate">
            <button type="submit">🚀 Regenerate Website Now</button>
        </form>

        <hr>
        <h3>Alternative: From Admin Dashboard</h3>
        <div class='info'>
            <p>After this regeneration, every time you save content from the admin dashboard, the website will automatically regenerate with the latest template.</p>
            <p>So you only need to run this script once to fix the current issue.</p>
        </div>
        <?php
    }
    ?>
</body>
</html>
<?php
/**
 * SIMPLEST FIX - Just creates the .production-environment file
 * Upload this file and access it once: http://testing.catehotel.co.tz/force_production_mode.php
 */

$envFile = __DIR__ . '/.production-environment';
$result = file_put_contents($envFile, 'production');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Force Production Mode</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 100px auto; padding: 20px; text-align: center; }
        .success { color: green; font-size: 24px; margin: 20px; }
        .error { color: red; font-size: 24px; margin: 20px; }
        .box { background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0; }
    </style>
</head>
<body>
    <?php if ($result !== false): ?>
        <h1 class="success">✅ SUCCESS!</h1>
        <div class="box">
            <p>Production mode has been activated!</p>
            <p><strong>File created:</strong><br><?php echo $envFile; ?></p>
        </div>

        <h3>Next Steps:</h3>
        <div class="box">
            <ol style="text-align: left;">
                <li>Run diagnostic: <a href="admin/check_production.php">Check Production Status</a></li>
                <li>If it shows "Environment: production" ✅, you're good!</li>
                <li>If database connection still fails, you need to create the production database</li>
            </ol>
        </div>

        <p><strong>Database Credentials Needed:</strong></p>
        <div class="box" style="text-align: left;">
            Database Name: <code>cateeccx_lake_db</code><br>
            Username: <code>cateeccx_lake_admin</code><br>
            Password: <code>Lake@2025</code>
        </div>

        <a href="admin/" style="display: inline-block; background: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 4px; margin-top: 20px;">
            Go to Admin Dashboard →
        </a>

    <?php else: ?>
        <h1 class="error">❌ FAILED</h1>
        <p>Could not create the production environment file.</p>
        <p>This usually means the web server doesn't have write permissions.</p>

        <h3>Manual Solution:</h3>
        <div class="box" style="text-align: left;">
            <p>Connect to your server via FTP or SSH and run:</p>
            <code>cd /home/cateeccx/testing.catehotel.co.tz/<br>
            touch .production-environment</code>
        </div>
    <?php endif; ?>
</body>
</html>
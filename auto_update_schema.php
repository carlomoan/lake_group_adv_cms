<?php
/**
 * Automatic Database Schema Updater
 * Adds missing columns and tables to match admin form fields
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300); // 5 minutes

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Auto Update Database Schema</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; max-width: 1400px; margin: 0 auto; }
        .success { color: #4ec9b0; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        .warning { color: #dcdcaa; }
        .info { color: #569cd6; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        .box { background: #2d2d2d; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #569cd6; }
        button { background: #0e639c; color: white; padding: 15px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; font-weight: bold; }
        button:hover { background: #1177bb; }
        h2 { color: #569cd6; }
        .step { background: #252526; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 3px solid #4ec9b0; }
    </style>
</head>
<body>
    <h1>🔄 Auto Update Database Schema</h1>

<?php
if (isset($_GET['action']) && $_GET['action'] === 'update') {

    echo "<div class='box'>";
    echo "<h2>⚙️ Updating Database Schema...</h2>";

    require_once __DIR__ . '/config.php';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        echo "<p class='success'>✅ Connected to database: $dbname</p>";

        $updates = 0;
        $errors = 0;

        // Helper function to check if column exists
        function columnExists($pdo, $table, $column) {
            try {
                $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
                $stmt->execute([$column]);
                return $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                return false;
            }
        }

        // Helper function to check if table exists
        function tableExists($pdo, $table) {
            try {
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
                return $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                return false;
            }
        }

        echo "<div class='step'>";
        echo "<h3>Step 1: Site Settings</h3>";

        $queries = [
            "ALTER TABLE `site_settings` ADD COLUMN `description` TEXT AFTER `tagline`",
            "ALTER TABLE `site_settings` ADD COLUMN `language` VARCHAR(10) DEFAULT 'en-US' AFTER `description`",
            "ALTER TABLE `site_settings` ADD COLUMN `timezone` VARCHAR(50) DEFAULT 'UTC' AFTER `language`",
            "ALTER TABLE `site_settings` ADD COLUMN `apple_touch_icon` VARCHAR(500) AFTER `favicon_url`",
            "ALTER TABLE `site_settings` ADD COLUMN `title_template` VARCHAR(255) DEFAULT '{{page}} - {{site}}' AFTER `apple_touch_icon`"
        ];

        foreach ($queries as $query) {
            try {
                $pdo->exec($query);
                $updates++;
                preg_match('/ADD COLUMN `(\w+)`/', $query, $matches);
                echo "<p class='success'>✅ Added column: " . ($matches[1] ?? 'column') . "</p>";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column') === false) {
                    echo "<p class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
                    $errors++;
                } else {
                    echo "<p class='info'>ℹ️ Column already exists: " . ($matches[1] ?? 'column') . "</p>";
                }
            }
        }
        echo "</div>";

        // Create navbar_settings table
        echo "<div class='step'>";
        echo "<h3>Step 2: Navbar Settings</h3>";

        if (!tableExists($pdo, 'navbar_settings')) {
            $query = "CREATE TABLE `navbar_settings` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `position` VARCHAR(20) DEFAULT 'fixed',
                `height` INT DEFAULT 70,
                `background_color` VARCHAR(7) DEFAULT '#ffffff',
                `transparency` INT DEFAULT 0,
                `text_color` VARCHAR(7) DEFAULT '#333333',
                `hover_color` VARCHAR(7) DEFAULT '#FFD200',
                `use_transparent_logo` TINYINT(1) DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            try {
                $pdo->exec($query);
                $pdo->exec("INSERT INTO `navbar_settings` (`id`) VALUES (1)");
                echo "<p class='success'>✅ Created navbar_settings table</p>";
                $updates++;
            } catch (PDOException $e) {
                echo "<p class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
                $errors++;
            }
        } else {
            echo "<p class='info'>ℹ️ navbar_settings table already exists</p>";

            // Add use_transparent_logo column if missing
            if (!columnExists($pdo, 'navbar_settings', 'use_transparent_logo')) {
                try {
                    $pdo->exec("ALTER TABLE `navbar_settings` ADD COLUMN `use_transparent_logo` TINYINT(1) DEFAULT 0");
                    echo "<p class='success'>✅ Added use_transparent_logo column</p>";
                    $updates++;
                } catch (PDOException $e) {
                    echo "<p class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        }
        echo "</div>";

        // Dropdown settings updates
        echo "<div class='step'>";
        echo "<h3>Step 3: Dropdown Settings</h3>";

        if (tableExists($pdo, 'dropdown_settings')) {
            $dropdownCols = [
                "ALTER TABLE `dropdown_settings` ADD COLUMN `background_image` VARCHAR(500) AFTER `background_color`",
                "ALTER TABLE `dropdown_settings` ADD COLUMN `background_position` VARCHAR(50) DEFAULT 'center' AFTER `background_image`"
            ];

            foreach ($dropdownCols as $query) {
                try {
                    $pdo->exec($query);
                    $updates++;
                    preg_match('/ADD COLUMN `(\w+)`/', $query, $matches);
                    echo "<p class='success'>✅ Added column: " . ($matches[1] ?? 'column') . "</p>";
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'Duplicate column') === false) {
                        echo "<p class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
            }
        } else {
            echo "<p class='warning'>⚠️ dropdown_settings table doesn't exist</p>";
        }
        echo "</div>";

        // Create social_media table
        echo "<div class='step'>";
        echo "<h3>Step 4: Social Media</h3>";

        if (!tableExists($pdo, 'social_media')) {
            $query = "CREATE TABLE `social_media` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `facebook` VARCHAR(500),
                `twitter` VARCHAR(500),
                `instagram` VARCHAR(500),
                `linkedin` VARCHAR(500),
                `youtube` VARCHAR(500),
                `google_plus` VARCHAR(500),
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            try {
                $pdo->exec($query);
                $pdo->exec("INSERT INTO `social_media` (`id`) VALUES (1)");
                echo "<p class='success'>✅ Created social_media table</p>";
                $updates++;
            } catch (PDOException $e) {
                echo "<p class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p class='info'>ℹ️ social_media table already exists</p>";
        }
        echo "</div>";

        // Hero settings and slides
        echo "<div class='step'>";
        echo "<h3>Step 5: Hero Settings & Slides</h3>";

        if (!tableExists($pdo, 'hero_settings')) {
            $query = "CREATE TABLE `hero_settings` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `autoplay` TINYINT(1) DEFAULT 1,
                `duration` INT DEFAULT 5,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            try {
                $pdo->exec($query);
                $pdo->exec("INSERT INTO `hero_settings` (`id`) VALUES (1)");
                echo "<p class='success'>✅ Created hero_settings table</p>";
                $updates++;
            } catch (PDOException $e) {
                echo "<p class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }

        // Update hero_slides
        if (tableExists($pdo, 'hero_slides')) {
            $heroSlidesCols = [
                "subtitle_color" => "ALTER TABLE `hero_slides` ADD COLUMN `subtitle_color` VARCHAR(7) DEFAULT '#FFD200' AFTER `subtitle`",
                "button1_text" => "ALTER TABLE `hero_slides` ADD COLUMN `button1_text` VARCHAR(100) AFTER `description`",
                "button1_url" => "ALTER TABLE `hero_slides` ADD COLUMN `button1_url` VARCHAR(500) AFTER `button1_text`",
                "button2_text" => "ALTER TABLE `hero_slides` ADD COLUMN `button2_text` VARCHAR(100) AFTER `button1_url`",
                "button2_url" => "ALTER TABLE `hero_slides` ADD COLUMN `button2_url` VARCHAR(500) AFTER `button2_text`"
            ];

            foreach ($heroSlidesCols as $col => $query) {
                if (!columnExists($pdo, 'hero_slides', $col)) {
                    try {
                        $pdo->exec($query);
                        echo "<p class='success'>✅ Added hero_slides.$col</p>";
                        $updates++;
                    } catch (PDOException $e) {
                        echo "<p class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
                    }
                }
            }
        }
        echo "</div>";

        // Services, Features, Projects, News settings tables
        $settingsTables = [
            'services_settings' => ['section_title' => 'Our Core Services', 'section_subtitle' => 'Comprehensive solutions across energy sectors'],
            'features_settings' => ['title' => 'Why Choose Us', 'subtitle' => 'Excellence in every aspect'],
            'projects_settings' => ['section_title' => 'Our Projects', 'section_subtitle' => 'Showcasing our best work'],
            'news_settings' => ['section_title' => 'Latest News', 'section_subtitle' => 'Stay updated with our latest updates']
        ];

        echo "<div class='step'>";
        echo "<h3>Step 6: Content Section Settings</h3>";

        foreach ($settingsTables as $tableName => $defaults) {
            if (!tableExists($pdo, $tableName)) {
                $firstCol = array_keys($defaults)[0];
                $secondCol = array_keys($defaults)[1];

                $query = "CREATE TABLE `$tableName` (
                    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `$firstCol` VARCHAR(255) DEFAULT '{$defaults[$firstCol]}',
                    `$secondCol` VARCHAR(255) DEFAULT '{$defaults[$secondCol]}',
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

                try {
                    $pdo->exec($query);
                    $pdo->exec("INSERT INTO `$tableName` (`id`) VALUES (1)");
                    echo "<p class='success'>✅ Created $tableName table</p>";
                    $updates++;
                } catch (PDOException $e) {
                    echo "<p class='error'>❌ $tableName: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            } else {
                echo "<p class='info'>ℹ️ $tableName already exists</p>";
            }
        }
        echo "</div>";

        // Component visibility
        echo "<div class='step'>";
        echo "<h3>Step 7: Component Visibility</h3>";

        if (!tableExists($pdo, 'component_visibility')) {
            $query = "CREATE TABLE `component_visibility` (
                `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `hero` TINYINT(1) DEFAULT 1,
                `services` TINYINT(1) DEFAULT 1,
                `about` TINYINT(1) DEFAULT 1,
                `features` TINYINT(1) DEFAULT 1,
                `projects` TINYINT(1) DEFAULT 1,
                `news` TINYINT(1) DEFAULT 1,
                `footer` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

            try {
                $pdo->exec($query);
                $pdo->exec("INSERT INTO `component_visibility` (`id`) VALUES (1)");
                echo "<p class='success'>✅ Created component_visibility table</p>";
                $updates++;
            } catch (PDOException $e) {
                echo "<p class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p class='info'>ℹ️ component_visibility already exists</p>";
        }
        echo "</div>";

        echo "<h2>📊 Summary</h2>";
        echo "<p><strong class='success'>Updates Applied: $updates</strong></p>";
        echo "<p><strong class='error'>Errors: $errors</strong></p>";

        if ($errors == 0) {
            echo "<h3 class='success'>🎉 Schema Update Complete!</h3>";
            echo "<p>Next steps:</p>";
            echo "<ol>";
            echo "<li><a href='compare_schema.php' style='color: #4ec9b0;'>View Updated Schema</a></li>";
            echo "<li>Update save_content.php to handle new fields</li>";
            echo "<li>Test admin forms</li>";
            echo "</ol>";
        }

    } catch (PDOException $e) {
        echo "<p class='error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    echo "</div>";

} else {
    ?>
    <div class="box">
        <h2>⚠️ Important Notice</h2>
        <p>This script will automatically update your database schema to match the admin form fields.</p>
        <p><strong>What it does:</strong></p>
        <ul>
            <li>Adds missing columns to existing tables</li>
            <li>Creates missing tables (navbar_settings, social_media, hero_settings, etc.)</li>
            <li>Inserts default data where needed</li>
            <li>Preserves existing data (no data loss)</li>
        </ul>

        <p class='warning'><strong>Recommendation:</strong> Backup your database before proceeding.</p>
    </div>

    <div class="box">
        <h2>🚀 Run Schema Update</h2>
        <form method="get">
            <input type="hidden" name="action" value="update">
            <button type="submit">⚡ UPDATE DATABASE SCHEMA NOW</button>
        </form>
    </div>
    <?php
}
?>

</body>
</html>

<?php
/**
 * Database Setup Script for Petroleum & Gas Admin Dashboard
 * This script creates all necessary tables and populates them with sample data
 */

// Load environment-based database configuration
require_once __DIR__ . '/config.php';

echo "🌍 Environment: " . $dbConfig['environment'] . "\n";
echo "📊 Database: {$username}@{$host}/{$dbname}\n";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to database successfully\n";
} catch(PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

try {
    echo "🔧 Setting up database tables...\n";

    // Read and execute the additional tables SQL
    $additionalTablesSql = file_get_contents(__DIR__ . '/additional_tables.sql');
    if (!$additionalTablesSql) {
        throw new Exception("Could not read additional_tables.sql file");
    }

    // Split SQL statements and execute them
    $statements = explode(';', $additionalTablesSql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    echo "✅ Database tables created successfully\n";

    // Read and execute the sample data SQL
    echo "📊 Populating tables with sample data...\n";
    $sampleDataSql = file_get_contents(__DIR__ . '/sample_data.sql');
    if (!$sampleDataSql) {
        throw new Exception("Could not read sample_data.sql file");
    }

    // Split SQL statements and execute them
    $statements = explode(';', $sampleDataSql);
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    echo "✅ Sample data inserted successfully\n";

    // Create default records for singleton tables
    echo "🏗️ Creating default configuration records...\n";

    $defaultRecords = [
        "INSERT IGNORE INTO navbar_settings (id) VALUES (1)",
        "INSERT IGNORE INTO dropdown_settings (id) VALUES (1)",
        "INSERT IGNORE INTO hero_settings (id) VALUES (1)",
        "INSERT IGNORE INTO services_settings (id) VALUES (1)",
        "INSERT IGNORE INTO about_section (id) VALUES (1)",
        "INSERT IGNORE INTO features_settings (id) VALUES (1)",
        "INSERT IGNORE INTO projects_settings (id) VALUES (1)",
        "INSERT IGNORE INTO news_settings (id) VALUES (1)",
        "INSERT IGNORE INTO layout_settings (id) VALUES (1)",
        "INSERT IGNORE INTO custom_code (id) VALUES (1)",
        "INSERT IGNORE INTO footer_settings (id) VALUES (1)",
        "INSERT IGNORE INTO social_media (id) VALUES (1)",
        "INSERT IGNORE INTO seo_settings_extended (id) VALUES (1)"
    ];

    foreach ($defaultRecords as $sql) {
        $pdo->exec($sql);
    }
    echo "✅ Default configuration records created\n";

    echo "\n🎉 Database setup completed successfully!\n";
    echo "\nYour admin dashboard is now ready to save data to the database.\n";
    echo "📋 Summary:\n";
    echo "- All necessary tables have been created\n";
    echo "- Sample data has been populated\n";
    echo "- Default configuration records are in place\n";
    echo "- The admin dashboard can now save and load from the database\n\n";

    echo "🔗 Next steps:\n";
    echo "1. Open the admin dashboard: admin/index.html\n";
    echo "2. Make changes to your content\n";
    echo "3. Click 'Save' to store data in the database\n";
    echo "4. Your changes will be preserved between sessions\n\n";

    // Check table counts
    echo "📊 Table record counts:\n";
    $tables = [
        'site_settings', 'navbar_settings', 'dropdown_settings', 'hero_slides',
        'services', 'about_section', 'features', 'projects', 'news_articles',
        'layout_sections', 'components', 'footer_columns', 'footer_menu',
        'social_media', 'seo_settings_extended'
    ];

    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "- $table: $count records\n";
        } catch (Exception $e) {
            echo "- $table: Error checking count\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration and try again.\n";
}
?>
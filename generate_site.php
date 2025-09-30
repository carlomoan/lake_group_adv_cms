<?php
/**
 * Site Generator - Creates temporary HTML file with database content
 * This script fetches content from database and generates a complete HTML file
 */

// Load database configuration
require_once __DIR__ . '/config.php';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "📡 Connected to database ($dbname)\n";

    // Fetch all content from database
    $content = [];

    // Load site settings
    $stmt = $pdo->query("SELECT * FROM site_settings WHERE id = 1");
    $siteSettings = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($siteSettings) {
        $content['siteSettings'] = [
            'siteTitle' => $siteSettings['site_title'],
            'tagline' => $siteSettings['tagline'],
            'primaryColor' => $siteSettings['primary_color'],
            'secondaryColor' => $siteSettings['secondary_color'],
            'tertiaryColor' => $siteSettings['tertiary_color'],
            'logo' => [
                'logoMain' => $siteSettings['logo_main'],
                'logoTransparent' => $siteSettings['logo_transparent'],
                'logoWidth' => $siteSettings['logo_width']
            ]
        ];
    }

    // Load hero slides
    $stmt = $pdo->query("SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY slide_order");
    $heroSlides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($heroSlides) {
        $content['hero'] = ['slides' => []];
        foreach ($heroSlides as $slide) {
            $content['hero']['slides'][] = [
                'image' => $slide['image_url'],
                'subtitle' => $slide['subtitle'],
                'subtitleColor' => $slide['subtitle_color'],
                'title' => $slide['title'],
                'description' => $slide['description'],
                'button1Text' => $slide['button1_text'],
                'button1Url' => $slide['button1_url'],
                'button2Text' => $slide['button2_text'],
                'button2Url' => $slide['button2_url']
            ];
        }
    }

    // Load services
    $stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order");
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($services) {
        $content['services'] = ['items' => []];
        foreach ($services as $service) {
            $content['services']['items'][] = [
                'title' => $service['title'],
                'image' => $service['image_url'],
                'description' => $service['description']
            ];
        }
    }

    // Load projects
    $stmt = $pdo->query("SELECT * FROM projects WHERE is_active = 1 ORDER BY sort_order LIMIT 6");
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($projects) {
        $content['projects'] = ['items' => []];
        foreach ($projects as $project) {
            $content['projects']['items'][] = [
                'title' => $project['title'],
                'image' => $project['image_url'],
                'description' => $project['description'],
                'category' => $project['category'],
                'link' => $project['url']
            ];
        }
    }

    echo "📦 Content loaded from database\n";
    echo "   - Site Title: " . ($content['siteSettings']['siteTitle'] ?? 'Not set') . "\n";
    echo "   - Hero Slides: " . count($content['hero']['slides'] ?? []) . "\n";
    echo "   - Services: " . count($content['services']['items'] ?? []) . "\n";
    echo "   - Projects: " . count($content['projects']['items'] ?? []) . "\n";

    // Read the template HTML file
    $templateFile = __DIR__ . '/index_template.html';
    if (!file_exists($templateFile)) {
        // If template doesn't exist, create it from current index.html
        copy(__DIR__ . '/index.html', $templateFile);
        echo "📄 Created template from index.html\n";
    }

    $htmlTemplate = file_get_contents($templateFile);
    echo "📖 Loaded HTML template\n";

    // Replace placeholders in template
    $siteTitle = $content['siteSettings']['siteTitle'] ?? 'Petroleum and Gas – Gas and Oil WordPress theme';
    $primaryColor = $content['siteSettings']['primaryColor'] ?? '#FFD200';
    $secondaryColor = $content['siteSettings']['secondaryColor'] ?? '#484939';

    // Replace title
    $htmlTemplate = str_replace(
        '<title>Petroleum and Gas – Gas and Oil WordPress theme</title>',
        '<title>' . htmlspecialchars($siteTitle) . '</title>',
        $htmlTemplate
    );

    // Inject content as JavaScript variable
    $contentJson = json_encode($content, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

    // Find the Vue.js app script and inject the content
    $injectionScript = "
        <script>
            // Pre-loaded content from database
            window.DATABASE_CONTENT = $contentJson;
            window.CONTENT_LOADED_FROM_DATABASE = true;
        </script>";

    // Insert before the main Vue.js script
    $htmlTemplate = str_replace(
        '<script>',
        $injectionScript . "\n        <script>",
        $htmlTemplate
    );

    // Modify the Vue.js loadContent method to use pre-loaded data
    $htmlTemplate = str_replace(
        'async loadContent() {
                        try {
                            // Try loading from database first
                            const response = await fetch(\'admin/save_content.php\', {
                                method: \'GET\',
                                headers: {
                                    \'Content-Type\': \'application/json\',
                                }
                            });

                            if (response.ok) {
                                const result = await response.json();
                                if (result.success && result.content) {
                                    console.log(\'✅ Content loaded from database for main website\');
                                    this.content = this.mergeWithDefaults(result.content);
                                } else {
                                    throw new Error(\'No content in database response\');
                                }
                            } else {
                                throw new Error(\'Database response not ok: \' + response.status);
                            }',
        'async loadContent() {
                        try {
                            // Use pre-loaded content from database
                            if (window.DATABASE_CONTENT && window.CONTENT_LOADED_FROM_DATABASE) {
                                console.log(\'✅ Using pre-loaded content from database\');
                                this.content = this.mergeWithDefaults(window.DATABASE_CONTENT);
                            } else {
                                // Fallback: Try loading from database via API
                                const response = await fetch(\'admin/save_content.php\', {
                                    method: \'GET\',
                                    headers: {
                                        \'Content-Type\': \'application/json\',
                                    }
                                });

                                if (response.ok) {
                                    const result = await response.json();
                                    if (result.success && result.content) {
                                        console.log(\'✅ Content loaded from database API fallback\');
                                        this.content = this.mergeWithDefaults(result.content);
                                    } else {
                                        throw new Error(\'No content in database response\');
                                    }
                                } else {
                                    throw new Error(\'Database response not ok: \' + response.status);
                                }
                            }',
        $htmlTemplate
    );

    // Write the generated HTML to temporary file
    $outputFile = __DIR__ . '/index_generated.html';
    file_put_contents($outputFile, $htmlTemplate);

    echo "✅ Generated website saved to: index_generated.html\n";
    echo "🌐 Site title set to: $siteTitle\n";

    // Handle file priority issue: if index.html exists, rename it as backup
    $indexHtmlFile = __DIR__ . '/index.html';
    if (file_exists($indexHtmlFile)) {
        $backupFile = __DIR__ . '/index_old_backup.html';
        if (rename($indexHtmlFile, $backupFile)) {
            echo "📦 Renamed old index.html -> index_old_backup.html\n";
        } else {
            echo "⚠️  Warning: Could not rename index.html (check permissions)\n";
        }
    }

    // Create/update index.php redirect (this will be served if index.html doesn't exist)
    $indexPhp = '<?php
// Auto-generated redirect to database-powered website
// This file is automatically updated when database content changes
header("Location: index_generated.html");
exit;
?>';

    if (file_put_contents(__DIR__ . '/index.php', $indexPhp)) {
        echo "🔄 Updated redirect file: index.php -> index_generated.html\n";
    } else {
        echo "⚠️  Warning: Could not update index.php (check permissions)\n";
    }

    echo "\n🎉 Website generation complete!\n";
    echo "📋 File priority handled automatically:\n";
    echo "   ✅ Old index.html backed up (if existed)\n";
    echo "   ✅ index.php redirects to index_generated.html\n";
    echo "   ✅ Website will now show database content!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
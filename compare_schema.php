<?php
/**
 * Compare Admin Forms with Database Schema
 * Shows what form fields exist vs what database columns exist
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config.php';

// Form fields from admin/index.html
$formFields = [
    'siteSettings' => [
        'siteTitle', 'tagline', 'description', 'language', 'timezone',
        'faviconUrl', 'appleTouchIcon', 'titleTemplate',
        'primaryColor', 'secondaryColor', 'tertiaryColor',
        'logo' => ['logoMain', 'logoTransparent', 'logoWidth', 'logoHeight', 'altText'],
        'navbar' => [
            'position', 'height', 'backgroundColor', 'transparency', 'textColor', 'hoverColor',
            'useTransparentLogo',
            'dropdown' => [
                'layoutType', 'backgroundType', 'backgroundColor', 'backgroundImage', 'backgroundPosition',
                'gradientStart', 'gradientEnd', 'textColor', 'hoverTextColor',
                'borderRadius', 'shadow', 'animation', 'width', 'fontSize', 'lineHeight',
                'itemPadding', 'borderStyle', 'enableMultiLevel', 'arrowStyle'
            ]
        ],
        'typography' => ['headerFont', 'bodyFont'],
        'socialMedia' => ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'googlePlus'],
        'componentVisibility' => ['hero', 'services', 'about', 'features', 'projects', 'news', 'footer']
    ],
    'hero' => [
        'autoplay', 'duration',
        'slides' => ['image', 'subtitle', 'subtitleColor', 'title', 'description', 'button1Text', 'button1Url', 'button2Text', 'button2Url']
    ],
    'services' => [
        'sectionTitle', 'sectionSubtitle',
        'items' => ['image', 'title', 'description', 'url']
    ],
    'aboutSection' => [
        'title', 'description', 'image', 'backgroundColor', 'textColor'
    ],
    'features' => [
        'title', 'subtitle',
        'items' => ['title', 'icon', 'description']
    ],
    'projects' => [
        'sectionTitle', 'sectionSubtitle',
        'items' => ['title', 'image', 'description', 'category', 'link']
    ],
    'news' => [
        'sectionTitle', 'sectionSubtitle',
        'items' => ['title', 'image', 'excerpt', 'date', 'author', 'link']
    ],
    'footer' => [
        'layout', 'backgroundType', 'backgroundColor', 'backgroundImage', 'backgroundSize',
        'gradientStart', 'gradientEnd', 'textColor', 'linkColor', 'linkHoverColor',
        'copyrightText', 'showSocialMedia', 'socialMediaPosition'
    ]
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Schema Comparison</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #569cd6; }
        .box { background: #2d2d2d; padding: 20px; margin: 20px 0; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th, table td { padding: 8px; text-align: left; border-bottom: 1px solid #444; }
        table th { background: #252526; }
        h2 { color: #569cd6; }
        pre { background: #252526; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Schema Comparison - Forms vs Database</h1>

    <?php
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get all tables
        $tables = [
            'site_settings',
            'navbar_settings',
            'dropdown_settings',
            'social_media',
            'hero_settings',
            'hero_slides',
            'services_settings',
            'services',
            'about_section',
            'features_settings',
            'features',
            'projects_settings',
            'projects',
            'news_settings',
            'news_articles',
            'footer_settings',
            'seo_settings'
        ];

        foreach ($tables as $table) {
            echo "<div class='box'>";
            echo "<h2>📋 Table: $table</h2>";

            try {
                $stmt = $pdo->query("SHOW COLUMNS FROM `$table`");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo "<table>";
                echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Default</th></tr>";
                foreach ($columns as $col) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
                    echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
                    echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
                    echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";

            } catch (PDOException $e) {
                echo "<p class='warning'>⚠️ Table doesn't exist: " . htmlspecialchars($e->getMessage()) . "</p>";
            }

            echo "</div>";
        }

    } catch (PDOException $e) {
        echo "<p class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>

</body>
</html>

<?php
/**
 * Comprehensive Fix: Admin Forms Save/Load
 *
 * This script diagnoses and shows what's being saved vs what's expected
 * Run this to see exactly what data is being sent and what's in the database
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/config.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Forms Diagnostic</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; max-width: 1400px; margin: 0 auto; }
        .success { color: #4ec9b0; font-weight: bold; }
        .error { color: #f48771; font-weight: bold; }
        .warning { color: #dcdcaa; }
        .info { color: #569cd6; }
        pre { background: #2d2d2d; padding: 15px; border-radius: 5px; overflow-x: auto; white-space: pre-wrap; }
        .box { background: #2d2d2d; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #569cd6; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table th, table td { padding: 8px; text-align: left; border-bottom: 1px solid #444; }
        table th { background: #252526; }
        h2 { color: #569cd6; }
        .mismatch { background: #4a1a1a; }
    </style>
</head>
<body>
    <h1>🔍 Admin Forms Save/Load Diagnostic</h1>

    <?php
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        echo "<div class='box'>";
        echo "<h2>Current Database Content</h2>";

        // Site Settings
        echo "<h3>Site Settings Table</h3>";
        $stmt = $pdo->query("SELECT * FROM site_settings WHERE id = 1");
        $siteSettings = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($siteSettings) {
            echo "<table>";
            echo "<tr><th>Database Column</th><th>Current Value</th><th>Admin Form Field</th><th>Status</th></tr>";

            $mappings = [
                'site_title' => ['form' => 'content.siteSettings.siteTitle', 'value' => $siteSettings['site_title']],
                'tagline' => ['form' => 'content.siteSettings.tagline', 'value' => $siteSettings['tagline']],
                'logo_main' => ['form' => 'content.siteSettings.logoMain (FLAT) or content.siteSettings.logo.logoMain (NESTED)', 'value' => $siteSettings['logo_main']],
                'logo_transparent' => ['form' => 'content.siteSettings.logoTransparent or logo.logoTransparent', 'value' => $siteSettings['logo_transparent']],
                'logo_width' => ['form' => 'content.siteSettings.logo.logoWidth', 'value' => $siteSettings['logo_width']],
                'primary_color' => ['form' => 'content.siteSettings.primaryColor', 'value' => $siteSettings['primary_color']],
                'secondary_color' => ['form' => 'content.siteSettings.secondaryColor', 'value' => $siteSettings['secondary_color']],
                'tertiary_color' => ['form' => 'content.siteSettings.tertiaryColor', 'value' => $siteSettings['tertiary_color']]
            ];

            foreach ($mappings as $dbCol => $info) {
                $hasValue = !empty($info['value']);
                $statusClass = $hasValue ? 'success' : 'warning';
                $status = $hasValue ? '✅ Has Data' : '⚠️ Empty';

                // Highlight mismatched structure
                $rowClass = (strpos($info['form'], 'FLAT') !== false || strpos($info['form'], 'or') !== false) ? ' class="mismatch"' : '';

                echo "<tr$rowClass>";
                echo "<td><code>$dbCol</code></td>";
                echo "<td>" . htmlspecialchars($info['value'] ?? 'NULL') . "</td>";
                echo "<td><code>{$info['form']}</code></td>";
                echo "<td class='$statusClass'>$status</td>";
                echo "</tr>";
            }
            echo "</table>";

            if ($siteSettings['logo_main'] || $siteSettings['logo_transparent']) {
                echo "<p class='info'>📷 Logo URLs in database:</p>";
                echo "<ul>";
                if ($siteSettings['logo_main']) {
                    echo "<li>Main Logo: <a href='" . htmlspecialchars($siteSettings['logo_main']) . "' target='_blank' style='color: #4ec9b0;'>" . htmlspecialchars($siteSettings['logo_main']) . "</a></li>";
                }
                if ($siteSettings['logo_transparent']) {
                    echo "<li>Transparent Logo: <a href='" . htmlspecialchars($siteSettings['logo_transparent']) . "' target='_blank' style='color: #4ec9b0;'>" . htmlspecialchars($siteSettings['logo_transparent']) . "</a></li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='error'>❌ No logo URLs in database!</p>";
            }
        }

        // Navbar Settings
        echo "<h3>Navbar Settings Table</h3>";
        if (tableExists($pdo, 'navbar_settings')) {
            $stmt = $pdo->query("SELECT * FROM navbar_settings WHERE id = 1");
            $navbarSettings = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($navbarSettings) {
                echo "<table>";
                echo "<tr><th>Column</th><th>Value</th></tr>";
                foreach ($navbarSettings as $col => $val) {
                    if ($col === 'id' || $col === 'created_at' || $col === 'updated_at') continue;
                    echo "<tr><td>$col</td><td>" . htmlspecialchars($val ?? 'NULL') . "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='warning'>⚠️ No navbar settings record (id=1)</p>";
            }
        } else {
            echo "<p class='error'>❌ navbar_settings table doesn't exist</p>";
        }

        echo "</div>";

        // Test what admin form would send
        echo "<div class='box'>";
        echo "<h2>What Admin Form Structure Sends</h2>";
        echo "<p class='info'>Based on v-model bindings in admin/index.html:</p>";

        $formStructure = [
            'siteSettings' => [
                'siteTitle' => 'content.siteSettings.siteTitle',
                'tagline' => 'content.siteSettings.tagline',
                'logoMain' => 'content.siteSettings.logoMain (⚠️ FLAT)',
                'logoTransparent' => 'content.siteSettings.logoTransparent (⚠️ FLAT)',
                'logo' => [
                    'logoWidth' => 'content.siteSettings.logo.logoWidth (✅ NESTED)',
                    'logoHeight' => 'content.siteSettings.logo.logoHeight (✅ NESTED)',
                    'altText' => 'content.siteSettings.logo.altText (✅ NESTED)'
                ],
                'primaryColor' => 'content.siteSettings.primaryColor',
                'secondaryColor' => 'content.siteSettings.secondaryColor',
                'tertiaryColor' => 'content.siteSettings.tertiaryColor'
            ]
        ];

        echo "<pre>" . json_encode($formStructure, JSON_PRETTY_PRINT) . "</pre>";

        echo "<p class='error'>🚨 PROBLEM IDENTIFIED:</p>";
        echo "<ul>";
        echo "<li>Admin form uses <code>content.siteSettings.logoMain</code> (flat)</li>";
        echo "<li>But save_content.php expects <code>content.siteSettings.logo.logoMain</code> (nested)</li>";
        echo "<li>Same issue with logoTransparent</li>";
        echo "</ul>";

        echo "</div>";

        // Show what save_content.php expects
        echo "<div class='box'>";
        echo "<h2>What save_content.php Expects</h2>";
        echo "<p class='info'>From admin/save_content.php line 81-82:</p>";
        echo "<pre>";
        echo "logo_main = \$content['siteSettings']['logo']['logoMain'] ?? ''\n";
        echo "logo_transparent = \$content['siteSettings']['logo']['logoTransparent'] ?? ''\n";
        echo "</pre>";
        echo "<p class='error'>❌ This expects NESTED structure, but form sends FLAT!</p>";
        echo "</div>";

        // Solution
        echo "<div class='box'>";
        echo "<h2>✅ Solution</h2>";
        echo "<p>We need to fix the mismatch in ONE of two ways:</p>";

        echo "<h3>Option 1: Fix Admin Form (Recommended)</h3>";
        echo "<p>Change admin form to use nested structure:</p>";
        echo "<pre>";
        echo "<!-- CHANGE FROM: -->\n";
        echo "&lt;input v-model=\"content.siteSettings.logoMain\"&gt;\n\n";
        echo "<!-- CHANGE TO: -->\n";
        echo "&lt;input v-model=\"content.siteSettings.logo.logoMain\"&gt;\n";
        echo "</pre>";

        echo "<h3>Option 2: Fix save_content.php (Alternative)</h3>";
        echo "<p>Update save_content.php to handle both structures:</p>";
        echo "<pre>";
        echo "\$logoMain = \$content['siteSettings']['logo']['logoMain'] \n";
        echo "    ?? \$content['siteSettings']['logoMain'] \n";
        echo "    ?? '';\n";
        echo "</pre>";

        echo "</div>";

        // Check other mismatches
        echo "<div class='box'>";
        echo "<h2>Checking All Form Sections</h2>";

        $sections = [
            'hero_slides' => 'Hero Slider',
            'services' => 'Services',
            'about_section' => 'About Section',
            'features' => 'Features',
            'projects' => 'Projects',
            'news_articles' => 'News',
            'footer_settings' => 'Footer'
        ];

        foreach ($sections as $table => $label) {
            if (tableExists($pdo, $table)) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $count = $result['count'];

                if ($count > 0) {
                    echo "<p class='success'>✅ $label: $count records</p>";
                } else {
                    echo "<p class='warning'>⚠️ $label: Table exists but no data</p>";
                }
            } else {
                echo "<p class='error'>❌ $label: Table missing</p>";
            }
        }

        echo "</div>";

    } catch (PDOException $e) {
        echo "<p class='error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    function tableExists($pdo, $table) {
        try {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
    ?>

    <div class="box">
        <h2>🔧 Next Steps</h2>
        <ol>
            <li>Run <code>auto_update_schema.php</code> to create missing tables/columns</li>
            <li>Fix the admin form logo fields (use nested structure)</li>
            <li>OR fix save_content.php to handle both structures</li>
            <li>Test saving from admin dashboard</li>
            <li>Verify data appears in database</li>
            <li>Regenerate website and check if logos appear</li>
        </ol>
    </div>

</body>
</html>

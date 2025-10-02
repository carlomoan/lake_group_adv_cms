<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Load environment-based database configuration
require_once __DIR__ . '/../config.php';

try {
    // Validate that all required config variables are set
    if (empty($host) || empty($dbname) || empty($username)) {
        throw new Exception('Database configuration is incomplete');
    }

    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Test the connection
    $pdo->query("SELECT 1")->fetch();

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage(),
        'environment' => $dbConfig['environment'] ?? 'unknown',
        'details' => [
            'host' => $host,
            'database' => $dbname,
            'username' => $username,
            'dsn' => isset($dsn) ? $dsn : 'not set'
        ]
    ]);
    exit;
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Configuration error: ' . $e->getMessage()
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['content'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input data']);
        exit;
    }

    $content = $input['content'];

    try {
        $pdo->beginTransaction();

        // Save Site Settings
        if (isset($content['siteSettings'])) {
            $stmt = $pdo->prepare("
                UPDATE site_settings SET
                    site_title = ?,
                    tagline = ?,
                    logo_main = ?,
                    logo_transparent = ?,
                    logo_width = ?,
                    primary_color = ?,
                    secondary_color = ?,
                    tertiary_color = ?
                WHERE id = 1
            ");
            // Handle both flat and nested logo structure
            $logoMain = $content['siteSettings']['logo']['logoMain']
                     ?? $content['siteSettings']['logoMain']
                     ?? '';

            $logoTransparent = $content['siteSettings']['logo']['logoTransparent']
                            ?? $content['siteSettings']['logoTransparent']
                            ?? '';

            $logoWidth = $content['siteSettings']['logo']['logoWidth']
                      ?? $content['siteSettings']['logoWidth']
                      ?? 150;

            $stmt->execute([
                $content['siteSettings']['siteTitle'] ?? '',
                $content['siteSettings']['tagline'] ?? '',
                $logoMain,
                $logoTransparent,
                $logoWidth,
                $content['siteSettings']['primaryColor'] ?? '#FFD200',
                $content['siteSettings']['secondaryColor'] ?? '#484939',
                $content['siteSettings']['tertiaryColor'] ?? '#1E3A8A'
            ]);
        }

        // Save Navbar Settings
        if (isset($content['siteSettings']['navbar'])) {
            $navbar = $content['siteSettings']['navbar'];
            $stmt = $pdo->prepare("
                UPDATE navbar_settings SET
                    position = ?,
                    height = ?,
                    background_color = ?,
                    transparency = ?,
                    text_color = ?,
                    hover_color = ?
                WHERE id = 1
            ");
            $stmt->execute([
                $navbar['position'],
                $navbar['height'],
                $navbar['backgroundColor'],
                $navbar['transparency'],
                $navbar['textColor'],
                $navbar['hoverColor']
            ]);

            // Save Dropdown Settings
            if (isset($navbar['dropdown'])) {
                $dropdown = $navbar['dropdown'];
                $stmt = $pdo->prepare("
                    UPDATE dropdown_settings SET
                        layout_type = ?,
                        background_type = ?,
                        background_color = ?,
                        gradient_start = ?,
                        gradient_end = ?,
                        text_color = ?,
                        hover_text_color = ?,
                        border_radius = ?,
                        shadow_intensity = ?,
                        animation = ?,
                        width = ?,
                        font_size = ?,
                        line_height = ?,
                        item_padding = ?,
                        border_style = ?,
                        enable_multi_level = ?,
                        arrow_style = ?
                    WHERE id = 1
                ");
                $stmt->execute([
                    $dropdown['layoutType'],
                    $dropdown['backgroundType'],
                    $dropdown['backgroundColor'],
                    $dropdown['gradientStart'],
                    $dropdown['gradientEnd'],
                    $dropdown['textColor'],
                    $dropdown['hoverTextColor'],
                    $dropdown['borderRadius'],
                    $dropdown['shadow'],
                    $dropdown['animation'],
                    $dropdown['width'],
                    $dropdown['fontSize'],
                    $dropdown['lineHeight'],
                    $dropdown['itemPadding'],
                    $dropdown['borderStyle'],
                    $dropdown['enableMultiLevel'] ? 1 : 0,
                    $dropdown['arrowStyle']
                ]);
            }
        }

        // Save Hero Slides
        if (isset($content['hero']['slides'])) {
            // Clear existing slides
            $pdo->exec("DELETE FROM hero_slides");

            $stmt = $pdo->prepare("
                INSERT INTO hero_slides (slide_order, image_url, subtitle, subtitle_color, title, description, button1_text, button1_url, button2_text, button2_url, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");

            foreach ($content['hero']['slides'] as $index => $slide) {
                $stmt->execute([
                    $index + 1,
                    $slide['image'] ?? '',
                    $slide['subtitle'] ?? '',
                    $slide['subtitleColor'] ?? '#FFD200',
                    $slide['title'] ?? '',
                    $slide['description'] ?? '',
                    $slide['button1Text'] ?? '',
                    $slide['button1Url'] ?? '',
                    $slide['button2Text'] ?? '',
                    $slide['button2Url'] ?? ''
                ]);
            }
        }

        // Save Services
        if (isset($content['services']['items'])) {
            // Update services settings
            $stmt = $pdo->prepare("
                UPDATE services_settings SET
                    section_title = ?,
                    section_subtitle = ?
                WHERE id = 1
            ");
            $stmt->execute([
                $content['services']['title'] ?? 'Our Core Services',
                $content['services']['subtitle'] ?? 'Comprehensive solutions across energy sectors'
            ]);

            // Clear existing services
            $pdo->exec("DELETE FROM services");

            $stmt = $pdo->prepare("
                INSERT INTO services (title, image_url, description, url, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, 1)
            ");

            foreach ($content['services']['items'] as $index => $service) {
                $stmt->execute([
                    $service['title'],
                    $service['image'] ?? '',
                    $service['description'],
                    $service['url'] ?? '',
                    $index + 1
                ]);
            }
        }

        // Save About Section
        if (isset($content['aboutSection'])) {
            $stmt = $pdo->prepare("
                UPDATE about_section SET
                    title = ?,
                    description = ?,
                    image_url = ?,
                    background_color = ?,
                    text_color = ?
                WHERE id = 1
            ");
            $stmt->execute([
                $content['aboutSection']['title'],
                $content['aboutSection']['description'],
                $content['aboutSection']['image'] ?? '',
                $content['aboutSection']['backgroundColor'],
                $content['aboutSection']['textColor']
            ]);
        }

        // Save Features
        if (isset($content['features']['items'])) {
            // Update features settings
            $stmt = $pdo->prepare("
                UPDATE features_settings SET
                    section_title = ?,
                    section_subtitle = ?
                WHERE id = 1
            ");
            $stmt->execute([
                $content['features']['title'] ?? 'Why Choose Us',
                $content['features']['subtitle'] ?? 'Excellence in every aspect'
            ]);

            // Clear existing features
            $pdo->exec("DELETE FROM features");

            $stmt = $pdo->prepare("
                INSERT INTO features (title, icon_url, description, sort_order, is_active)
                VALUES (?, ?, ?, ?, 1)
            ");

            foreach ($content['features']['items'] as $index => $feature) {
                $stmt->execute([
                    $feature['title'],
                    $feature['icon'] ?? '',
                    $feature['description'],
                    $index + 1
                ]);
            }
        }

        // Save Projects
        if (isset($content['projects']['items'])) {
            // Update projects settings
            $stmt = $pdo->prepare("
                UPDATE projects_settings SET
                    section_title = ?,
                    section_subtitle = ?
                WHERE id = 1
            ");
            $stmt->execute([
                $content['projects']['sectionTitle'] ?? 'Latest Projects',
                $content['projects']['sectionSubtitle'] ?? 'Our recent work'
            ]);

            // Clear existing projects
            $pdo->exec("DELETE FROM projects");

            $stmt = $pdo->prepare("
                INSERT INTO projects (title, category, image_url, description, url, client, project_date, status, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");

            foreach ($content['projects']['items'] as $index => $project) {
                $stmt->execute([
                    $project['title'],
                    $project['category'] ?? '',
                    $project['image'] ?? '',
                    $project['description'],
                    $project['url'] ?? '',
                    $project['client'] ?? '',
                    $project['date'] ?? null,
                    $project['status'] ?? 'completed',
                    $index + 1
                ]);
            }
        }

        // Save News Articles
        if (isset($content['news']['articles'])) {
            // Update news settings
            $stmt = $pdo->prepare("
                UPDATE news_settings SET
                    section_title = ?,
                    section_subtitle = ?
                WHERE id = 1
            ");
            $stmt->execute([
                $content['news']['sectionTitle'] ?? 'Our Latest News',
                $content['news']['sectionSubtitle'] ?? 'Stay updated with our latest developments'
            ]);

            // Clear existing news articles
            $pdo->exec("DELETE FROM news_articles");

            $stmt = $pdo->prepare("
                INSERT INTO news_articles (title, publication_date, image_url, excerpt, url, category, author, is_featured, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");

            foreach ($content['news']['articles'] as $index => $article) {
                $stmt->execute([
                    $article['title'],
                    $article['date'] ?? date('Y-m-d'),
                    $article['image'] ?? '',
                    $article['excerpt'],
                    $article['url'] ?? '',
                    $article['category'] ?? 'industry-news',
                    $article['author'] ?? '',
                    $article['featured'] ? 1 : 0,
                    $index + 1
                ]);
            }
        }

        // Save Layout Settings
        if (isset($content['layout'])) {
            $stmt = $pdo->prepare("
                UPDATE layout_settings SET
                    container_width = ?,
                    mobile_breakpoint = ?,
                    tablet_breakpoint = ?
                WHERE id = 1
            ");
            $stmt->execute([
                $content['layout']['containerWidth'],
                $content['layout']['mobileBreakpoint'],
                $content['layout']['tabletBreakpoint']
            ]);

            // Save layout sections
            if (isset($content['layout']['sections'])) {
                $pdo->exec("DELETE FROM layout_sections");

                $stmt = $pdo->prepare("
                    INSERT INTO layout_sections (section_name, section_id, sort_order, is_visible, background_type, background_color, background_image, padding, margin)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                foreach ($content['layout']['sections'] as $index => $section) {
                    $stmt->execute([
                        $section['name'],
                        $section['id'],
                        $index + 1,
                        $section['visible'] ? 1 : 0,
                        $section['backgroundType'],
                        $section['backgroundColor'],
                        $section['backgroundImage'] ?? '',
                        $section['padding'],
                        $section['margin']
                    ]);
                }
            }
        }

        // Save Components
        if (isset($content['components'])) {
            // Clear existing components
            $pdo->exec("DELETE FROM components");

            $stmt = $pdo->prepare("
                INSERT INTO components (name, type, is_enabled, is_configurable, description, settings)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($content['components']['available'] as $component) {
                $stmt->execute([
                    $component['name'],
                    $component['type'],
                    $component['enabled'] ? 1 : 0,
                    $component['configurable'] ? 1 : 0,
                    $component['description'],
                    json_encode($component['settings'])
                ]);
            }

            // Save custom code
            $stmt = $pdo->prepare("
                UPDATE custom_code SET
                    custom_css = ?,
                    custom_js = ?
                WHERE id = 1
            ");
            $stmt->execute([
                $content['components']['customCSS'],
                $content['components']['customJS']
            ]);
        }

        // Save Footer Settings
        if (isset($content['footer'])) {
            $footer = $content['footer'];

            $stmt = $pdo->prepare("
                UPDATE footer_settings SET
                    layout = ?,
                    background_type = ?,
                    background_color = ?,
                    background_image = ?,
                    background_size = ?,
                    gradient_start = ?,
                    gradient_end = ?,
                    text_color = ?
                WHERE id = 1
            ");
            $stmt->execute([
                $footer['layout'],
                $footer['backgroundType'],
                $footer['backgroundColor'],
                $footer['backgroundImage'] ?? '',
                $footer['backgroundSize'] ?? 'cover',
                $footer['gradientStart'],
                $footer['gradientEnd'],
                $footer['textColor']
            ]);

            // Save footer columns
            if (isset($footer['columns'])) {
                $pdo->exec("DELETE FROM footer_columns");

                $stmt = $pdo->prepare("
                    INSERT INTO footer_columns (column_number, title, content_type, content_data)
                    VALUES (?, ?, ?, ?)
                ");

                $columnTypes = ['company_info', 'contact', 'newsletter'];

                for ($i = 1; $i <= 3; $i++) {
                    $columnKey = 'column' . $i;
                    if (isset($footer['columns'][$columnKey])) {
                        $stmt->execute([
                            $i,
                            $footer['columns'][$columnKey]['title'],
                            $columnTypes[$i-1],
                            json_encode($footer['columns'][$columnKey])
                        ]);
                    }
                }
            }

            // Save footer menu
            if (isset($footer['copyright']['menu'])) {
                $pdo->exec("DELETE FROM footer_menu");

                $stmt = $pdo->prepare("
                    INSERT INTO footer_menu (title, url, sort_order, is_active)
                    VALUES (?, ?, ?, 1)
                ");

                foreach ($footer['copyright']['menu'] as $index => $item) {
                    $stmt->execute([
                        $item['title'],
                        $item['url'],
                        $index + 1
                    ]);
                }
            }
        }

        // Save Social Media
        if (isset($content['socialMedia'])) {
            $social = $content['socialMedia'];
            $stmt = $pdo->prepare("
                UPDATE social_media SET
                    facebook = ?,
                    instagram = ?,
                    twitter = ?,
                    linkedin = ?,
                    youtube = ?,
                    googleplus = ?,
                    show_in_header = ?,
                    show_in_footer = ?
                WHERE id = 1
            ");
            $stmt->execute([
                $social['facebook'],
                $social['instagram'],
                $social['twitter'],
                $social['linkedin'],
                $social['youtube'],
                $social['googleplus'],
                $social['showInHeader'] ? 1 : 0,
                $social['showInFooter'] ? 1 : 0
            ]);
        }

        // Save SEO Settings
        if (isset($content['seoSettings'])) {
            $seo = $content['seoSettings'];
            $stmt = $pdo->prepare("
                UPDATE seo_settings_extended SET
                    meta_description = ?,
                    meta_keywords = ?,
                    google_analytics = ?,
                    google_search_console = ?,
                    facebook_pixel = ?,
                    custom_head_code = ?,
                    custom_body_code = ?
                WHERE id = 1
            ");
            $stmt->execute([
                $seo['metaDescription'],
                $seo['metaKeywords'],
                $seo['googleAnalytics'],
                $seo['googleSearchConsole'] ?? '',
                $seo['facebookPixel'] ?? '',
                $seo['customHeadCode'] ?? '',
                $seo['customBodyCode'] ?? ''
            ]);
        }

        $pdo->commit();

        // Auto-regenerate the website with new content
        try {
            $output = shell_exec('cd ' . escapeshellarg(__DIR__ . '/..') . ' && php generate_site.php 2>&1');
            $regenerated = true;
            $regenerationLog = $output;
        } catch (Exception $e) {
            $regenerated = false;
            $regenerationLog = 'Error: ' . $e->getMessage();
        }

        echo json_encode([
            'success' => true,
            'message' => 'Content saved successfully to database',
            'website_regenerated' => $regenerated,
            'regeneration_log' => $regenerationLog
        ]);

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to save content: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Load content from database
    try {
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
                // Use flat structure to match form inputs
                'logoMain' => $siteSettings['logo_main'],
                'logoTransparent' => $siteSettings['logo_transparent'],
                'logoWidth' => $siteSettings['logo_width']
            ];
        }

        // Load navbar and dropdown settings
        $stmt = $pdo->query("SELECT * FROM navbar_settings WHERE id = 1");
        $navbar = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->query("SELECT * FROM dropdown_settings WHERE id = 1");
        $dropdown = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($navbar && $dropdown) {
            $content['siteSettings']['navbar'] = [
                'position' => $navbar['position'],
                'height' => $navbar['height'],
                'backgroundColor' => $navbar['background_color'],
                'transparency' => $navbar['transparency'],
                'textColor' => $navbar['text_color'],
                'hoverColor' => $navbar['hover_color'],
                'dropdown' => [
                    'layoutType' => $dropdown['layout_type'],
                    'backgroundType' => $dropdown['background_type'],
                    'backgroundColor' => $dropdown['background_color'],
                    'gradientStart' => $dropdown['gradient_start'],
                    'gradientEnd' => $dropdown['gradient_end'],
                    'textColor' => $dropdown['text_color'],
                    'hoverTextColor' => $dropdown['hover_text_color'],
                    'borderRadius' => $dropdown['border_radius'],
                    'shadow' => $dropdown['shadow_intensity'],
                    'animation' => $dropdown['animation'],
                    'width' => $dropdown['width'],
                    'fontSize' => $dropdown['font_size'],
                    'lineHeight' => $dropdown['line_height'],
                    'itemPadding' => $dropdown['item_padding'],
                    'borderStyle' => $dropdown['border_style'],
                    'enableMultiLevel' => (bool)$dropdown['enable_multi_level'],
                    'arrowStyle' => $dropdown['arrow_style']
                ]
            ];
        }

        // Load Hero Slides
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

        // Load Services
        try {
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
        } catch (Exception $e) {
            // Services table might not exist, skip silently
        }

        // Load Projects
        try {
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
        } catch (Exception $e) {
            // Projects table might not exist, skip silently
        }

        // Load News Articles (skip if table doesn't exist)
        try {
            $stmt = $pdo->query("SELECT * FROM news WHERE is_active = 1 ORDER BY created_at DESC LIMIT 6");
            $news = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($news) {
                $content['news'] = ['articles' => []];
                foreach ($news as $article) {
                    $content['news']['articles'][] = [
                        'title' => $article['title'],
                        'image' => $article['featured_image'],
                        'excerpt' => $article['excerpt'],
                        'date' => $article['created_at'],
                        'author' => $article['author'] ?? 'Admin',
                        'link' => $article['url']
                    ];
                }
            }
        } catch (Exception $e) {
            // News table might not exist, skip silently
        }

        echo json_encode([
            'success' => true,
            'content' => $content
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Failed to load content: ' . $e->getMessage()
        ]);
    }
}
?>
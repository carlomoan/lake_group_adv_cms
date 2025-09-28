<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Load environment-based database configuration
require_once __DIR__ . '/../config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initialize content structure
    $content = [
        'siteSettings' => [
            'siteTitle' => 'Petroleum and Gas – Gas and Oil WordPress theme',
            'tagline' => 'Professional Energy Solutions',
            'description' => 'Leading provider of oil and gas services',
            'primaryColor' => '#FFD200',
            'secondaryColor' => '#484939',
            'tertiaryColor' => '#1E3A8A',
            'logo' => 'https://via.placeholder.com/150x50/FFD200/333?text=PETROLEUM',
            'faviconUrl' => '',
            'navbar' => [
                'backgroundColor' => '#ffffff',
                'height' => 70,
                'textColor' => '#333333',
                'hoverColor' => '#FFD200'
            ]
        ],
        'navigation' => [
            'mainMenu' => [
                ['title' => 'Home', 'url' => '#home'],
                ['title' => 'About', 'url' => '#about'],
                ['title' => 'Services', 'url' => '#services'],
                ['title' => 'Projects', 'url' => '#projects'],
                ['title' => 'News', 'url' => '#news'],
                ['title' => 'Contact', 'url' => '#contact']
            ]
        ],
        'hero' => [
            'slides' => [
                [
                    'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=1920&h=1080&fit=crop&crop=center',
                    'subtitle' => 'Welcome to Petroleum & Gas',
                    'title' => 'Leading Energy Solutions Provider',
                    'description' => 'Delivering excellence in oil and gas services worldwide',
                    'button1Text' => 'Our Services',
                    'button1Url' => '#services',
                    'button2Text' => 'Contact Us',
                    'button2Url' => '#contact'
                ]
            ]
        ],
        'services' => [
            'items' => [
                [
                    'title' => 'Oil Drilling',
                    'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop',
                    'description' => 'Professional oil drilling services with state-of-the-art equipment.'
                ],
                [
                    'title' => 'Gas Processing',
                    'image' => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=400&h=300&fit=crop',
                    'description' => 'Advanced gas processing and refining solutions.'
                ],
                [
                    'title' => 'Pipeline Services',
                    'image' => 'https://images.unsplash.com/photo-1588072432836-e10032774350?w=400&h=300&fit=crop',
                    'description' => 'Complete pipeline installation and maintenance services.'
                ]
            ]
        ],
        'aboutSection' => [
            'title' => 'About Our Company',
            'description' => 'We are a leading provider of oil and gas services with over 20 years of experience in the industry.',
            'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=600&h=400&fit=crop'
        ],
        'latestProjects' => [
            [
                'title' => 'Offshore Platform Project',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop',
                'link' => '#project1'
            ],
            [
                'title' => 'Pipeline Installation',
                'image' => 'https://images.unsplash.com/photo-1588072432836-e10032774350?w=400&h=300&fit=crop',
                'link' => '#project2'
            ]
        ],
        'latestNews' => [
            [
                'title' => 'New Oil Discovery',
                'date' => '2024-01-15',
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop',
                'excerpt' => 'Major oil discovery in the North Sea region.',
                'link' => '#news1'
            ],
            [
                'title' => 'Sustainable Energy Initiative',
                'date' => '2024-01-10',
                'image' => 'https://images.unsplash.com/photo-1581094794329-c8112a89af12?w=400&h=300&fit=crop',
                'excerpt' => 'Our commitment to sustainable energy solutions.',
                'link' => '#news2'
            ]
        ],
        'footer' => [
            'logo' => 'https://via.placeholder.com/150x50/FFFFFF/333?text=PETROLEUM',
            'description' => 'Petroleum is the leader in the country with efficient and reliable energy solutions.',
            'menu' => [
                ['title' => 'Home', 'url' => '#home'],
                ['title' => 'About', 'url' => '#about'],
                ['title' => 'Services', 'url' => '#services'],
                ['title' => 'Contact', 'url' => '#contact']
            ]
        ]
    ];

    // Load content from database if available
    try {
        // Load site settings
        $stmt = $pdo->query("SELECT * FROM site_settings WHERE id = 1");
        $siteSettings = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($siteSettings) {
            $content['siteSettings']['siteTitle'] = $siteSettings['site_title'] ?: $content['siteSettings']['siteTitle'];
            $content['siteSettings']['tagline'] = $siteSettings['tagline'] ?: $content['siteSettings']['tagline'];
            $content['siteSettings']['primaryColor'] = $siteSettings['primary_color'] ?: $content['siteSettings']['primaryColor'];
            $content['siteSettings']['secondaryColor'] = $siteSettings['secondary_color'] ?: $content['siteSettings']['secondaryColor'];
            $content['siteSettings']['tertiaryColor'] = $siteSettings['tertiary_color'] ?: $content['siteSettings']['tertiaryColor'];
            $content['siteSettings']['faviconUrl'] = $siteSettings['favicon_url'] ?: $content['siteSettings']['faviconUrl'];

            // Update logo if available
            if ($siteSettings['logo_main']) {
                $content['siteSettings']['logo'] = $siteSettings['logo_main'];
            }
        }

        // Load services
        $stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($services) {
            $content['services']['items'] = array_map(function($service) {
                return [
                    'title' => $service['title'],
                    'description' => $service['description'],
                    'image' => $service['image_url'] ?: 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop'
                ];
            }, $services);
        }

        // Load projects
        $stmt = $pdo->query("SELECT * FROM projects WHERE is_active = 1 ORDER BY sort_order LIMIT 6");
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($projects) {
            $content['latestProjects'] = array_map(function($project) {
                return [
                    'title' => $project['title'],
                    'image' => $project['image_url'] ?: 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop',
                    'link' => $project['url'] ?: '#'
                ];
            }, $projects);
        }

        // Load news articles
        $stmt = $pdo->query("SELECT * FROM news_articles WHERE is_active = 1 ORDER BY publication_date DESC LIMIT 6");
        $news = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($news) {
            $content['latestNews'] = array_map(function($article) {
                return [
                    'title' => $article['title'],
                    'date' => $article['publication_date'],
                    'image' => $article['image_url'] ?: 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=300&fit=crop',
                    'excerpt' => $article['excerpt'] ?: '',
                    'link' => $article['url'] ?: '#'
                ];
            }, $news);
        }

        // Load about section
        $stmt = $pdo->query("SELECT * FROM about_section WHERE id = 1");
        $about = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($about) {
            $content['aboutSection']['title'] = $about['title'] ?: $content['aboutSection']['title'];
            $content['aboutSection']['description'] = $about['description'] ?: $content['aboutSection']['description'];
            $content['aboutSection']['image'] = $about['image_url'] ?: $content['aboutSection']['image'];
        }

        // Load hero slides
        $stmt = $pdo->query("SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY slide_order");
        $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($slides) {
            $content['hero']['slides'] = array_map(function($slide) {
                return [
                    'image' => $slide['image_url'] ?: 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=1920&h=1080&fit=crop',
                    'subtitle' => $slide['subtitle'] ?: '',
                    'title' => $slide['title'] ?: '',
                    'description' => $slide['description'] ?: '',
                    'button1Text' => $slide['button1_text'] ?: '',
                    'button1Url' => $slide['button1_url'] ?: '',
                    'button2Text' => $slide['button2_text'] ?: '',
                    'button2Url' => $slide['button2_url'] ?: ''
                ];
            }, $slides);
        }

    } catch (PDOException $e) {
        // If database fails, use default content
        error_log("Database error in content-public.php: " . $e->getMessage());
    }

    // Return the content
    echo json_encode($content, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // If database connection fails completely, return default content
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection failed',
        'content' => $content ?? []
    ]);
}
?>
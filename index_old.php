<?php
// Database Configuration
$db_host = 'localhost';
$db_name = 'petroleum_gas_db';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch site settings
function getSiteOption($pdo, $option_name, $default = '') {
    $stmt = $pdo->prepare("SELECT option_value FROM site_options WHERE option_name = ?");
    $stmt->execute([$option_name]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['option_value'] : $default;
}

// Fetch page content
function getPageContent($pdo, $page_id = 1) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ? AND status = 'published'");
    $stmt->execute([$page_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch menu items
function getMenuItems($pdo, $menu_location = 'primary') {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE menu_location = ? ORDER BY sort_order ASC");
    $stmt->execute([$menu_location]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch products (if e-commerce is needed)
function getFeaturedProducts($pdo, $limit = 8) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE featured = 1 AND status = 'active' LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get site data
$site_title = getSiteOption($pdo, 'site_title', 'Lake Energies');
$site_description = getSiteOption($pdo, 'site_description', 'Petroleum, Gas and Oil Industry Solutions');
$logo_url = getSiteOption($pdo, 'logo_url', 'uploads/Lake-Logos-ALL-36-768x443.png');
$logo_sticky_url = getSiteOption($pdo, 'logo_sticky_url', 'uploads/Lake-Logos-ALL-36-768x443.png');
$page_content = getPageContent($pdo, 1);
$menu_items = getMenuItems($pdo, 'primary');
$featured_products = getFeaturedProducts($pdo, 8);
?>
<!doctype html>
<html class="no-js" lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <title><?php echo htmlspecialchars($site_title . ' - ' . $site_description); ?></title>
    <meta name='robots' content='index, follow' />

    <!-- Custom Styles -->
    <style>
        img:is([sizes="auto" i], [sizes^="auto," i]) {
            contain-intrinsic-size: 3000px 1500px
        }

        /* Reset WordPress emoji styles */
        img.emoji {
            display: inline !important;
            border: none !important;
            box-shadow: none !important;
            height: 1em !important;
            width: 1em !important;
            margin: 0 0.07em !important;
            vertical-align: -0.1em !important;
            background: none !important;
            padding: 0 !important;
        }

        /* Custom button styles */
        .wp-block-button__link {
            color: #fff;
            background-color: #32373c;
            border-radius: 9999px;
            box-shadow: none;
            text-decoration: none;
            padding: calc(.667em + 2px) calc(1.333em + 2px);
            font-size: 1.125em
        }

        /* Custom variables */
        :root {
            --wp--preset--color--primary: #FFD200;
            --wp--preset--color--secondary: #484939;
        }

        /* Logo Styling */
        .logo, .logo-sticky {
            max-height: 45px;
            max-width: 180px;
            height: auto;
            width: auto;
            object-fit: contain;
            vertical-align: middle;
        }

        .logo-sticky {
            max-height: 40px;
        }

        .name {
            overflow: hidden;
        }

        .name h1 {
            margin: 0;
            line-height: 80px;
            height: 80px;
            display: flex;
            align-items: center;
        }

        .name h1 a {
            display: flex;
            align-items: center;
            height: 100%;
        }

        /* Header Styling */
        .l-header {
            background: rgba(52, 152, 219, 0.95);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .top-bar {
            background: transparent;
            height: 80px;
            line-height: 80px;
        }

        .name h1 {
            margin: 0;
            line-height: 80px;
        }

        .logo-fallback {
            color: #fff !important;
        }

        /* Navigation Menu Styling */
        .menu > li > a {
            padding: 0 20px;
            font-weight: 500;
            text-transform: uppercase;
            color: #fff;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .menu > li > a:hover {
            color: var(--wp--preset--color--primary);
            background: rgba(255,255,255,0.1);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('uploads/SSZG7600-1024x768.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            color: white;
            text-align: left;
            margin-top: 80px;
        }

        .hero-overlay {
            width: 100%;
        }

        .hero-content {
            max-width: 600px;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 300;
            line-height: 1.2;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero-title .highlight {
            color: var(--wp--preset--color--primary);
            font-weight: 500;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }

        /* Services Section */
        .services-section {
            background: #f8f9fa;
            padding: 100px 0;
        }

        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 50px;
            margin-top: 50px;
        }

        .service-item {
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
            overflow: hidden;
            position: relative;
            height: 400px;
        }

        .service-item:hover {
            transform: translateY(-10px);
        }

        .service-background-image {
            position: relative;
            height: 100%;
            width: 100%;
        }

        .service-bg-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }

        .service-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.7));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 40px 20px;
            color: white;
        }

        .service-icon {
            font-size: 3rem;
            color: var(--wp--preset--color--primary);
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .service-item h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: white;
            margin-bottom: 15px;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .service-underline {
            width: 60px;
            height: 3px;
            background: var(--wp--preset--color--primary);
            margin: 0 auto 20px;
        }

        .service-item p {
            color: rgba(255,255,255,0.9);
            line-height: 1.6;
            font-size: 14px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        /* Mobile Menu */
        .toggle-topbar {
            display: none;
            color: #fff;
        }

        @media (max-width: 768px) {
            .toggle-topbar {
                display: block;
            }

            .top-bar-section {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(52, 152, 219, 0.95);
                padding: 20px 0;
            }

            .top-bar-section.active {
                display: block;
            }

            .menu {
                flex-direction: column;
            }

            .menu > li {
                width: 100%;
            }

            .menu > li > a {
                display: block;
                padding: 15px 20px;
                border-bottom: 1px solid rgba(255,255,255,0.1);
            }

            .logo, .logo-sticky {
                max-height: 40px;
                max-width: 150px;
            }

            .top-bar {
                height: 60px;
                line-height: 60px;
            }

            .name h1 {
                line-height: 60px;
            }

            .hero-section {
                margin-top: 60px;
                min-height: 70vh;
                text-align: center;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }

            .services-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        /* Icon fallbacks */
        .icon-fallback {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 8px;
        }

        /* Product Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .product-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .product-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-info h3 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }

        .price {
            font-size: 20px;
            font-weight: bold;
            color: var(--wp--preset--color--primary);
            margin: 10px 0;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--wp--preset--color--primary);
            color: #333;
        }

        .btn-primary:hover {
            background: #e6bb00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 210, 0, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: #fff;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.1);
            border-color: var(--wp--preset--color--primary);
            color: var(--wp--preset--color--primary);
        }

        /* Footer */
        .site-footer {
            background: #333;
            color: #fff;
            padding: 40px 0 20px;
            margin-top: 50px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-section h3, .footer-section h4 {
            margin-bottom: 15px;
            color: var(--wp--preset--color--primary);
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li {
            margin-bottom: 8px;
        }

        .footer-section ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: var(--wp--preset--color--primary);
        }

        .social-links {
            display: flex;
            gap: 15px;
        }

        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: var(--wp--preset--color--primary);
            color: #333;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: #e6bb00;
            transform: translateY(-2px);
        }

        .footer-bottom {
            border-top: 1px solid #555;
            padding-top: 20px;
            text-align: center;
            color: #ccc;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Page Content */
        .page-content {
            padding: 50px 0;
        }

        .page-content .container {
            max-width: 800px;
        }
    </style>

    <!-- CDN Icon Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS Files (replace WordPress theme files) -->
    <link rel='stylesheet' href='assets/css/foundation.css' type='text/css' media='all' />
    <link rel='stylesheet' href='assets/css/owl.carousel.css' type='text/css' media='all' />
    <link rel='stylesheet' href='assets/css/owl.theme.css' type='text/css' media='all' />
    <link rel='stylesheet' href='assets/css/animate.css' type='text/css' media='all' />
    <link rel='stylesheet' href='assets/css/swiper.min.css' type='text/css' media='all' />
    <link rel='stylesheet' href='assets/css/main.css' type='text/css' media='all' />

    <!-- Google Fonts -->
    <link href='https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;700&display=swap' rel='stylesheet'>

    <!-- Custom inline styles for backgrounds -->
    <style type="text/css">
        .custom-bg-1 {
            background: url(assets/images/titlebar-bg.jpg) no-repeat #6DD676;
            background-size: cover;
        }
        .custom-bg-2 {
            background: rgb(44, 44, 44) url(assets/images/bg4-3.jpg) no-repeat;
            background-size: cover;
        }
    </style>
</head>

<body class="home page-template-default page theme-petroleum js-enabled">
    <!-- Header -->
    <header class='l-header creative-layout'>
        <div class="top-bar-container contain-to-grid sticky">
            <nav class="top-bar" data-topbar="">
                <ul class="title-area">
                    <li class="name">
                        <h1>
                            <a title="<?php echo htmlspecialchars($site_title); ?>" rel="home" href="/">
                                <img class="logo" alt="<?php echo htmlspecialchars($site_title); ?>" src="<?php echo htmlspecialchars($logo_url); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                                <img class="logo-sticky" alt="<?php echo htmlspecialchars($site_title); ?>" src="<?php echo htmlspecialchars($logo_sticky_url); ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-block';">
                                <span class="logo-fallback" style="display:none; font-size: 24px; font-weight: bold; color: var(--wp--preset--color--primary);">
                                    <i class="fas fa-industry"></i> <?php echo htmlspecialchars($site_title); ?>
                                </span>
                            </a>
                        </h1>
                    </li>
                    <li class="toggle-topbar menu-icon">
                        <a href="#"><span>Menu</span></a>
                    </li>
                </ul>

                <section class="creative top-bar-section right">
                    <div class="menu-container">
                        <ul id="main-menu" class="menu">
                            <?php foreach($menu_items as $item): ?>
                            <li class="menu-item <?php echo $item['has_children'] ? 'has-dropdown' : ''; ?> <?php echo $item['css_class']; ?>">
                                <a href="<?php echo htmlspecialchars($item['url']); ?>" class="has-icon">
                                    <?php
                                    // Add appropriate icons based on menu title
                                    $icon_class = '';
                                    switch(strtolower($item['title'])) {
                                        case 'home':
                                            $icon_class = 'fas fa-home';
                                            break;
                                        case 'about':
                                        case 'about us':
                                            $icon_class = 'fas fa-info-circle';
                                            break;
                                        case 'services':
                                            $icon_class = 'fas fa-cogs';
                                            break;
                                        case 'portfolio':
                                            $icon_class = 'fas fa-briefcase';
                                            break;
                                        case 'contact':
                                            $icon_class = 'fas fa-envelope';
                                            break;
                                        case 'products':
                                            $icon_class = 'fas fa-shopping-bag';
                                            break;
                                        case 'blog':
                                        case 'news':
                                            $icon_class = 'fas fa-newspaper';
                                            break;
                                        default:
                                            $icon_class = 'fas fa-chevron-right';
                                    }
                                    ?>
                                    <i class="<?php echo $icon_class; ?> icon-fallback"></i>
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </a>

                                <?php if($item['has_children']): ?>
                                <ul class="sub-menu dropdown">
                                    <?php
                                    // Get child menu items
                                    $child_stmt = $pdo->prepare("SELECT * FROM menu_items WHERE parent_id = ? ORDER BY sort_order ASC");
                                    $child_stmt->execute([$item['id']]);
                                    $child_items = $child_stmt->fetchAll(PDO::FETCH_ASSOC);
                                    ?>
                                    <?php foreach($child_items as $child): ?>
                                    <li class="menu-item <?php echo $child['css_class']; ?>">
                                        <a href="<?php echo htmlspecialchars($child['url']); ?>" class="has-icon">
                                            <i class="fas fa-angle-right icon-fallback"></i>
                                            <?php echo htmlspecialchars($child['title']); ?>
                                        </a>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </section>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main id="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-overlay">
                <div class="container">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            Overcoming <span class="highlight">technology</span><br>
                            challenges: making<br>
                            the most of resources
                        </h1>
                        <div class="hero-buttons">
                            <a href="#services" class="btn btn-primary">READ MORE</a>
                            <a href="#contact" class="btn btn-secondary">READ MORE</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section class="services-section">
            <div class="container">
                <div class="services-grid">
                    <div class="service-item service-oil-gas">
                        <div class="service-background-image">
                            <img src="uploads/photolubes_8-1-1536x1023.jpg" alt="Oil & Gas Distribution" class="service-bg-img">
                            <div class="service-overlay">
                                <div class="service-icon">
                                    <i class="fas fa-oil-well"></i>
                                </div>
                                <h3>OIL & GAS DISTRIBUTION</h3>
                                <div class="service-underline"></div>
                                <p>End-to-end petroleum solutions with extensive storage and distribution network across East and Central Africa</p>
                            </div>
                        </div>
                    </div>

                    <div class="service-item service-logistics">
                        <div class="service-background-image">
                            <img src="uploads/IXER3494-1024x768.jpg" alt="Logistics & Transport" class="service-bg-img">
                            <div class="service-overlay">
                                <div class="service-icon">
                                    <i class="fas fa-truck"></i>
                                </div>
                                <h3>LOGISTICS & TRANSPORT</h3>
                                <div class="service-underline"></div>
                                <p>Comprehensive transportation, container handling, and supply chain solutions for businesses across the region</p>
                            </div>
                        </div>
                    </div>

                    <div class="service-item service-manufacturing">
                        <div class="service-background-image">
                            <img src="uploads/ORGG4289.jpg" alt="Manufacturing Solutions" class="service-bg-img">
                            <div class="service-overlay">
                                <div class="service-icon">
                                    <i class="fas fa-industry"></i>
                                </div>
                                <h3>MANUFACTURING SOLUTIONS</h3>
                                <div class="service-underline"></div>
                                <p>Steel production, construction materials, and industrial manufacturing serving the growing infrastructure needs</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products Section -->
        <?php if(!empty($featured_products)): ?>
        <section class="featured-products">
            <div class="container">
                <h2><i class="fas fa-star"></i> Featured Products</h2>
                <div class="products-grid">
                    <?php foreach($featured_products as $product): ?>
                    <div class="product-item">
                        <div class="product-image">
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="product-image-fallback" style="display:none; justify-content:center; align-items:center; height:200px; background:#f5f5f5; color:#999;">
                                <i class="fas fa-industry" style="font-size:48px;"></i>
                            </div>
                        </div>
                        <div class="product-info">
                            <h3><i class="fas fa-wrench"></i> <?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price"><i class="fas fa-dollar-sign"></i> $<?php echo number_format($product['price'], 2); ?></p>
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> View Product
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo htmlspecialchars($site_title); ?></h3>
                    <p><?php echo htmlspecialchars($site_description); ?></p>
                </div>
                <div class="footer-section">
                    <h4><i class="fas fa-links"></i> Quick Links</h4>
                    <ul>
                        <?php foreach(array_slice($menu_items, 0, 5) as $item): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($item['url']); ?>">
                                <i class="fas fa-chevron-right"></i> <?php echo htmlspecialchars($item['title']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4><i class="fas fa-share-alt"></i> Follow Us</h4>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_title); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/vendor/jquery.min.js"></script>
    <script src="assets/js/vendor/foundation.min.js"></script>
    <script src="assets/js/vendor/owl.carousel.min.js"></script>
    <script src="assets/js/vendor/modernizr.js"></script>
    <script src="assets/js/vendor/parallax.js"></script>
    <script src="assets/js/vendor/smoothscroll.min.js"></script>
    <script src="assets/js/vendor/swiper.min.js"></script>
    <script src="assets/js/scripts.js"></script>

    <!-- Custom JavaScript -->
    <script>
        // Initialize Foundation
        $(document).foundation();

        // Custom functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const menuToggle = document.querySelector('.toggle-topbar');
            const menu = document.querySelector('.top-bar-section');

            if(menuToggle && menu) {
                menuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    menu.classList.toggle('active');
                });
            }

            // Smooth scroll for anchor links
            const anchorLinks = document.querySelectorAll('a[href^="#"]');
            anchorLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if(target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>

    <!-- Analytics (replace with your tracking code) -->
    <script>
        // Replace with your analytics code
        // Example: Google Analytics
        /*
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-XXXXXXXX-X', 'auto');
        ga('send', 'pageview');
        */
    </script>
</body>
</html>
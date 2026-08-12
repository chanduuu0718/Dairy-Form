<?php
if (!defined('SITE_NAME')) {
    require_once __DIR__ . '/../config/config.php';
}
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$cartCount = getCartCount();
$loggedInUser = Auth::getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title><?= isset($pageTitle) ? $pageTitle . ' | ' . SITE_NAME : SITE_NAME . ' - ' . SITE_TAGLINE ?></title>
    <meta name="description" content="<?= isset($pageDescription) ? $pageDescription : 'PM Dairy - Pure Milk, Pure Life. Farm fresh dairy products delivered daily. A2 Cow Milk, Ghee, Paneer, Dahi & more from Haryana.' ?>">
    <meta name="keywords" content="PM Dairy, farm fresh milk, A2 milk, desi ghee, paneer, dairy farm, Haryana dairy, organic milk">
    <meta name="author" content="PM Dairy">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= isset($pageTitle) ? $pageTitle : SITE_NAME ?>">
    <meta property="og:description" content="<?= isset($pageDescription) ? $pageDescription : SITE_TAGLINE ?>">
    <meta property="og:image" content="<?= ASSETS_URL ?>/images/og-image.jpg">
    <meta property="og:url" content="<?= SITE_URL ?>">
    <meta property="og:type" content="website">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= ASSETS_URL ?>/images/icons/favicon.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">

    <?php if (isset($extraCSS)): ?>
        <?php foreach ($extraCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Schema Markup -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "LocalBusiness",
        "name": "<?= SITE_NAME ?>",
        "description": "<?= SITE_TAGLINE ?>",
        "url": "<?= SITE_URL ?>",
        "telephone": "<?= SITE_PHONE ?>",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Village Khanpur, Tehsil Pehowa",
            "addressLocality": "Kurukshetra",
            "addressRegion": "Haryana",
            "postalCode": "136128",
            "addressCountry": "IN"
        }
    }
    </script>
</head>
<body class="<?= isset($bodyClass) ? $bodyClass : '' ?>">

    <!-- Page Loader -->
    <div id="page-loader" class="page-loader">
        <div class="loader-content">
            <div class="loader-spinner"></div>
            <p>Loading...</p>
        </div>
    </div>

    <!-- Header -->
    <header class="site-header" id="site-header">
        <div class="header-top">
            <div class="container">
                <div class="header-top-content">
                    <div class="header-contact-info">
                        <a href="tel:<?= SITE_PHONE ?>"><i class="fas fa-phone-alt"></i> <?= SITE_PHONE ?></a>
                        <a href="mailto:<?= SITE_EMAIL ?>"><i class="fas fa-envelope"></i> <?= SITE_EMAIL ?></a>
                    </div>
                    <div class="header-social">
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram instagram-icon"></i></a>
                        <a href="https://www.youtube.com/@PurvanchalDairyfarm" aria-label="YouTube"> <i class="fab fa-youtube youtube-icon"></i></a>
                        <a href="#" target="_blank" aria-label="WhatsApp"><i class="fab fa-whatsapp whatsapp-icon"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <nav class="main-nav" id="main-nav">
            <div class="container">
                <div class="nav-content">
                    <!-- Logo -->
                    <a href="<?= SITE_URL ?>/" class="nav-logo">
                        <span class="logo-icon">🐄</span>
                        <div class="logo-text">
                            <span class="logo-name"><?= SITE_NAME ?></span>
                            <span class="logo-tagline">Farm Fresh Daily</span>
                        </div>
                    </a>

                    <!-- Navigation Links -->
                    <ul class="nav-links" id="nav-links">
                        <li><a href="<?= SITE_URL ?>/" class="<?= $currentPage === 'index' ? 'active' : '' ?>">Home</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/about.php" class="<?= $currentPage === 'about' ? 'active' : '' ?>">About Us</a></li>
                        <li class="has-dropdown">
                            <a href="<?= SITE_URL ?>/pages/products.php" class="<?= $currentPage === 'products' ? 'active' : '' ?>">Products <i class="fas fa-chevron-down"></i></a>
                            <ul class="dropdown-menu">
                                <li><a href="<?= SITE_URL ?>/pages/products.php?category=milk">Milk</a></li>
                                <li><a href="<?= SITE_URL ?>/pages/products.php?category=ghee">Ghee</a></li>
                                <li><a href="<?= SITE_URL ?>/pages/products.php?category=paneer">Paneer</a></li>
                                <li><a href="<?= SITE_URL ?>/pages/products.php?category=dahi">Dahi</a></li>
                                <li><a href="<?= SITE_URL ?>/pages/products.php?category=butter">Butter</a></li>
                                <li><a href="<?= SITE_URL ?>/pages/products.php">All Products</a></li>
                            </ul>
                        </li>
                        <li><a href="<?= SITE_URL ?>/pages/cattle.php" class="<?= $currentPage === 'cattle' ? 'active' : '' ?>">Our Cattle</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/gallery.php" class="<?= $currentPage === 'gallery' ? 'active' : '' ?>">Gallery</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/blog.php" class="<?= $currentPage === 'blog' ? 'active' : '' ?>">Blog</a></li>
                        <li><a href="<?= SITE_URL ?>/pages/contact.php" class="<?= $currentPage === 'contact' ? 'active' : '' ?>">Contact</a></li>
                    </ul>

                    <!-- Nav Actions -->
                    <div class="nav-actions">
                        <!-- Dark Mode Toggle -->
                        <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode">
                            <i class="fas fa-moon"></i>
                        </button>

                        <!-- Cart -->
                        <a href="<?= SITE_URL ?>/pages/cart.php" class="nav-cart" id="nav-cart">
                            <i class="fas fa-shopping-bag"></i>
                            <span class="cart-count" id="cart-count"><?= $cartCount ?></span>
                        </a>

                        <!-- User -->
                        <?php if ($loggedInUser): ?>
                            <div class="nav-user has-dropdown">
                                <button class="nav-user-btn">
                                    <i class="fas fa-user-circle"></i>
                                    <span class="user-name-short"><?= explode(' ', $loggedInUser['name'])[0] ?></span>
                                </button>
                                <ul class="dropdown-menu dropdown-right">
                                    <li><a href="<?= SITE_URL ?>/pages/profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                                    <li><a href="<?= SITE_URL ?>/pages/my-orders.php"><i class="fas fa-box"></i> My Orders</a></li>
                                    <li><a href="<?= SITE_URL ?>/pages/my-subscriptions.php"><i class="fas fa-sync"></i> Subscriptions</a></li>
                                    <?php if ($loggedInUser['role'] === 'admin'): ?>
                                        <li><a href="<?= SITE_URL ?>/admin/"><i class="fas fa-tachometer-alt"></i> Admin Panel</a></li>
                                    <?php endif; ?>
                                    <li class="divider"></li>
                                    <li><a href="#" id="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                                </ul>
                            </div>
                        <?php else: ?>
                            <a href="<?= SITE_URL ?>/pages/login.php" class="btn btn-sm btn-primary nav-login-btn">
                                <i class="fas fa-user"></i> Login
                            </a>
                        <?php endif; ?>

                        <!-- Mobile Menu Toggle -->
                        <button class="hamburger" id="hamburger" aria-label="Toggle menu">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobile-overlay"></div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Main Content Wrapper -->
    <main class="main-content">

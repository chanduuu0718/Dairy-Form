<?php
require_once __DIR__ . '/../../config/config.php';

if (!Auth::isAdmin()) {
    header('Location: ' . SITE_URL . '/pages/login.php');
    exit;
}

$adminUser = Auth::getUser();
$adminPage = basename($_SERVER['PHP_SELF'], '.php');

// Quick stats
$db = Database::getInstance();
// Orders badge: count orders placed AFTER the last time admin opened the Orders page.
// Uses max order ID stored in session — more reliable than datetime comparisons.
$lastSeenId = (int)($_SESSION['orders_last_seen_id'] ?? 0);
$todayOrders = $db->fetchOne(
    "SELECT COUNT(*) as c, COALESCE(SUM(total_amount),0) as t FROM orders WHERE id > ?",
    [$lastSeenId], 'i'
);
$totalCustomers = $db->fetchOne("SELECT COUNT(*) as c FROM users WHERE role = 'customer'");
$activeSubs = $db->fetchOne("SELECT COUNT(*) as c FROM subscriptions WHERE status = 'active'");
$unreadContacts = $db->fetchOne("SELECT COUNT(*) as c FROM contacts WHERE is_read = 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($adminTitle) ? $adminTitle . ' | ' : '' ?>Admin - <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
</head>
<body class="admin-body">

    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="admin-sidebar-header">
            <a href="<?= SITE_URL ?>/admin/" class="admin-logo">
                <span>🐄</span>
                <span>Purvanchal Dairyfarm</span>
            </a>
            <button class="sidebar-toggle" id="sidebar-toggle"><i class="fas fa-bars"></i></button>
        </div>

        <nav class="admin-nav">
            <a href="<?= SITE_URL ?>/admin/" class="<?= $adminPage === 'index' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/pages/orders.php" class="<?= $adminPage === 'orders' ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i><span>Orders</span>
                <?php if ($adminPage !== 'orders' && $todayOrders['c'] > 0): ?><span class="nav-badge"><?= $todayOrders['c'] ?></span><?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>/admin/pages/products.php" class="<?= $adminPage === 'products' ? 'active' : '' ?>">
                <i class="fas fa-box"></i><span>Products</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/pages/subscriptions.php" class="<?= $adminPage === 'subscriptions' ? 'active' : '' ?>">
                <i class="fas fa-sync"></i><span>Subscriptions</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/pages/blogs.php" class="<?= $adminPage === 'blogs' ? 'active' : '' ?>">
                <i class="fas fa-newspaper"></i><span>Blog Posts</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/pages/farm-visits.php" class="<?= $adminPage === 'farm-visits' ? 'active' : '' ?>">
                <i class="fas fa-tractor"></i><span>Farm Visits</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/pages/testimonials.php" class="<?= $adminPage === 'testimonials' ? 'active' : '' ?>">
                <i class="fas fa-star"></i><span>Testimonials</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/pages/users.php" class="<?= $adminPage === 'users' ? 'active' : '' ?>">
                <i class="fas fa-users"></i><span>Customers</span>
            </a>
            <a href="<?= SITE_URL ?>/admin/pages/contacts.php" class="<?= $adminPage === 'contacts' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i><span>Messages</span>
                <?php if ($unreadContacts['c'] > 0): ?><span class="nav-badge"><?= $unreadContacts['c'] ?></span><?php endif; ?>
            </a>
            <div class="nav-divider"></div>
            <a href="<?= SITE_URL ?>/" target="_blank">
                <i class="fas fa-external-link-alt"></i><span>View Website</span>
            </a>
            <a href="#" onclick="PMAuth.logout()">
                <i class="fas fa-sign-out-alt"></i><span>Logout</span>
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <header class="admin-header">
            <button class="mobile-sidebar-toggle" id="mobile-sidebar-toggle"><i class="fas fa-bars"></i></button>
            <div class="admin-header-right">
                <span class="admin-welcome">Welcome, <?= htmlspecialchars($adminUser['name']) ?></span>
                <div class="admin-user-avatar"><?= strtoupper(substr($adminUser['name'], 0, 1)) ?></div>
            </div>
        </header>

        <div class="admin-content">

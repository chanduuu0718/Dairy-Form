<?php
/**
 * PM Dairy Farm configuration template.
 * Copy this file to config/config.php and replace every placeholder with
 * environment-specific values. Never commit config/config.php.
 */

// Database
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'pm_dairy');

// Application
define('SITE_URL', getenv('SITE_URL') ?: 'https://example.com');
define('SITE_EMAIL', getenv('SITE_EMAIL') ?: 'info@example.com');
define('SITE_PHONE', getenv('SITE_PHONE') ?: '+91-0000000000');
define('SITE_WHATSAPP', getenv('SITE_WHATSAPP') ?: '910000000000');
define('SITE_NAME', getenv('SITE_NAME') ?: 'PM Dairy Farm');

// Authentication: generate a long random value for production.
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'CHANGE_ME_TO_A_LONG_RANDOM_SECRET');
define('JWT_EXPIRY', (int)(getenv('JWT_EXPIRY') ?: 86400));

// Pagination and uploads
define('ITEMS_PER_PAGE', 20);
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('ROOT_PATH', dirname(__DIR__) . '/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('UPLOADS_URL', rtrim(SITE_URL, '/') . '/uploads');

// Cashfree Payments. Keep these values out of source control.
define('CASHFREE_MODE', getenv('CASHFREE_MODE') ?: 'TEST');
define('CASHFREE_APP_ID', getenv('CASHFREE_APP_ID') ?: '');
define('CASHFREE_SECRET_KEY', getenv('CASHFREE_SECRET_KEY') ?: '');
define(
    'CASHFREE_API_URL',
    CASHFREE_MODE === 'TEST' ? 'https://sandbox.cashfree.com/pg' : 'https://api.cashfree.com/pg'
);

// Load shared application code.
require_once ROOT_PATH . 'config/database.php';
require_once ROOT_PATH . 'includes/functions.php';
require_once ROOT_PATH . 'includes/auth.php';

// Use secure session cookies whenever the application is served over HTTPS.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

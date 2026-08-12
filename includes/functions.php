<?php
/**
 * Helper Functions
 * PM Dairy Farm
 */

// JSON Response
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Sanitize input
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Generate slug
function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

// Generate order number
function generateOrderNumber() {
    return 'PMD' . date('Ymd') . strtoupper(substr(uniqid(), -5));
}

// Generate OTP
function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Format price
function formatPrice($price) {
    return '₹' . number_format($price, 2);
}

// Get time ago
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

// Upload image
function uploadImage($file, $directory = 'products', $maxWidth = 800) {
    $uploadDir = UPLOADS_PATH . $directory . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'message' => 'Invalid image type. Allowed: JPG, PNG, WebP, GIF'];
    }

    if ($file['size'] > MAX_IMAGE_SIZE) {
        return ['success' => false, 'message' => 'Image too large. Max size: 5MB'];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '_' . time() . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Resize if needed
        resizeImage($filepath, $maxWidth);
        return [
            'success' => true,
            'filename' => $filename,
            'path' => '/uploads/' . $directory . '/' . $filename,
            'url' => UPLOADS_URL . '/' . $directory . '/' . $filename
        ];
    }

    return ['success' => false, 'message' => 'Failed to upload image'];
}

// Resize image
function resizeImage($filepath, $maxWidth = 800) {
    $info = getimagesize($filepath);
    if (!$info) return;

    list($origW, $origH) = $info;
    if ($origW <= $maxWidth) return;

    $ratio = $maxWidth / $origW;
    $newW = $maxWidth;
    $newH = intval($origH * $ratio);

    switch ($info['mime']) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($filepath);
            break;
        case 'image/png':
            $source = imagecreatefrompng($filepath);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($filepath);
            break;
        default:
            return;
    }

    $dest = imagecreatetruecolor($newW, $newH);

    // Preserve transparency for PNG
    if ($info['mime'] === 'image/png') {
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
    }

    imagecopyresampled($dest, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

    switch ($info['mime']) {
        case 'image/jpeg':
            imagejpeg($dest, $filepath, 85);
            break;
        case 'image/png':
            imagepng($dest, $filepath, 8);
            break;
        case 'image/webp':
            imagewebp($dest, $filepath, 85);
            break;
    }

    imagedestroy($source);
    imagedestroy($dest);
}

// Delete uploaded image
function deleteImage($path) {
    $fullPath = ROOT_PATH . ltrim($path, '/');
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

// Validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone
function isValidPhone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

// Validate required fields
function validateRequired($fields, $data) {
    $errors = [];
    foreach ($fields as $field => $label) {
        if (empty($data[$field])) {
            $errors[] = "$label is required";
        }
    }
    return $errors;
}

// Get pagination data
function getPagination($totalItems, $currentPage, $perPage = ITEMS_PER_PAGE) {
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total' => $totalItems,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

// Get setting value
function getSetting($key) {
    $db = Database::getInstance();
    $row = $db->fetchOne("SELECT setting_value FROM site_settings WHERE setting_key = ?", [$key], 's');
    return $row ? $row['setting_value'] : null;
}

// Get cart count
function getCartCount() {
    $db = Database::getInstance();
    $sessionId = session_id();
    if (isset($_SESSION['user_id'])) {
        $row = $db->fetchOne(
            "SELECT COALESCE(SUM(quantity), 0) as count FROM cart_items WHERE user_id = ? OR (session_id = ? AND user_id IS NULL)",
            [$_SESSION['user_id'], $sessionId], 'is'
        );
    } else {
        $row = $db->fetchOne("SELECT COALESCE(SUM(quantity), 0) as count FROM cart_items WHERE session_id = ?", [$sessionId], 's');
    }
    return $row ? (int)$row['count'] : 0;
}

// Truncate text
function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

// Get category name
function getCategoryName($slug) {
    $categories = [
        'milk' => 'Milk',
        'ghee' => 'Ghee',
        'paneer' => 'Paneer',
        'dahi' => 'Dahi',
        'chhach' => 'Chhach',
        'cream' => 'Cream',
        'khoya' => 'Khoya',
        'butter' => 'Butter'
    ];
    return $categories[$slug] ?? $slug;
}

// Get blog category name
function getBlogCategoryName($slug) {
    $categories = [
        'health-tips' => 'Health Tips',
        'farm-updates' => 'Farm Updates',
        'dairy-knowledge' => 'Dairy Knowledge',
        'recipes' => 'Recipes'
    ];
    return $categories[$slug] ?? $slug;
}

// Get order status badge
function getOrderStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'confirmed' => '<span class="badge badge-info">Confirmed</span>',
        'processing' => '<span class="badge badge-primary">Processing</span>',
        'out_for_delivery' => '<span class="badge badge-accent">Out for Delivery</span>',
        'delivered' => '<span class="badge badge-success">Delivered</span>',
        'cancelled' => '<span class="badge badge-danger">Cancelled</span>'
    ];
    return $badges[$status] ?? '<span class="badge">' . ucfirst($status) . '</span>';
}

// CSRF Token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting (simple file-based)
function checkRateLimit($identifier, $maxAttempts = 60, $timeWindow = 60) {
    $file = sys_get_temp_dir() . '/rate_' . md5($identifier) . '.json';
    $now = time();
    $data = [];

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?: [];
    }

    // Remove old entries
    $data = array_filter($data, function($timestamp) use ($now, $timeWindow) {
        return ($now - $timestamp) < $timeWindow;
    });

    if (count($data) >= $maxAttempts) {
        return false;
    }

    $data[] = $now;
    file_put_contents($file, json_encode($data));
    return true;
}

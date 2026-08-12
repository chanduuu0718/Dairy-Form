<?php
/**
 * API: Products
 * GET /api/products/ - List all products
 * GET /api/products/?category=milk - Filter by category
 * GET /api/products/?search=ghee - Search products
 * GET /api/products/?featured=1 - Featured products
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$db = Database::getInstance();

$category = sanitize($_GET['category'] ?? '');
$search = sanitize($_GET['search'] ?? '');
$featured = intval($_GET['featured'] ?? 0);
$sort = sanitize($_GET['sort'] ?? 'newest');
$minPrice = floatval($_GET['min_price'] ?? 0);
$maxPrice = floatval($_GET['max_price'] ?? 0);
$page = max(1, intval($_GET['page'] ?? 1));
$limit = intval($_GET['limit'] ?? ITEMS_PER_PAGE);

$where = ["p.is_available = 1"];
$params = [];
$types = '';

if (!empty($category)) {
    $where[] = "c.slug = ?";
    $params[] = $category;
    $types .= 's';
}

if (!empty($search)) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'sss';
}

if ($featured) {
    $where[] = "p.is_featured = 1";
}

if ($minPrice > 0) {
    $where[] = "p.price >= ?";
    $params[] = $minPrice;
    $types .= 'd';
}

if ($maxPrice > 0) {
    $where[] = "p.price <= ?";
    $params[] = $maxPrice;
    $types .= 'd';
}

$whereClause = implode(' AND ', $where);

// Count total
$countRow = $db->fetchOne(
    "SELECT COUNT(*) as total FROM products p JOIN categories c ON p.category_id = c.id WHERE $whereClause",
    $params, $types
);
$total = $countRow['total'];
$pagination = getPagination($total, $page, $limit);

// Sort
$orderBy = match($sort) {
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name' => 'p.name ASC',
    'popular' => 'p.is_featured DESC, p.id DESC',
    default => 'p.created_at DESC'
};

// Fetch products
$offset = $pagination['offset'];
$products = $db->fetchAll(
    "SELECT p.*, c.name as category_name, c.slug as category_slug
     FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE $whereClause
     ORDER BY $orderBy
     LIMIT $limit OFFSET $offset",
    $params, $types
);

// Parse JSON images
foreach ($products as &$product) {
    $product['images'] = json_decode($product['images'] ?? '[]', true) ?: [];
    $product['nutrition_info'] = json_decode($product['nutrition_info'] ?? '{}', true) ?: [];
}

jsonResponse([
    'success' => true,
    'products' => $products,
    'pagination' => $pagination
]);

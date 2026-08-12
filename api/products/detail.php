<?php
/**
 * API: Product Detail
 * GET /api/products/detail.php?slug=farm-fresh-cow-milk
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$slug = sanitize($_GET['slug'] ?? '');
if (empty($slug)) {
    jsonResponse(['success' => false, 'message' => 'Product slug is required'], 400);
}

$db = Database::getInstance();

$product = $db->fetchOne(
    "SELECT p.*, c.name as category_name, c.slug as category_slug
     FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE p.slug = ? AND p.is_available = 1",
    [$slug], 's'
);

if (!$product) {
    jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
}

$product['images'] = json_decode($product['images'] ?? '[]', true) ?: [];
$product['nutrition_info'] = json_decode($product['nutrition_info'] ?? '{}', true) ?: [];

// Related products
$related = $db->fetchAll(
    "SELECT p.*, c.name as category_name FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE p.category_id = ? AND p.id != ? AND p.is_available = 1
     ORDER BY RAND() LIMIT 4",
    [$product['category_id'], $product['id']], 'ii'
);

foreach ($related as &$r) {
    $r['images'] = json_decode($r['images'] ?? '[]', true) ?: [];
}

jsonResponse([
    'success' => true,
    'product' => $product,
    'related' => $related
]);

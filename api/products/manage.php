<?php
/**
 * API: Manage Products (Admin)
 * POST   /api/products/manage.php - Create product
 * PUT    /api/products/manage.php?id=1 - Update product
 * DELETE /api/products/manage.php?id=1 - Delete product
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$admin = Auth::requireAdmin();
$db = Database::getInstance();
// $method = $_SERVER['REQUEST_METHOD'];
$method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $description = $_POST['description'] ?? '';
    $shortDesc = sanitize($_POST['short_description'] ?? '');
    $nutritionInfo = $_POST['nutrition_info'] ?? '{}';
    $price = floatval($_POST['price'] ?? 0);
    $comparePrice = floatval($_POST['compare_price'] ?? 0) ?: null;
    $unit = sanitize($_POST['unit'] ?? 'kg');
    $weight = sanitize($_POST['weight'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    $isFeatured = intval($_POST['is_featured'] ?? 0);
    $metaTitle = sanitize($_POST['meta_title'] ?? '');
    $metaDesc = sanitize($_POST['meta_description'] ?? '');

    if (empty($name) || $categoryId <= 0 || $price <= 0) {
        jsonResponse(['success' => false, 'message' => 'Name, category, and price are required'], 422);
    }

    $slug = generateSlug($name);
    // Ensure unique slug
    $existing = $db->fetchOne("SELECT id FROM products WHERE slug = ?", [$slug], 's');
    if ($existing) $slug .= '-' . time();

    // Handle image uploads
    $images = [];
    // Handle image upload on update
    if (!empty($_FILES['images'])) {

        $files = $_FILES['images'];

        for ($i = 0; $i < min(count($files['name']), 5); $i++) {

            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                if ($files['size'][$i] > $MAX_IMAGE_SIZE) {
                    jsonResponse([ 'success' => false, 'message' => 'Each image must be less than 5MB' ], 422);
                }

                $file = [
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'size' => $files['size'][$i],
                    'error' => $files['error'][$i]
                ];

                $result = uploadImage($file, 'products');

                if ($result['success']) {
                    $images[] = $result['path'];
                }
            }
        }
    }

    $imagesJson = json_encode($images);
// ===== END IMAGE UPDATE =====

    $imagesJson = json_encode($images);

    $productId = $db->insert(
        "INSERT INTO products (category_id, name, slug, description, short_description, nutrition_info, price, compare_price, unit, weight, stock, images, is_featured, meta_title, meta_description)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$categoryId, $name, $slug, $description, $shortDesc, $nutritionInfo, $price, $comparePrice, $unit, $weight, $stock, $imagesJson, $isFeatured, $metaTitle, $metaDesc],
        'isssssddssissss'
    );

    jsonResponse(['success' => true, 'message' => 'Product created', 'product_id' => $productId], 201);
}

if ($method === 'PUT') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);

    // parse_str(file_get_contents('php://input'), $data);
    $data = $_POST;

    $fields = [];
    $params = [];
    $types = '';

    $allowedFields = ['name', 'description', 'short_description', 'nutrition_info', 'unit', 'weight', 'meta_title', 'meta_description'];
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $params[] = $data[$field];
            $types .= 's';
        }
    }

    if (isset($data['category_id'])) {
        $fields[] = "category_id = ?";
        $params[] = intval($data['category_id']);
        $types .= 'i';
    }
    if (isset($data['price'])) {
        $fields[] = "price = ?";
        $params[] = floatval($data['price']);
        $types .= 'd';
    }
    if (isset($data['compare_price'])) {
        $fields[] = "compare_price = ?";
        $params[] = floatval($data['compare_price']) ?: null;
        $types .= 'd';
    }
    if (isset($data['stock'])) {
        $fields[] = "stock = ?";
        $params[] = intval($data['stock']);
        $types .= 'i';
    }
    if (isset($data['is_featured'])) {
        $fields[] = "is_featured = ?";
        $params[] = intval($data['is_featured']);
        $types .= 'i';
    }
    if (isset($data['is_available'])) {
        $fields[] = "is_available = ?";
        $params[] = intval($data['is_available']);
        $types .= 'i';
    }

    // ===== IMAGE UPDATE SUPPORT =====
    if (!empty($_FILES['images']) && $_FILES['images']['name'][0] != '') {

        $existingProduct = $db->fetchOne(
            "SELECT images FROM products WHERE id = ?",
            [$id],
            'i'
        );

        if ($existingProduct) {
            $oldImages = json_decode($existingProduct['images'] ?? '[]', true) ?: [];
            foreach ($oldImages as $img) {
                deleteImage($img);
            }
        }

        $images = [];
        $files = $_FILES['images'];

        for ($i = 0; $i < min(count($files['name']), 5); $i++) {

            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                if ($files['size'][$i] > $MAX_IMAGE_SIZE) {
                    jsonResponse([ 'success' => false,  'message' => 'Each image must be less than 5MB' ], 422);
                }

                $file = [
                    'name' => $files['name'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'size' => $files['size'][$i],
                    'error' => $files['error'][$i]
                ];

                $result = uploadImage($file, 'products');

                if ($result['success']) {
                    $images[] = $result['path'];
                }
            }
        }

        if (!empty($images)) {
            $fields[] = "images = ?";
            $params[] = json_encode($images);
            $types   .= 's';
        }
    }
    // ===== END IMAGE UPDATE =====

    if (empty($fields)) {
        jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
    }

    $params[] = $id;
    $types .= 'i';

    $db->update("UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?", $params, $types);
    jsonResponse(['success' => true, 'message' => 'Product updated']);
}

if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);

    // Delete product images
    $product = $db->fetchOne("SELECT images FROM products WHERE id = ?", [$id], 'i');
    if ($product) {
        $images = json_decode($product['images'] ?? '[]', true) ?: [];
        foreach ($images as $img) {
            deleteImage($img);
        }
    }

    $db->update("DELETE FROM products WHERE id = ?", [$id], 'i');
    jsonResponse(['success' => true, 'message' => 'Product deleted']);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

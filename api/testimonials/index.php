<?php
/**
 * API: Testimonials
 * GET  /api/testimonials/ - Approved testimonials
 * POST /api/testimonials/ - Admin: add/manage testimonials
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $all = intval($_GET['all'] ?? 0);

    if ($all && Auth::isAdmin()) {
        $testimonials = $db->fetchAll("SELECT * FROM testimonials ORDER BY created_at DESC");
    } else {
        $testimonials = $db->fetchAll("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC");
    }

    jsonResponse(['success' => true, 'testimonials' => $testimonials]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin = Auth::requireAdmin();
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $action = sanitize($_GET['action'] ?? 'create');

    if ($action === 'approve') {
        $id = intval($data['id'] ?? 0);
        $db->update("UPDATE testimonials SET is_approved = 1 WHERE id = ?", [$id], 'i');
        jsonResponse(['success' => true, 'message' => 'Testimonial approved']);
    }

    if ($action === 'reject') {
        $id = intval($data['id'] ?? 0);
        $db->update("UPDATE testimonials SET is_approved = 0 WHERE id = ?", [$id], 'i');
        jsonResponse(['success' => true, 'message' => 'Testimonial rejected']);
    }

    if ($action === 'delete') {
        $id = intval($data['id'] ?? 0);
        $db->update("DELETE FROM testimonials WHERE id = ?", [$id], 'i');
        jsonResponse(['success' => true, 'message' => 'Testimonial deleted']);
    }

    // Create
    $customerName = sanitize($data['customer_name'] ?? '');
    $customerLocation = sanitize($data['customer_location'] ?? '');
    $rating = max(1, min(5, intval($data['rating'] ?? 5)));
    $reviewText = sanitize($data['review_text'] ?? '');

    if (empty($customerName) || empty($reviewText)) {
        jsonResponse(['success' => false, 'message' => 'Name and review are required'], 422);
    }

    $photo = null;
    if (!empty($_FILES['customer_photo']) && $_FILES['customer_photo']['error'] === UPLOAD_ERR_OK) {
        $result = uploadImage($_FILES['customer_photo'], 'testimonials', 200);
        if ($result['success']) $photo = $result['path'];
    }

    $db->insert(
        "INSERT INTO testimonials (customer_name, customer_photo, customer_location, rating, review_text, is_approved) VALUES (?, ?, ?, ?, ?, 1)",
        [$customerName, $photo, $customerLocation, $rating, $reviewText],
        'sssis'
    );

    jsonResponse(['success' => true, 'message' => 'Testimonial added'], 201);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

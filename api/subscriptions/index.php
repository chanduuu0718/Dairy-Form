<?php
/**
 * API: Subscriptions
 * POST /api/subscriptions/ - Create subscription
 * GET  /api/subscriptions/ - My subscriptions / Admin: all
 * POST /api/subscriptions/?action=update - Update subscription (pause/resume/cancel)
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = Auth::requireAuth();
    $page = max(1, intval($_GET['page'] ?? 1));

    if ($user['role'] === 'admin') {
        $subs = $db->fetchAll(
            "SELECT s.*, u.name as user_name, u.phone as user_phone
             FROM subscriptions s JOIN users u ON s.user_id = u.id
             ORDER BY s.created_at DESC LIMIT 20 OFFSET " . (($page - 1) * 20)
        );
    } else {
        $subs = $db->fetchAll(
            "SELECT * FROM subscriptions WHERE user_id = ? ORDER BY created_at DESC",
            [$user['id']], 'i'
        );
    }

    foreach ($subs as &$sub) {
        $sub['items'] = $db->fetchAll(
            "SELECT si.*, p.name, p.price, p.unit, p.images FROM subscription_items si
             JOIN products p ON si.product_id = p.id WHERE si.subscription_id = ?",
            [$sub['id']], 'i'
        );
        foreach ($sub['items'] as &$item) {
            $item['images'] = json_decode($item['images'] ?? '[]', true) ?: [];
        }
    }

    jsonResponse(['success' => true, 'subscriptions' => $subs]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = Auth::requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $action = sanitize($_GET['action'] ?? '');

    // Update subscription
    if ($action === 'update') {
        $subId = intval($data['subscription_id'] ?? 0);
        $newStatus = sanitize($data['status'] ?? '');

        $validStatuses = ['active', 'paused', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            jsonResponse(['success' => false, 'message' => 'Invalid status'], 400);
        }

        $isActive = $newStatus === 'active' ? 1 : 0;
        $db->update(
            "UPDATE subscriptions SET status = ?, is_active = ? WHERE id = ? AND (user_id = ? OR ? = 'admin')",
            [$newStatus, $isActive, $subId, $user['id'], $user['role']],
            'siiss'
        );

        jsonResponse(['success' => true, 'message' => 'Subscription updated']);
    }

    // Create subscription
    $planType = sanitize($data['plan_type'] ?? '');
    $deliveryTime = sanitize($data['delivery_time'] ?? 'morning');
    $products = $data['products'] ?? [];

    if (!in_array($planType, ['daily', 'weekly', 'monthly'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid plan type'], 400);
    }

    if (empty($products)) {
        jsonResponse(['success' => false, 'message' => 'Select at least one product'], 400);
    }

    $startDate = date('Y-m-d', strtotime('+1 day'));
    $endDate = match($planType) {
        'daily' => date('Y-m-d', strtotime('+30 days')),
        'weekly' => date('Y-m-d', strtotime('+4 weeks')),
        'monthly' => date('Y-m-d', strtotime('+1 month')),
    };

    // Calculate total price
    $totalPrice = 0;
    foreach ($products as $prod) {
        $product = $db->fetchOne("SELECT price FROM products WHERE id = ?", [$prod['product_id']], 'i');
        if ($product) {
            $totalPrice += $product['price'] * ($prod['quantity'] ?? 1);
        }
    }

    // Adjust for plan duration
    $multiplier = match($planType) {
        'daily' => 30,
        'weekly' => 4,
        'monthly' => 1,
    };
    $totalPrice *= $multiplier;

    $db->beginTransaction();
    try {
        $subId = $db->insert(
            "INSERT INTO subscriptions (user_id, plan_type, start_date, end_date, delivery_time, total_price)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$user['id'], $planType, $startDate, $endDate, $deliveryTime, $totalPrice],
            'issssd'
        );

        foreach ($products as $prod) {
            $db->insert(
                "INSERT INTO subscription_items (subscription_id, product_id, quantity) VALUES (?, ?, ?)",
                [$subId, intval($prod['product_id']), intval($prod['quantity'] ?? 1)],
                'iii'
            );
        }

        $db->commit();
        jsonResponse(['success' => true, 'message' => 'Subscription created!', 'subscription_id' => $subId], 201);
    } catch (Exception $e) {
        $db->rollback();
        jsonResponse(['success' => false, 'message' => 'Failed to create subscription'], 500);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

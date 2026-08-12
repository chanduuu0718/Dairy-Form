<?php
/**
 * API: Orders
 * POST /api/orders/ - Place order
 * GET  /api/orders/ - My orders / Admin: all orders
 * GET  /api/orders/?id=1 - Order detail
 * POST /api/orders/?action=update-status - Admin: update status
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user = Auth::requireAuth();
    $orderId = intval($_GET['id'] ?? 0);
    if ($orderId > 0) {
        $order = $db->fetchOne(
            "SELECT o.*, u.name as user_name, u.phone as user_phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ? AND (o.user_id = ? OR ? = 'admin')",
            [$orderId, $user['id'], $user['role']], 'iis'
        );
        if (!$order) jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
        $order['items'] = $db->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$orderId], 'i');
        jsonResponse(['success' => true, 'order' => $order]);
    }

    $page = max(1, intval($_GET['page'] ?? 1));
    $status = sanitize($_GET['status'] ?? '');
    $limit = ITEMS_PER_PAGE;
    if ($user['role'] === 'admin') {
        $where = "1=1";
        $params = [];
        $types = '';
        if ($status !== '') { $where .= " AND o.order_status = ?"; $params[] = $status; $types .= 's'; }
        $countRow = $db->fetchOne("SELECT COUNT(*) as total FROM orders o WHERE $where", $params, $types);
        $pagination = getPagination($countRow['total'], $page, $limit);
        $orders = $db->fetchAll(
            "SELECT o.*, u.name as user_name, u.phone as user_phone FROM orders o JOIN users u ON o.user_id = u.id WHERE $where ORDER BY o.created_at DESC LIMIT $limit OFFSET {$pagination['offset']}",
            $params, $types
        );
    } else {
        $countRow = $db->fetchOne("SELECT COUNT(*) as total FROM orders WHERE user_id = ?", [$user['id']], 'i');
        $pagination = getPagination($countRow['total'], $page, $limit);
        $orders = $db->fetchAll("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit OFFSET {$pagination['offset']}", [$user['id']], 'i');
    }
    foreach ($orders as &$order) {
        $order['items'] = $db->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$order['id']], 'i');
    }
    jsonResponse(['success' => true, 'orders' => $orders, 'pagination' => $pagination]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;
    $action = sanitize($_GET['action'] ?? '');

    if ($action === 'update-status') {
        Auth::requireAdmin();
        $orderId = intval($data['order_id'] ?? 0);
        $newStatus = sanitize($data['status'] ?? '');
        $validStatuses = ['pending', 'confirmed', 'processing', 'out_for_delivery', 'delivered', 'cancelled'];
        if (!in_array($newStatus, $validStatuses, true)) jsonResponse(['success' => false, 'message' => 'Invalid status'], 400);
        $db->update("UPDATE orders SET order_status = ? WHERE id = ?", [$newStatus, $orderId], 'si');
        if ($newStatus === 'delivered') {
            $db->update("UPDATE orders SET payment_status = 'paid' WHERE id = ? AND payment_method = 'cod'", [$orderId], 'i');
        }
        jsonResponse(['success' => true, 'message' => 'Order status updated']);
    }

    $user = Auth::requireAuth();
    $deliveryAddress = sanitize($data['delivery_address'] ?? '');
    $deliveryDate = sanitize($data['delivery_date'] ?? '');
    $deliveryTimeSlot = sanitize($data['delivery_time_slot'] ?? '');
    $paymentMethod = sanitize($data['payment_method'] ?? 'cod');
    $notes = sanitize($data['notes'] ?? '');

    if ($deliveryAddress === '') jsonResponse(['success' => false, 'message' => 'Delivery address is required'], 422);
    if (!in_array($paymentMethod, ['cod', 'online'], true)) jsonResponse(['success' => false, 'message' => 'Invalid payment method'], 422);

    $sessionId = session_id();
    $cartItems = $db->fetchAll(
        "SELECT ci.*, p.name, p.price, p.stock, p.is_available FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.user_id = ? OR (ci.session_id = ? AND ci.user_id IS NULL)",
        [$user['id'], $sessionId], 'is'
    );
    if (empty($cartItems)) jsonResponse(['success' => false, 'message' => 'Your cart is empty'], 400);

    $subtotal = 0;
    foreach ($cartItems as $item) {
        $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000]]);
        if ($quantity === false) jsonResponse(['success' => false, 'message' => 'Invalid quantity for ' . $item['name']], 422);
        if (!(int)$item['is_available']) jsonResponse(['success' => false, 'message' => $item['name'] . ' is no longer available'], 400);
        if ((int)$item['stock'] >= 0 && $quantity > (int)$item['stock']) {
            jsonResponse(['success' => false, 'message' => $item['name'] . ' has only ' . (int)$item['stock'] . ' units available.'], 409);
        }
        $subtotal += (float)$item['price'] * $quantity;
    }

    $deliveryCharge = max(0, (float)(getSetting('delivery_charge') ?? 0));
    $totalAmount = $subtotal + $deliveryCharge;
    $orderNumber = generateOrderNumber();

    $db->beginTransaction();
    try {
        // Reserve/deduct stock atomically. Stock -1 means unlimited inventory.
        // If another customer wins the race first, this update affects zero rows and the transaction aborts.
        foreach ($cartItems as $item) {
            $quantity = (int)$item['quantity'];
            $affected = $db->update(
                "UPDATE products SET stock = CASE WHEN stock >= 0 THEN stock - ? ELSE stock END WHERE id = ? AND is_available = 1 AND (stock = -1 OR stock >= ?)",
                [$quantity, $item['product_id'], $quantity], 'iii'
            );
            if ($affected !== 1) {
                throw new RuntimeException('INSUFFICIENT_STOCK:' . $item['name']);
            }
        }

        $orderId = $db->insert(
            "INSERT INTO orders (order_number, user_id, subtotal, delivery_charge, total_amount, delivery_address, delivery_date, delivery_time_slot, payment_method, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$orderNumber, $user['id'], $subtotal, $deliveryCharge, $totalAmount, $deliveryAddress, $deliveryDate ?: null, $deliveryTimeSlot, $paymentMethod, $notes],
            'sidddsssss'
        );

        foreach ($cartItems as $item) {
            $quantity = (int)$item['quantity'];
            $lineTotal = (float)$item['price'] * $quantity;
            $db->insert(
                "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)",
                [$orderId, $item['product_id'], $item['name'], $quantity, $item['price'], $lineTotal], 'iisidd'
            );
        }

        if ($paymentMethod === 'cod') {
            $db->update("DELETE FROM cart_items WHERE user_id = ? OR (session_id = ? AND user_id IS NULL)", [$user['id'], $sessionId], 'is');
        }

        $db->commit();
        jsonResponse([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order' => ['id' => $orderId, 'order_number' => $orderNumber, 'total_amount' => $totalAmount, 'payment_method' => $paymentMethod]
        ], 201);
    } catch (Throwable $e) {
        $db->rollback();
        if (str_starts_with($e->getMessage(), 'INSUFFICIENT_STOCK:')) {
            jsonResponse(['success' => false, 'message' => 'Sorry, one of the products sold out while you were checking out. Please review your cart.'], 409);
        }
        error_log('Order creation failed: ' . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Failed to place order. Please try again.'], 500);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

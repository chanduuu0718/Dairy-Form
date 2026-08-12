<?php
/**
 * API: Cart Operations
 * GET    /api/cart/ - Get cart items
 * POST   /api/cart/?action=add - Add to cart (authenticated customers)
 * POST   /api/cart/?action=update - Update quantity
 * POST   /api/cart/?action=remove - Remove item
 * POST   /api/cart/?action=clear - Clear cart
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$userId = Auth::getCurrentUserId();
$sessionId = session_id();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($userId) {
        $items = $db->fetchAll(
            "SELECT ci.id, ci.quantity, p.id as product_id, p.name, p.slug, p.price, p.compare_price, p.unit, p.weight, p.images, p.is_available, p.stock
             FROM cart_items ci JOIN products p ON ci.product_id = p.id
             WHERE ci.user_id = ? OR (ci.session_id = ? AND ci.user_id IS NULL)
             ORDER BY ci.created_at DESC",
            [$userId, $sessionId], 'is'
        );
    } else {
        $items = $db->fetchAll(
            "SELECT ci.id, ci.quantity, p.id as product_id, p.name, p.slug, p.price, p.compare_price, p.unit, p.weight, p.images, p.is_available, p.stock
             FROM cart_items ci JOIN products p ON ci.product_id = p.id
             WHERE ci.session_id = ? ORDER BY ci.created_at DESC",
            [$sessionId], 's'
        );
    }

    $subtotal = 0;
    foreach ($items as &$item) {
        $item['images'] = json_decode($item['images'] ?? '[]', true) ?: [];
        $item['line_total'] = (float)$item['price'] * (int)$item['quantity'];
        $subtotal += $item['line_total'];
    }

    $deliveryCharge = max(0, (float)(getSetting('delivery_charge') ?? 0));
    $minOrder = (float)(getSetting('min_order_amount') ?? 100);

    jsonResponse([
        'success' => true,
        'items' => $items,
        'count' => array_sum(array_column($items, 'quantity')),
        'subtotal' => $subtotal,
        'delivery_charge' => $deliveryCharge,
        'total' => $subtotal + $deliveryCharge,
        'min_order' => $minOrder
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $action = sanitize($_GET['action'] ?? $data['action'] ?? '');
    $productId = intval($data['product_id'] ?? 0);
    $requestedQuantity = filter_var($data['quantity'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 1000]]);

    switch ($action) {
        case 'add':
            if (!$userId) {
                jsonResponse([
                    'success' => false,
                    'auth_required' => true,
                    'message' => 'Please login or sign up to buy this product.'
                ], 401);
            }
            if ($productId <= 0 || $requestedQuantity === false) {
                jsonResponse(['success' => false, 'message' => 'Valid product and quantity are required'], 422);
            }

            $product = $db->fetchOne(
                "SELECT id, name, price, stock FROM products WHERE id = ? AND is_available = 1",
                [$productId], 'i'
            );
            if (!$product) {
                jsonResponse(['success' => false, 'message' => 'Product not available'], 404);
            }

            $existing = $db->fetchOne(
                "SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?",
                [$userId, $productId], 'ii'
            );

            $newQty = $existing ? ((int)$existing['quantity'] + $requestedQuantity) : $requestedQuantity;
            if ((int)$product['stock'] >= 0 && $newQty > (int)$product['stock']) {
                jsonResponse(['success' => false, 'message' => 'Only ' . (int)$product['stock'] . ' units are available.'], 409);
            }

            if ($existing) {
                $db->update("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?", [$newQty, $existing['id'], $userId], 'iii');
            } else {
                $db->insert("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)", [$userId, $productId, $requestedQuantity], 'iii');
            }

            jsonResponse([
                'success' => true,
                'message' => $product['name'] . ' added to cart!',
                'cart_count' => getCartCount()
            ]);
            break;

        case 'update':
            if (!$userId) {
                jsonResponse(['success' => false, 'auth_required' => true, 'message' => 'Please login to manage your cart.'], 401);
            }
            $cartItemId = intval($data['cart_item_id'] ?? 0);
            $quantity = filter_var($data['quantity'] ?? 0, FILTER_VALIDATE_INT);
            if ($cartItemId <= 0 || $quantity === false) {
                jsonResponse(['success' => false, 'message' => 'Invalid cart item or quantity'], 422);
            }

            $item = $db->fetchOne(
                "SELECT ci.id, ci.product_id, p.stock FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.id = ? AND ci.user_id = ?",
                [$cartItemId, $userId], 'ii'
            );
            if (!$item) {
                jsonResponse(['success' => false, 'message' => 'Cart item not found'], 404);
            }
            if ($quantity > 0 && (int)$item['stock'] >= 0 && $quantity > (int)$item['stock']) {
                jsonResponse(['success' => false, 'message' => 'Only ' . (int)$item['stock'] . ' units are available.'], 409);
            }

            if ($quantity <= 0) {
                $db->update("DELETE FROM cart_items WHERE id = ? AND user_id = ?", [$cartItemId, $userId], 'ii');
            } else {
                $db->update("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?", [$quantity, $cartItemId, $userId], 'iii');
            }
            jsonResponse(['success' => true, 'message' => 'Cart updated', 'cart_count' => getCartCount()]);
            break;

        case 'remove':
            if (!$userId) {
                jsonResponse(['success' => false, 'auth_required' => true, 'message' => 'Please login to manage your cart.'], 401);
            }
            if ($productId > 0) {
                $db->update("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?", [$userId, $productId], 'ii');
            }
            jsonResponse(['success' => true, 'message' => 'Item removed', 'cart_count' => getCartCount()]);
            break;

        case 'clear':
            if (!$userId) {
                jsonResponse(['success' => false, 'auth_required' => true, 'message' => 'Please login to manage your cart.'], 401);
            }
            $db->update("DELETE FROM cart_items WHERE user_id = ?", [$userId], 'i');
            jsonResponse(['success' => true, 'message' => 'Cart cleared', 'cart_count' => 0]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

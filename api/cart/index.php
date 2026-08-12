<?php
/**
 * API: Cart Operations
 * GET    /api/cart/ - Get cart items
 * POST   /api/cart/?action=add - Add to cart
 * POST   /api/cart/?action=update - Update quantity
 * POST   /api/cart/?action=remove - Remove item
 * POST   /api/cart/?action=clear - Clear cart
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$userId = Auth::getCurrentUserId();
$sessionId = session_id();

// GET: Fetch cart items
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($userId) {
        // Also pick up any session-based items not yet merged
        $items = $db->fetchAll(
            "SELECT ci.id, ci.quantity, p.id as product_id, p.name, p.slug, p.price, p.compare_price, p.unit, p.weight, p.images, p.is_available, p.stock
             FROM cart_items ci
             JOIN products p ON ci.product_id = p.id
             WHERE ci.user_id = ? OR (ci.session_id = ? AND ci.user_id IS NULL)
             ORDER BY ci.created_at DESC",
            [$userId, $sessionId], 'is'
        );
    } else {
        $items = $db->fetchAll(
            "SELECT ci.id, ci.quantity, p.id as product_id, p.name, p.slug, p.price, p.compare_price, p.unit, p.weight, p.images, p.is_available, p.stock
             FROM cart_items ci
             JOIN products p ON ci.product_id = p.id
             WHERE ci.session_id = ?
             ORDER BY ci.created_at DESC",
            [$sessionId], 's'
        );
    }

    $subtotal = 0;
    foreach ($items as &$item) {
        $item['images'] = json_decode($item['images'] ?? '[]', true) ?: [];
        $item['line_total'] = $item['price'] * $item['quantity'];
        $subtotal += $item['line_total'];
    }

    $deliveryCharge = floatval(getSetting('delivery_charge') ?? 0);
    $minOrder = floatval(getSetting('min_order_amount') ?? 100);

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

// POST: Cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $action = sanitize($_GET['action'] ?? $data['action'] ?? '');
    $productId = intval($data['product_id'] ?? 0);
    $quantity = max(1, intval($data['quantity'] ?? 1));

    switch ($action) {
        case 'add':
            if ($productId <= 0) {
                jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
            }

            // Check product exists and available
            $product = $db->fetchOne("SELECT id, name, price, stock FROM products WHERE id = ? AND is_available = 1", [$productId], 'i');
            if (!$product) {
                jsonResponse(['success' => false, 'message' => 'Product not available'], 404);
            }

            // Check if already in cart
            if ($userId) {
                $existing = $db->fetchOne("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?", [$userId, $productId], 'ii');
            } else {
                $existing = $db->fetchOne("SELECT id, quantity FROM cart_items WHERE session_id = ? AND product_id = ?", [$sessionId, $productId], 'si');
            }

            if ($existing) {
                $newQty = $existing['quantity'] + $quantity;
                $db->update("UPDATE cart_items SET quantity = ? WHERE id = ?", [$newQty, $existing['id']], 'ii');
            } else {
                if ($userId) {
                    $db->insert("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)", [$userId, $productId, $quantity], 'iii');
                } else {
                    $db->insert("INSERT INTO cart_items (session_id, product_id, quantity) VALUES (?, ?, ?)", [$sessionId, $productId, $quantity], 'sii');
                }
            }

            // Get updated count
            $cartCount = getCartCount();
            jsonResponse(['success' => true, 'message' => $product['name'] . ' added to cart!', 'cart_count' => $cartCount]);
            break;

        case 'update':
            $cartItemId = intval($data['cart_item_id'] ?? 0);
            if ($cartItemId <= 0) {
                jsonResponse(['success' => false, 'message' => 'Cart item ID required'], 400);
            }

            if ($quantity <= 0) {
                // Remove item
                $db->update("DELETE FROM cart_items WHERE id = ?", [$cartItemId], 'i');
            } else {
                $db->update("UPDATE cart_items SET quantity = ? WHERE id = ?", [$quantity, $cartItemId], 'ii');
            }

            jsonResponse(['success' => true, 'message' => 'Cart updated', 'cart_count' => getCartCount()]);
            break;

        case 'remove':
            if ($productId > 0) {
                if ($userId) {
                    $db->update("DELETE FROM cart_items WHERE (user_id = ? OR (session_id = ? AND user_id IS NULL)) AND product_id = ?", [$userId, $sessionId, $productId], 'isi');
                } else {
                    $db->update("DELETE FROM cart_items WHERE session_id = ? AND product_id = ?", [$sessionId, $productId], 'si');
                }
            }
            jsonResponse(['success' => true, 'message' => 'Item removed', 'cart_count' => getCartCount()]);
            break;

        case 'clear':
            if ($userId) {
                $db->update("DELETE FROM cart_items WHERE user_id = ? OR (session_id = ? AND user_id IS NULL)", [$userId, $sessionId], 'is');
            } else {
                $db->update("DELETE FROM cart_items WHERE session_id = ?", [$sessionId], 's');
            }
            jsonResponse(['success' => true, 'message' => 'Cart cleared', 'cart_count' => 0]);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

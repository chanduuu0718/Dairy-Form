<?php
/**
 * API: Payment (Cashfree)
 * POST /api/payment/?action=create-order  - Create Cashfree order & payment session
 * POST /api/payment/?action=verify        - Verify payment status
 * POST /api/payment/?action=webhook       - Cashfree webhook callback
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$db = Database::getInstance();
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;
$action = sanitize($_GET['action'] ?? '');

// Webhook doesn't need user auth
if ($action !== 'webhook') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
    $user = Auth::requireAuth();
}

/**
 * Cashfree API helper
 */
function cashfreeRequest($endpoint, $payload = null, $method = 'POST') {
    $url = CASHFREE_API_URL . $endpoint;
    $headers = [
        'Content-Type: application/json',
        'x-api-version: 2023-08-01',
        'x-client-id: ' . CASHFREE_APP_ID,
        'x-client-secret: ' . CASHFREE_SECRET_KEY
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if ($method === 'POST' && $payload) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    } elseif ($method === 'GET') {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['error' => true, 'message' => $curlErr, 'http_code' => 0];
    }

    $decoded = json_decode($response, true) ?: [];
    $decoded['http_code'] = $httpCode;
    return $decoded;
}

// ── Create Cashfree Order ──────────────────────────────────
if ($action === 'create-order') {
    $orderId = intval($data['order_id'] ?? 0);
    $order = $db->fetchOne("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $user['id']], 'ii');
    if (!$order) {
        jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
    }

    $cfOrderId = 'ORD_' . $order['order_number'] . '_' . time();

    $payload = [
        'order_id'       => $cfOrderId,
        'order_amount'   => floatval(number_format($order['total_amount'], 2, '.', '')),
        'order_currency' => 'INR',
        'customer_details' => [
            'customer_id'    => 'CUST_' . $user['id'],
            'customer_name'  => $user['name'] ?: 'Customer',
            'customer_email' => $user['email'] ?: 'customer@pmdairy.com',
            'customer_phone' => $user['phone'] ?: '9999999999'
        ],
        'order_meta' => [
            'return_url' => SITE_URL . '/pages/payment-return.php?order_id=' . $orderId . '&cf_order_id={order_id}',
            'notify_url' => SITE_URL . '/api/payment/?action=webhook'
        ],
        'order_note' => 'PM Dairy Order #' . $order['order_number']
    ];

    $res = cashfreeRequest('/orders', $payload);

    if (isset($res['http_code']) && in_array($res['http_code'], [200, 201])) {
        $db->update(
            "UPDATE orders SET cf_order_id = ?, payment_session_id = ? WHERE id = ?",
            [$cfOrderId, $res['payment_session_id'], $orderId],
            'ssi'
        );

        jsonResponse([
            'success'            => true,
            'cf_order_id'        => $cfOrderId,
            'payment_session_id' => $res['payment_session_id'],
            'order_amount'       => $payload['order_amount'],
            'order_number'       => $order['order_number'],
            'environment'        => CASHFREE_MODE === 'TEST' ? 'sandbox' : 'production'
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => $res['message'] ?? 'Failed to create payment order',
            'debug'   => $res
        ], 200); // Temporarily using 200 so the frontend success block can parse the debug info.
    }
}

// ── Verify Payment ─────────────────────────────────────────
if ($action === 'verify') {
    $orderId   = intval($data['order_id'] ?? 0);
    $cfOrderId = sanitize($data['cf_order_id'] ?? '');

    if (empty($cfOrderId) || $orderId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Order details missing'], 400);
    }

    $order = $db->fetchOne("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $user['id']], 'ii');
    if (!$order) {
        jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
    }

    $res = cashfreeRequest('/orders/' . $cfOrderId, null, 'GET');

    if (!$res || !isset($res['order_status'])) {
        jsonResponse(['success' => false, 'message' => 'Unable to verify payment'], 500);
    }

    if ($res['order_status'] === 'PAID') {
        // Fetch payment ID
        $payments = cashfreeRequest('/orders/' . $cfOrderId . '/payments', null, 'GET');
        $paymentId = '';
        if (is_array($payments)) {
            foreach ($payments as $p) {
                if (isset($p['payment_status']) && $p['payment_status'] === 'SUCCESS') {
                    $paymentId = $p['cf_payment_id'] ?? '';
                    break;
                }
            }
        }

        $db->update(
            "UPDATE orders SET payment_status = 'paid', cf_payment_id = ?, order_status = 'confirmed' WHERE id = ?",
            [$paymentId, $orderId],
            'si'
        );

        // Clear user's cart
        $sessionId = session_id();
        $db->update("DELETE FROM cart_items WHERE user_id = ? OR (session_id = ? AND user_id IS NULL)", [$user['id'], $sessionId], 'is');

        jsonResponse(['success' => true, 'message' => 'Payment verified!', 'payment_status' => 'paid']);

    } elseif ($res['order_status'] === 'ACTIVE') {
        jsonResponse(['success' => false, 'message' => 'Payment still processing...', 'payment_status' => 'pending']);
    } else {
        $db->update("UPDATE orders SET payment_status = 'failed' WHERE id = ?", [$orderId], 'i');
        jsonResponse(['success' => false, 'message' => 'Payment failed or cancelled', 'payment_status' => 'failed']);
    }
}

// ── Webhook ────────────────────────────────────────────────
if ($action === 'webhook') {
    $rawInput = file_get_contents('php://input');
    $webhookData = json_decode($rawInput, true);

    // Signature verification
    $timestamp = $_SERVER['HTTP_X_CASHFREE_TIMESTAMP'] ?? '';
    $signature = $_SERVER['HTTP_X_CASHFREE_SIGNATURE'] ?? '';

    if ($signature && $timestamp) {
        $expected = base64_encode(hash_hmac('sha256', $timestamp . $rawInput, CASHFREE_SECRET_KEY, true));
        if (!hash_equals($expected, $signature)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid signature']);
            exit;
        }
    }

    if ($webhookData && isset($webhookData['data']['order']['order_id'])) {
        $cfOrderId   = $webhookData['data']['order']['order_id'];
        $payStatus   = $webhookData['data']['payment']['payment_status'] ?? '';
        $cfPaymentId = $webhookData['data']['payment']['cf_payment_id'] ?? '';

        $order = $db->fetchOne("SELECT id, user_id FROM orders WHERE cf_order_id = ?", [$cfOrderId], 's');

        if ($order) {
            if ($payStatus === 'SUCCESS') {
                $db->update(
                    "UPDATE orders SET payment_status = 'paid', cf_payment_id = ?, order_status = 'confirmed' WHERE id = ?",
                    [$cfPaymentId, $order['id']], 'si'
                );
                
                // Clear user's cart (webhook doesn't have session_id, so only clear by user_id)
                $db->update("DELETE FROM cart_items WHERE user_id = ?", [$order['user_id']], 'i');
            } elseif (in_array($payStatus, ['FAILED', 'CANCELLED', 'VOID'])) {
                $db->update("UPDATE orders SET payment_status = 'failed' WHERE id = ?", [$order['id']], 'i');
            }
        }
    }

    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);

function log_cf_debug($msg, $data) {
    file_put_contents(__DIR__ . "/cf_debug.log", date("Y-m-d H:i:s") . " - " . $msg . ": " . print_r($data, true) . "\n", FILE_APPEND);
}


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

// Webhook is authenticated by Cashfree's signature instead of a user token.
if ($action !== 'webhook') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
    $user = Auth::requireAuth();
}

function cashfreeRequest($endpoint, $payload = null, $method = 'POST') {
    $url = CASHFREE_API_URL . $endpoint;
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
        'x-api-version: 2023-08-01',
        'x-client-id: ' . CASHFREE_APP_ID,
        'x-client-secret: ' . CASHFREE_SECRET_KEY
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    if ($method === 'POST' && $payload !== null) {
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
        error_log('Cashfree request failed: ' . $curlErr);
        return ['error' => true, 'http_code' => 0];
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return ['error' => true, 'http_code' => $httpCode];
    }

    $decoded['http_code'] = $httpCode;
    return $decoded;
}

function getRequestHeader($names) {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    foreach ($names as $wanted) {
        foreach ($headers as $name => $value) {
            if (strtolower($name) === strtolower($wanted)) {
                return trim($value);
            }
        }
    }
    return '';
}

function verifyCashfreeWebhookSignature($rawInput) {
    $timestamp = getRequestHeader(['x-webhook-timestamp', 'x-cashfree-timestamp']);
    $signature = getRequestHeader(['x-webhook-signature', 'x-cashfree-signature']);

    if ($timestamp === '' || $signature === '') {
        return false;
    }

    // Cashfree signs timestamp + raw request body with the webhook secret.
    $expected = base64_encode(hash_hmac('sha256', $timestamp . $rawInput, CASHFREE_SECRET_KEY, true));
    return hash_equals($expected, $signature);
}

// ── Create Cashfree Order ──────────────────────────────────
if ($action === 'create-order') {
    $orderId = intval($data['order_id'] ?? 0);
    $order = $db->fetchOne(
        "SELECT * FROM orders WHERE id = ? AND user_id = ? AND payment_method = 'online'",
        [$orderId, $user['id']],
        'ii'
    );
    if (!$order) {
        jsonResponse(['success' => false, 'message' => 'Online payment order not found'], 404);
    }

    if ($order['payment_status'] === 'paid') {
        jsonResponse(['success' => false, 'message' => 'Order is already paid'], 409);
    }

    // Reuse an existing payment session rather than creating duplicate gateway orders.
    if (!empty($order['cf_order_id']) && !empty($order['payment_session_id'])) {
        jsonResponse([
            'success' => true,
            'cf_order_id' => $order['cf_order_id'],
            'payment_session_id' => $order['payment_session_id'],
            'order_amount' => (float)$order['total_amount'],
            'order_number' => $order['order_number'],
            'environment' => CASHFREE_MODE === 'TEST' ? 'sandbox' : 'production'
        ]);
    }

    $cfOrderId = 'ORD_' . $order['order_number'] . '_' . time();
    $amount = round((float)$order['total_amount'], 2);

    $payload = [
        'order_id' => $cfOrderId,
        'order_amount' => $amount,
        'order_currency' => 'INR',
        'customer_details' => [
            'customer_id' => 'CUST_' . $user['id'],
            'customer_name' => $user['name'] ?: 'Customer',
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

    if (isset($res['http_code']) && in_array($res['http_code'], [200, 201], true) && !empty($res['payment_session_id'])) {
        $db->update(
            "UPDATE orders SET cf_order_id = ?, payment_session_id = ? WHERE id = ? AND payment_status = 'pending'",
            [$cfOrderId, $res['payment_session_id'], $orderId],
            'ssi'
        );

        jsonResponse([
            'success' => true,
            'cf_order_id' => $cfOrderId,
            'payment_session_id' => $res['payment_session_id'],
            'order_amount' => $amount,
            'order_number' => $order['order_number'],
            'environment' => CASHFREE_MODE === 'TEST' ? 'sandbox' : 'production'
        ]);
    }

    error_log('Cashfree create-order failed for order ' . $orderId . ' with HTTP ' . ($res['http_code'] ?? 0));
    jsonResponse(['success' => false, 'message' => 'Unable to start online payment. Please try again.'], 502);
}

// ── Verify Payment ─────────────────────────────────────────
if ($action === 'verify') {
    $orderId = intval($data['order_id'] ?? 0);
    $cfOrderId = sanitize($data['cf_order_id'] ?? '');

    if (empty($cfOrderId) || $orderId <= 0) {
        jsonResponse(['success' => false, 'message' => 'Order details missing'], 400);
    }

    $order = $db->fetchOne(
        "SELECT * FROM orders WHERE id = ? AND user_id = ? AND payment_method = 'online'",
        [$orderId, $user['id']],
        'ii'
    );
    if (!$order) {
        jsonResponse(['success' => false, 'message' => 'Order not found'], 404);
    }

    if (empty($order['cf_order_id']) || !hash_equals((string)$order['cf_order_id'], (string)$cfOrderId)) {
        jsonResponse(['success' => false, 'message' => 'Payment order mismatch'], 400);
    }

    $res = cashfreeRequest('/orders/' . rawurlencode($cfOrderId), null, 'GET');

    if (!$res || !isset($res['order_status'], $res['order_amount'])) {
        jsonResponse(['success' => false, 'message' => 'Unable to verify payment'], 502);
    }

    $expectedAmount = round((float)$order['total_amount'], 2);
    $gatewayOrderAmount = round((float)$res['order_amount'], 2);
    if ($gatewayOrderAmount !== $expectedAmount) {
        error_log('Cashfree amount mismatch for order ' . $orderId);
        jsonResponse(['success' => false, 'message' => 'Payment amount verification failed'], 409);
    }

    if ($res['order_status'] === 'PAID') {
        $payments = cashfreeRequest('/orders/' . rawurlencode($cfOrderId) . '/payments', null, 'GET');
        $paymentId = '';
        $paymentAmountValid = false;

        if (is_array($payments)) {
            foreach ($payments as $p) {
                if (isset($p['payment_status']) && $p['payment_status'] === 'SUCCESS') {
                    $paymentId = (string)($p['cf_payment_id'] ?? '');
                    if (isset($p['payment_amount'])) {
                        $paymentAmountValid = round((float)$p['payment_amount'], 2) === $expectedAmount;
                    }
                    break;
                }
            }
        }

        if ($paymentId === '' || !$paymentAmountValid) {
            jsonResponse(['success' => false, 'message' => 'Payment transaction verification failed'], 409);
        }

        $db->beginTransaction();
        try {
            $db->update(
                "UPDATE orders SET payment_status = 'paid', cf_payment_id = ?, order_status = 'confirmed' WHERE id = ? AND payment_status = 'pending'",
                [$paymentId, $orderId],
                'si'
            );

            $sessionId = session_id();
            $db->update(
                "DELETE FROM cart_items WHERE user_id = ? OR (session_id = ? AND user_id IS NULL)",
                [$user['id'], $sessionId],
                'is'
            );
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            error_log('Payment finalization failed for order ' . $orderId . ': ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Payment was received but order finalization failed. Please contact support.'], 500);
        }

        jsonResponse(['success' => true, 'message' => 'Payment verified!', 'payment_status' => 'paid']);
    }

    if ($res['order_status'] === 'ACTIVE') {
        jsonResponse(['success' => false, 'message' => 'Payment still processing...', 'payment_status' => 'pending']);
    }

    $db->update(
        "UPDATE orders SET payment_status = 'failed' WHERE id = ? AND payment_status = 'pending'",
        [$orderId],
        'i'
    );
    jsonResponse(['success' => false, 'message' => 'Payment failed or cancelled', 'payment_status' => 'failed']);
}

// ── Webhook ────────────────────────────────────────────────
if ($action === 'webhook') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    $rawInput = file_get_contents('php://input');
    if (!verifyCashfreeWebhookSignature($rawInput)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }

    $webhookData = json_decode($rawInput, true);

    if ($webhookData && isset($webhookData['data']['order']['order_id'])) {
        $cfOrderId = sanitize($webhookData['data']['order']['order_id']);
        $payStatus = sanitize($webhookData['data']['payment']['payment_status'] ?? '');
        $cfPaymentId = sanitize($webhookData['data']['payment']['cf_payment_id'] ?? '');
        $paymentAmount = isset($webhookData['data']['payment']['payment_amount'])
            ? round((float)$webhookData['data']['payment']['payment_amount'], 2)
            : null;

        $order = $db->fetchOne("SELECT id, user_id, total_amount, payment_status FROM orders WHERE cf_order_id = ?", [$cfOrderId], 's');

        if ($order) {
            $expectedAmount = round((float)$order['total_amount'], 2);

            if ($payStatus === 'SUCCESS' && $paymentAmount === $expectedAmount && $cfPaymentId !== '') {
                // Idempotent: a repeated success webhook does not duplicate state changes.
                $db->update(
                    "UPDATE orders SET payment_status = 'paid', cf_payment_id = ?, order_status = 'confirmed' WHERE id = ? AND payment_status <> 'paid'",
                    [$cfPaymentId, $order['id']],
                    'si'
                );
                $db->update("DELETE FROM cart_items WHERE user_id = ?", [$order['user_id']], 'i');
            } elseif (in_array($payStatus, ['FAILED', 'CANCELLED', 'VOID'], true)) {
                $db->update(
                    "UPDATE orders SET payment_status = 'failed' WHERE id = ? AND payment_status = 'pending'",
                    [$order['id']],
                    'i'
                );
            }
        }
    }

    http_response_code(200);
    echo json_encode(['status' => 'ok']);
    exit;
}

jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);

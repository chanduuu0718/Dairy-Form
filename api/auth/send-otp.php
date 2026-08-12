<?php
/**
 * API: Send OTP
 * POST /api/auth/send-otp.php
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$phone = sanitize($data['phone'] ?? '');

if (!isValidPhone($phone)) {
    jsonResponse(['success' => false, 'message' => 'Valid 10-digit phone required'], 422);
}

if (!checkRateLimit('otp_' . $phone, 3, 300)) {
    jsonResponse(['success' => false, 'message' => 'Too many OTP requests. Wait 5 minutes.'], 429);
}

$db = Database::getInstance();
$user = $db->fetchOne("SELECT id FROM users WHERE phone = ?", [$phone], 's');

if (!$user) {
    jsonResponse(['success' => false, 'message' => 'Phone number not registered'], 404);
}

$otp = generateOTP();
$expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));

$db->update(
    "UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE phone = ?",
    [$otp, $expiresAt, $phone],
    'sss'
);

// In production, send OTP via SMS gateway (MSG91, Twilio, etc.)
// For development, we return it in response
jsonResponse([
    'success' => true,
    'message' => 'OTP sent to your phone',
    'otp_debug' => $otp // Remove in production
]);

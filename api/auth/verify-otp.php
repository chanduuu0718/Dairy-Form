<?php
/**
 * API: Verify OTP & Login
 * POST /api/auth/verify-otp.php
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$phone = sanitize($data['phone'] ?? '');
$otp = sanitize($data['otp'] ?? '');

if (!isValidPhone($phone) || !preg_match('/^\d{6}$/', $otp)) {
    jsonResponse(['success' => false, 'message' => 'Phone and a valid 6-digit OTP are required'], 422);
}

if (!checkRateLimit('otp_verify_' . $phone, 5, 600)) {
    jsonResponse(['success' => false, 'message' => 'Too many verification attempts. Please request a new OTP later.'], 429);
}

$db = Database::getInstance();
$user = $db->fetchOne(
    "SELECT id, name, phone, email, role, otp_code, otp_expires_at FROM users WHERE phone = ?",
    [$phone], 's'
);

if (!$user || empty($user['otp_code']) || empty($user['otp_expires_at'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid or expired OTP'], 401);
}

if (strtotime($user['otp_expires_at']) < time()) {
    $db->update("UPDATE users SET otp_code = NULL, otp_expires_at = NULL WHERE id = ?", [$user['id']], 'i');
    jsonResponse(['success' => false, 'message' => 'OTP has expired'], 401);
}

if (!hash_equals((string)$user['otp_code'], (string)$otp)) {
    jsonResponse(['success' => false, 'message' => 'Invalid OTP'], 401);
}

$db->update("UPDATE users SET otp_code = NULL, otp_expires_at = NULL, is_verified = 1 WHERE id = ?", [$user['id']], 'i');

$token = Auth::loginUser($user['id'], $user['role']);

jsonResponse([
    'success' => true,
    'message' => 'OTP verified successfully!',
    'token' => $token,
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
        'phone' => $user['phone'],
        'email' => $user['email'],
        'role' => $user['role']
    ]
]);

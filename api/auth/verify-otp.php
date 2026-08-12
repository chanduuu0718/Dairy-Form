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

if (!isValidPhone($phone) || empty($otp)) {
    jsonResponse(['success' => false, 'message' => 'Phone and OTP are required'], 422);
}

$db = Database::getInstance();
$user = $db->fetchOne(
    "SELECT id, name, phone, email, role, otp_code, otp_expires_at FROM users WHERE phone = ?",
    [$phone], 's'
);

if (!$user) {
    jsonResponse(['success' => false, 'message' => 'User not found'], 404);
}

if ($user['otp_code'] !== $otp) {
    jsonResponse(['success' => false, 'message' => 'Invalid OTP'], 401);
}

if (strtotime($user['otp_expires_at']) < time()) {
    jsonResponse(['success' => false, 'message' => 'OTP has expired'], 401);
}

// Clear OTP & mark verified
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

<?php
/**
 * API: Login User
 * POST /api/auth/login.php
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$login = sanitize($data['login'] ?? '');
$password = $data['password'] ?? '';

if ($login === '' || $password === '') {
    jsonResponse(['success' => false, 'message' => 'Phone/Email and password are required'], 422);
}

if (!checkRateLimit('login_' . $_SERVER['REMOTE_ADDR'], 10, 300)) {
    jsonResponse(['success' => false, 'message' => 'Too many login attempts. Try again later.'], 429);
}

$db = Database::getInstance();
$user = $db->fetchOne(
    "SELECT id, name, phone, email, password, role, is_verified FROM users WHERE phone = ? OR email = ?",
    [$login, $login], 'ss'
);

if (!$user || !Auth::verifyPassword($password, $user['password'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid credentials'], 401);
}

if (!(int)$user['is_verified']) {
    jsonResponse([
        'success' => false,
        'verification_required' => true,
        'phone' => $user['phone'],
        'message' => 'Please verify your mobile number with OTP before logging in.'
    ], 403);
}

Auth::loginUser($user['id'], $user['role']);

jsonResponse([
    'success' => true,
    'message' => 'Login successful!',
    'user' => [
        'id' => $user['id'],
        'name' => $user['name'],
        'phone' => $user['phone'],
        'email' => $user['email'],
        'role' => $user['role']
    ]
]);

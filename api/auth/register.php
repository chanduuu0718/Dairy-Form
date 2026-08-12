<?php
/**
 * API: Register User
 * POST /api/auth/register.php
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$name = sanitize($data['name'] ?? '');
$phone = sanitize($data['phone'] ?? '');
$email = sanitize($data['email'] ?? '');
$password = $data['password'] ?? '';
$address = sanitize($data['address'] ?? '');

// Validate
$errors = [];
if (empty($name)) $errors[] = 'Name is required';
if (!isValidPhone($phone)) $errors[] = 'Valid 10-digit phone number is required';
if (!empty($email) && !isValidEmail($email)) $errors[] = 'Valid email is required';
if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';

if (!empty($errors)) {
    jsonResponse(['success' => false, 'errors' => $errors], 422);
}

// Rate limiting
if (!checkRateLimit('register_' . $_SERVER['REMOTE_ADDR'], 5, 300)) {
    jsonResponse(['success' => false, 'message' => 'Too many attempts. Try again later.'], 429);
}

$db = Database::getInstance();

// Check if phone exists
$existing = $db->fetchOne("SELECT id FROM users WHERE phone = ?", [$phone], 's');
if ($existing) {
    jsonResponse(['success' => false, 'message' => 'Phone number already registered'], 409);
}

// Check if email exists
if (!empty($email)) {
    $existing = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email], 's');
    if ($existing) {
        jsonResponse(['success' => false, 'message' => 'Email already registered'], 409);
    }
}

// Create user
$hashedPassword = Auth::hashPassword($password);
$userId = $db->insert(
    "INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)",
    [$name, $phone, $email ?: null, $hashedPassword],
    'ssss'
);

// Add address if provided
if (!empty($address)) {
    $db->insert(
        "INSERT INTO user_addresses (user_id, address_line1, city, is_default) VALUES (?, ?, 'Not specified', 1)",
        [$userId, $address],
        'is'
    );
}

// Login the user; the HttpOnly auth cookie carries the session token.
Auth::loginUser($userId);

jsonResponse([
    'success' => true,
    'message' => 'Registration successful!',
    'user' => [
        'id' => $userId,
        'name' => $name,
        'phone' => $phone,
        'email' => $email
    ]
]);

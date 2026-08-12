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

$errors = [];
if (empty($name)) $errors[] = 'Name is required';
if (!isValidPhone($phone)) $errors[] = 'Valid 10-digit phone number is required';
if (!empty($email) && !isValidEmail($email)) $errors[] = 'Valid email is required';
if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
if (!empty($errors)) {
    jsonResponse(['success' => false, 'errors' => $errors], 422);
}

if (!checkRateLimit('register_' . $_SERVER['REMOTE_ADDR'], 5, 300)) {
    jsonResponse(['success' => false, 'message' => 'Too many attempts. Try again later.'], 429);
}
if (!checkRateLimit('register_phone_' . $phone, 3, 600)) {
    jsonResponse(['success' => false, 'message' => 'Too many registration attempts for this number. Try again later.'], 429);
}

$db = Database::getInstance();

$existing = $db->fetchOne("SELECT id, is_verified FROM users WHERE phone = ?", [$phone], 's');
if ($existing) {
    if (!(int)$existing['is_verified']) {
        jsonResponse(['success' => false, 'message' => 'This number has a pending verification. Please complete OTP verification or use login.'], 409);
    }
    jsonResponse(['success' => false, 'message' => 'Phone number already registered'], 409);
}

if (!empty($email)) {
    $existing = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email], 's');
    if ($existing) {
        jsonResponse(['success' => false, 'message' => 'Email already registered'], 409);
    }
}

$hashedPassword = Auth::hashPassword($password);
$userId = $db->insert(
    "INSERT INTO users (name, phone, email, password, is_verified) VALUES (?, ?, ?, ?, 0)",
    [$name, $phone, $email ?: null, $hashedPassword],
    'ssss'
);

if (!empty($address)) {
    $db->insert(
        "INSERT INTO user_addresses (user_id, address_line1, city, is_default) VALUES (?, ?, 'Not specified', 1)",
        [$userId, $address],
        'is'
    );
}

// Send the first OTP using the same real SMS provider used by OTP login.
if (SMS_PROVIDER !== 'TWILIO' || TWILIO_ACCOUNT_SID === '' || TWILIO_AUTH_TOKEN === '' || TWILIO_FROM_NUMBER === '') {
    error_log('Registration OTP SMS configuration is incomplete.');
    $db->update("DELETE FROM user_addresses WHERE user_id = ?", [$userId], 'i');
    $db->update("DELETE FROM users WHERE id = ?", [$userId], 'i');
    jsonResponse(['success' => false, 'message' => 'OTP service is temporarily unavailable. Please try again later.'], 503);
}

$otp = generateOTP();
$expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));
$message = 'Your PM Dairy Farm verification OTP is ' . $otp . '. It expires in 5 minutes. Do not share this code.';

$ch = curl_init('https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode(TWILIO_ACCOUNT_SID) . '/Messages.json');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_USERPWD => TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN,
    CURLOPT_POSTFIELDS => http_build_query([
        'To' => '+91' . $phone,
        'From' => TWILIO_FROM_NUMBER,
        'Body' => $message
    ], '', '&'),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

$smsResponse = json_decode($response ?: '', true);
if ($curlError || $httpCode < 200 || $httpCode >= 300 || !is_array($smsResponse) || empty($smsResponse['sid'])) {
    error_log('Registration OTP delivery failed. HTTP ' . $httpCode . ($curlError ? ' - ' . $curlError : ''));
    $db->update("DELETE FROM user_addresses WHERE user_id = ?", [$userId], 'i');
    $db->update("DELETE FROM users WHERE id = ?", [$userId], 'i');
    jsonResponse(['success' => false, 'message' => 'Unable to send verification OTP. Please try again.'], 502);
}

$db->update(
    "UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?",
    [$otp, $expiresAt, $userId],
    'ssi'
);

jsonResponse([
    'success' => true,
    'otp_required' => true,
    'message' => 'Account created. We sent a verification OTP to your mobile number.',
    'user' => [
        'id' => $userId,
        'name' => $name,
        'phone' => $phone,
        'email' => $email
    ],
    'expires_in' => 300
], 201);

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

if (SMS_PROVIDER !== 'TWILIO') {
    jsonResponse(['success' => false, 'message' => 'SMS provider is not configured'], 503);
}

if (TWILIO_ACCOUNT_SID === '' || TWILIO_AUTH_TOKEN === '' || TWILIO_FROM_NUMBER === '') {
    error_log('OTP SMS configuration is incomplete.');
    jsonResponse(['success' => false, 'message' => 'OTP service is temporarily unavailable'], 503);
}

$otp = generateOTP();
$expiresAt = date('Y-m-d H:i:s', strtotime('+5 minutes'));
$message = 'Your PM Dairy Farm login OTP is ' . $otp . '. It expires in 5 minutes. Do not share this code.';

$ch = curl_init(
    'https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode(TWILIO_ACCOUNT_SID) . '/Messages.json'
);
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

if ($curlError || $httpCode < 200 || $httpCode >= 300) {
    error_log('OTP SMS delivery failed. HTTP ' . $httpCode . ($curlError ? ' - ' . $curlError : ''));
    jsonResponse(['success' => false, 'message' => 'Unable to send OTP. Please try again.'], 502);
}

$smsResponse = json_decode($response, true);
if (!is_array($smsResponse) || empty($smsResponse['sid'])) {
    error_log('OTP SMS provider returned an unexpected response.');
    jsonResponse(['success' => false, 'message' => 'Unable to send OTP. Please try again.'], 502);
}

// Store the OTP only after the SMS provider accepts the message.
$db->update(
    "UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE phone = ?",
    [$otp, $expiresAt, $phone],
    'sss'
);

// Never return the OTP or provider response to the browser.
jsonResponse([
    'success' => true,
    'message' => 'OTP sent to your phone',
    'expires_in' => 300
]);

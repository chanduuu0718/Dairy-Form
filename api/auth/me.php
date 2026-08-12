<?php
/**
 * API: Get Current User
 * GET /api/auth/me.php
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$user = Auth::getUser();
if (!$user) {
    jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
}

$db = Database::getInstance();
$addresses = $db->fetchAll("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC", [$user['id']], 'i');

jsonResponse([
    'success' => true,
    'user' => array_merge($user, ['addresses' => $addresses])
]);

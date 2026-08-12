<?php
/**
 * API: Logout User
 * POST /api/auth/logout.php
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

Auth::logout();
jsonResponse(['success' => true, 'message' => 'Logged out successfully']);

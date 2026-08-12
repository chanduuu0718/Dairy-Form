<?php
/**
 * API: User Management
 * GET  /api/users/ - Admin: all users
 * POST /api/users/?action=update-profile - Update profile
 * POST /api/users/?action=add-address - Add address
 * POST /api/users/?action=delete-address - Delete address
 * POST /api/users/?action=change-password - Change password
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $admin = Auth::requireAdmin();
    $page = max(1, intval($_GET['page'] ?? 1));
    $search = sanitize($_GET['search'] ?? '');

    $where = "role = 'customer'";
    $params = [];
    $types = '';

    if (!empty($search)) {
        $where .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
        $types .= 'sss';
    }

    $countRow = $db->fetchOne("SELECT COUNT(*) as total FROM users WHERE $where", $params, $types);
    $pagination = getPagination($countRow['total'], $page, ADMIN_ITEMS_PER_PAGE);

    $users = $db->fetchAll(
        "SELECT id, name, phone, email, is_verified, created_at FROM users WHERE $where ORDER BY created_at DESC LIMIT " . ADMIN_ITEMS_PER_PAGE . " OFFSET {$pagination['offset']}",
        $params, $types
    );

    jsonResponse(['success' => true, 'users' => $users, 'pagination' => $pagination]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = Auth::requireAuth();
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $action = sanitize($_GET['action'] ?? '');

    switch ($action) {
        case 'update-profile':
            $name = sanitize($data['name'] ?? '');
            $email = sanitize($data['email'] ?? '');

            if (empty($name)) {
                jsonResponse(['success' => false, 'message' => 'Name is required'], 422);
            }

            if (!empty($email)) {
                $existing = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user['id']], 'si');
                if ($existing) {
                    jsonResponse(['success' => false, 'message' => 'Email already in use'], 409);
                }
            }

            $db->update(
                "UPDATE users SET name = ?, email = ? WHERE id = ?",
                [$name, $email ?: null, $user['id']],
                'ssi'
            );

            jsonResponse(['success' => true, 'message' => 'Profile updated']);
            break;

        case 'add-address':
            $label = sanitize($data['label'] ?? 'Home');
            $line1 = sanitize($data['address_line1'] ?? '');
            $line2 = sanitize($data['address_line2'] ?? '');
            $city = sanitize($data['city'] ?? '');
            $state = sanitize($data['state'] ?? 'Haryana');
            $pincode = sanitize($data['pincode'] ?? '');
            $isDefault = intval($data['is_default'] ?? 0);

            if (empty($line1) || empty($city) || empty($pincode)) {
                jsonResponse(['success' => false, 'message' => 'Address, city, and pincode are required'], 422);
            }

            if ($isDefault) {
                $db->update("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?", [$user['id']], 'i');
            }

            $addrId = $db->insert(
                "INSERT INTO user_addresses (user_id, label, address_line1, address_line2, city, state, pincode, is_default)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$user['id'], $label, $line1, $line2, $city, $state, $pincode, $isDefault],
                'issssssi'
            );

            jsonResponse(['success' => true, 'message' => 'Address added', 'address_id' => $addrId]);
            break;

        case 'delete-address':
            $addrId = intval($data['address_id'] ?? 0);
            $db->update("DELETE FROM user_addresses WHERE id = ? AND user_id = ?", [$addrId, $user['id']], 'ii');
            jsonResponse(['success' => true, 'message' => 'Address removed']);
            break;

        case 'change-password':
            $currentPassword = $data['current_password'] ?? '';
            $newPassword = $data['new_password'] ?? '';

            if (strlen($newPassword) < 6) {
                jsonResponse(['success' => false, 'message' => 'New password must be at least 6 characters'], 422);
            }

            $userData = $db->fetchOne("SELECT password FROM users WHERE id = ?", [$user['id']], 'i');
            if (!Auth::verifyPassword($currentPassword, $userData['password'])) {
                jsonResponse(['success' => false, 'message' => 'Current password is incorrect'], 401);
            }

            $hashed = Auth::hashPassword($newPassword);
            $db->update("UPDATE users SET password = ? WHERE id = ?", [$hashed, $user['id']], 'si');

            jsonResponse(['success' => true, 'message' => 'Password changed successfully']);
            break;

        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

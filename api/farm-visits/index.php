<?php
/**
 * API: Farm Visits
 * POST /api/farm-visits/ - Book a visit
 * GET  /api/farm-visits/ - Admin: all bookings
 * POST /api/farm-visits/?action=update-status - Admin: update status
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $admin = Auth::requireAdmin();

    $status = sanitize($_GET['status'] ?? '');
    $page = max(1, intval($_GET['page'] ?? 1));

    $where = "1=1";
    $params = [];
    $types = '';

    if (!empty($status)) {
        $where .= " AND status = ?";
        $params[] = $status;
        $types .= 's';
    }

    $countRow = $db->fetchOne("SELECT COUNT(*) as total FROM farm_visits WHERE $where", $params, $types);
    $pagination = getPagination($countRow['total'], $page, ADMIN_ITEMS_PER_PAGE);

    $visits = $db->fetchAll(
        "SELECT * FROM farm_visits WHERE $where ORDER BY preferred_date DESC LIMIT " . ADMIN_ITEMS_PER_PAGE . " OFFSET {$pagination['offset']}",
        $params, $types
    );

    jsonResponse(['success' => true, 'visits' => $visits, 'pagination' => $pagination]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $action = sanitize($_GET['action'] ?? '');

    // Admin: update status
    if ($action === 'update-status') {
        $admin = Auth::requireAdmin();
        $visitId = intval($data['visit_id'] ?? 0);
        $newStatus = sanitize($data['status'] ?? '');
        $notes = sanitize($data['admin_notes'] ?? '');

        if (!in_array($newStatus, ['pending', 'confirmed', 'rejected', 'completed'])) {
            jsonResponse(['success' => false, 'message' => 'Invalid status'], 400);
        }

        $db->update("UPDATE farm_visits SET status = ?, admin_notes = ? WHERE id = ?", [$newStatus, $notes, $visitId], 'ssi');
        jsonResponse(['success' => true, 'message' => 'Visit status updated']);
    }

    // Book a visit
    $name = sanitize($data['name'] ?? '');
    $phone = sanitize($data['phone'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $preferredDate = sanitize($data['preferred_date'] ?? '');
    $visitors = max(1, intval($data['visitors'] ?? 1));
    $message = sanitize($data['message'] ?? '');

    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (!isValidPhone($phone)) $errors[] = 'Valid phone number required';
    if (empty($preferredDate)) $errors[] = 'Preferred date required';
    if (strtotime($preferredDate) < strtotime('tomorrow')) $errors[] = 'Date must be in the future';

    if (!empty($errors)) {
        jsonResponse(['success' => false, 'errors' => $errors], 422);
    }

    if (!checkRateLimit('farm_visit_' . $_SERVER['REMOTE_ADDR'], 3, 3600)) {
        jsonResponse(['success' => false, 'message' => 'Too many booking requests. Try again later.'], 429);
    }

    $visitId = $db->insert(
        "INSERT INTO farm_visits (name, phone, email, preferred_date, visitors, message) VALUES (?, ?, ?, ?, ?, ?)",
        [$name, $phone, $email, $preferredDate, $visitors, $message],
        'ssssss'
    );

    jsonResponse(['success' => true, 'message' => 'Farm visit booked! We will contact you soon to confirm.', 'visit_id' => $visitId], 201);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

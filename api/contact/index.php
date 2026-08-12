<?php
/**
 * API: Contact Form
 * POST /api/contact/ - Submit contact form
 * POST /api/contact/?action=mark-read - Admin: mark as read
 * GET  /api/contact/ - Admin: all messages
 */
require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $admin = Auth::requireAdmin();
    $page = max(1, intval($_GET['page'] ?? 1));

    $countRow = $db->fetchOne("SELECT COUNT(*) as total FROM contacts");
    $pagination = getPagination($countRow['total'], $page, ADMIN_ITEMS_PER_PAGE);

    $messages = $db->fetchAll(
        "SELECT * FROM contacts ORDER BY created_at DESC LIMIT " . ADMIN_ITEMS_PER_PAGE . " OFFSET {$pagination['offset']}"
    );

    jsonResponse(['success' => true, 'messages' => $messages, 'pagination' => $pagination]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    $action = sanitize($_GET['action'] ?? '');

    // Admin: Mark as read
    if ($action === 'mark-read') {
        $admin = Auth::requireAdmin();
        $id = intval($data['id'] ?? 0);
        if ($id > 0) {
            $db->update("UPDATE contacts SET is_read = 1 WHERE id = ?", [$id], 'i');
            jsonResponse(['success' => true, 'message' => 'Message marked as read']);
        }
        jsonResponse(['success' => false, 'message' => 'Invalid message ID'], 400);
    }

    $name = sanitize($data['name'] ?? '');
    $phone = sanitize($data['phone'] ?? '');
    $email = sanitize($data['email'] ?? '');
    $subject = sanitize($data['subject'] ?? '');
    $message = sanitize($data['message'] ?? '');

    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (!isValidEmail($email)) $errors[] = 'Valid email required';
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';

    if (!empty($errors)) {
        jsonResponse(['success' => false, 'errors' => $errors], 422);
    }

    if (!checkRateLimit('contact_' . $_SERVER['REMOTE_ADDR'], 5, 3600)) {
        jsonResponse(['success' => false, 'message' => 'Too many messages. Try again later.'], 429);
    }

    $db->insert(
        "INSERT INTO contacts (name, phone, email, subject, message) VALUES (?, ?, ?, ?, ?)",
        [$name, $phone, $email, $subject, $message],
        'sssss'
    );

    jsonResponse(['success' => true, 'message' => 'Thank you! Your message has been sent. We will get back to you soon.'], 201);
}

jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

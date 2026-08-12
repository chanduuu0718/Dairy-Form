<?php
/**
 * Database Setup Script
 * Run from the command line only.
 *
 * Required environment variables:
 *   DB_HOST, DB_USER, DB_PASS, ADMIN_PHONE, ADMIN_EMAIL, ADMIN_PASSWORD
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$adminPhone = getenv('ADMIN_PHONE') ?: '';
$adminEmail = getenv('ADMIN_EMAIL') ?: '';
$adminPassword = getenv('ADMIN_PASSWORD') ?: '';

if ($adminPhone === '' || $adminEmail === '' || $adminPassword === '') {
    fwrite(STDERR, "ADMIN_PHONE, ADMIN_EMAIL and ADMIN_PASSWORD must be set before running setup.\n");
    exit(1);
}

if (!preg_match('/^[6-9]\d{9}$/', $adminPhone) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Invalid admin phone or email.\n");
    exit(1);
}

if (strlen($adminPassword) < 12) {
    fwrite(STDERR, "ADMIN_PASSWORD must contain at least 12 characters.\n");
    exit(1);
}

try {
    $conn = new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
        throw new Exception('Database server connection failed.');
    }
    $conn->set_charset('utf8mb4');

    $sqlFile = __DIR__ . '/schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception('schema.sql not found.');
    }

    $sql = file_get_contents($sqlFile);
    if (!$conn->multi_query($sql)) {
        throw new Exception('Database schema installation failed.');
    }

    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    // Replace the legacy seeded admin account with operator-supplied credentials.
    $conn->query("DELETE FROM pm_dairy.users WHERE phone = '9876543210' AND email = 'admin@pmdairy.com'");

    $passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        "INSERT INTO pm_dairy.users (name, phone, email, password, role, is_verified)
         VALUES (?, ?, ?, ?, 'admin', 1)"
    );
    $name = 'PM Dairy Admin';
    $stmt->bind_param('ssss', $name, $adminPhone, $adminEmail, $passwordHash);
    $stmt->execute();
    $stmt->close();

    $conn->close();
    fwrite(STDOUT, "Database setup completed successfully.\n");
    fwrite(STDOUT, "Admin account created from supplied environment credentials.\n");
} catch (Throwable $e) {
    fwrite(STDERR, "Database setup failed. Check your configuration and database server logs.\n");
    exit(1);
}

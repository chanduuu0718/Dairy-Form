<?php
/**
 * Database Setup Script
 * Run this file once to create database and tables
 * URL: http://localhost/DairyForm/database/setup.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';

echo "<h1>PM Dairy - Database Setup</h1>";
echo "<pre style='background:#1a1a1a;color:#0f0;padding:20px;border-radius:8px;font-size:14px;max-width:800px;'>";

try {
    // Connect without database
    $conn = new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "✓ Connected to MySQL server\n";

    // Read and execute SQL
    $sqlFile = __DIR__ . '/schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("schema.sql not found!");
    }

    $sql = file_get_contents($sqlFile);
    echo "✓ Read schema.sql (" . strlen($sql) . " bytes)\n\n";

    // Execute multi query
    if ($conn->multi_query($sql)) {
        $queryCount = 0;
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
            $queryCount++;
        } while ($conn->more_results() && $conn->next_result());

        echo "✓ Executed $queryCount SQL statements\n";
    } else {
        throw new Exception("SQL Error: " . $conn->error);
    }

    echo "\n========================================\n";
    echo "✓ DATABASE SETUP COMPLETE!\n";
    echo "========================================\n\n";
    echo "Database: pm_dairy\n";
    echo "Admin Login:\n";
    echo "  Phone: 9876543210\n";
    echo "  Email: admin@pmdairy.com\n";
    echo "  Password: admin123\n\n";
    echo "Admin Panel: http://localhost/DairyForm/admin/\n";
    echo "Website: http://localhost/DairyForm/\n";

    $conn->close();

} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
}

echo "</pre>";
echo "<br><a href='/DairyForm/' style='display:inline-block;padding:12px 24px;background:#1A3C2A;color:#fff;text-decoration:none;border-radius:8px;font-family:sans-serif;'>→ Go to Website</a>";
echo " <a href='/DairyForm/admin/' style='display:inline-block;padding:12px 24px;background:#D4A843;color:#fff;text-decoration:none;border-radius:8px;font-family:sans-serif;'>→ Go to Admin Panel</a>";
?>

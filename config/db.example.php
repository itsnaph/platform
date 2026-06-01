<?php
// FILE: config/db.example.php
// Copy this file to config/db.php and fill in your actual credentials.
// NEVER commit config/db.php to version control (it is in .gitignore).

define('DB_HOST', 'your_mysql_host_here');     // e.g. sql123.epizy.com
define('DB_NAME', 'your_database_name_here');  // e.g. epiz_12345678_hustlehub
define('DB_USER', 'your_database_user_here');
define('DB_PASS', 'your_database_password_here');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('Service unavailable. Please try again later.');
}

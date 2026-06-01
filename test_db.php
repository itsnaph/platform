<?php
try {
    $p = new PDO('mysql:host=127.0.0.1;port=3306;dbname=hustlehub;charset=utf8mb4', 'root', '');
    echo 'OK: ' . $p->getAttribute(PDO::ATTR_SERVER_VERSION) . PHP_EOL;
    $stmt = $p->query('SELECT COUNT(*) as cnt FROM users');
    $row = $stmt->fetch();
    echo 'Users in DB: ' . $row['cnt'] . PHP_EOL;
} catch (Exception $e) {
    echo 'FAIL: ' . $e->getMessage() . PHP_EOL;
}

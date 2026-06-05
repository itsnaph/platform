<?php
// FILE: admin/includes/admin_header.php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// RBAC — must be admin or moderator
if (!isset($_SESSION['user_id'], $_SESSION['role']) ||
    !in_array($_SESSION['role'], ['admin','moderator'], true)) {
    header('Location: /pages/login.php');
    exit;
}

// CSRF generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
}

// Session timeout
if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) {
    session_unset(); session_destroy();
    header('Location: /pages/login.php?reason=timeout'); exit;
}
$_SESSION['last_active'] = time();

$isSuperAdmin = $_SESSION['role'] === 'admin';
$adminName    = $_SESSION['user_name'] ?? 'Admin';
$adminRole    = ucfirst($_SESSION['role']);
$currentFile  = basename($_SERVER['PHP_SELF']);

function e($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

<?php
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

//Generate CSRF token once per session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = md5(uniqid(mt_rand(), true));
}

//Session timeout: 30 minutes of inactivity
if (isset($_SESSION['last_active']) && (time() - $_SESSION['last_active'] > 1800)) {
    session_unset();
    session_destroy();
    header('Location: /pages/login.php?reason=timeout');
    exit;
}
$_SESSION['last_active'] = time();

//Redirect to login if user is not logged in or does not have the right role
function requireRole($role)
{
    if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
        header('Location: /pages/login.php');
        exit;
    }
    if ($_SESSION['role'] !== $role) {
        die('Access denied.');
    }
}

//Check CSRF token on POST submissions
function verifyCsrfToken()
{
    $token = $_POST['csrf_token'] ?? '';
    if ($_SESSION['csrf_token'] !== $token) {
        die('Invalid request. Please go back and try again.');
    }
}

//Escape output to prevent XSS
function e($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

//Check if a user is currently logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

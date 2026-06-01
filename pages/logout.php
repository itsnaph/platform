<?php
// FILE: pages/logout.php
require_once '../includes/auth.php';
session_unset();
session_destroy();
header('Location: /pages/login.php?msg=logged_out');
exit;

<?php
$pageTitle = $pageTitle ?? 'HustleHub';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> — HustleHub</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Custom styles -->
  <link rel="stylesheet" href="/assets/css/style.css">
  <!-- CSRF meta tag for JS Ajax calls -->
  <meta name="csrf-token" content="<?= e($_SESSION['csrf_token']) ?>">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-md navbar-hustlehub">
  <div class="container-xl">
    <a class="navbar-brand" href="/index.php">HustleHub</a>
    <button class="navbar-toggler" type="button"
            data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-md-0">
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'browse.php' ? 'active' : '' ?>"
             href="/pages/browse.php">Browse Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/pages/how_it_works.php">How It Works</a>
        </li>
      </ul>
      <ul class="navbar-nav mb-2 mb-md-0">
        <?php if (isLoggedIn()): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userMenu" data-bs-toggle="dropdown">
              <i class="bi bi-person-circle me-1"></i><?= e($_SESSION['user_name'] ?? 'Account') ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <?php if ($_SESSION['role'] === 'worker'): ?>
                <li><a class="dropdown-item" href="/pages/worker_dashboard.php">My Dashboard</a></li>
                <li><a class="dropdown-item" href="/pages/worker_dashboard.php">My Listings</a></li>
              <?php elseif ($_SESSION['role'] === 'client'): ?>
                <li><a class="dropdown-item" href="/pages/client_dashboard.php">My Bookings</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="/pages/logout.php">Logout</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/pages/login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-primary btn-sm ms-2" href="/pages/register.php">Register</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<!-- /NAVBAR -->

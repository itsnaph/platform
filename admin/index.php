<?php
// FILE: admin/index.php — Admin Dashboard
// Requires: admin or moderator role (checked in admin_header.php)
require_once '../admin/admin_header.php';
require_once '../config/db.php';

// Summary stats
$stats = [];
$stats['total_users']       = $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('worker','client')")->fetchColumn();
$stats['pending_listings']  = $pdo->query("SELECT COUNT(*) FROM services WHERE approval_status = 'pending'")->fetchColumn();
$stats['open_disputes']     = $pdo->query("SELECT COUNT(*) FROM disputes WHERE status = 'open'")->fetchColumn();
$stats['escrow_held']       = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE escrow_status = 'held'")->fetchColumn();
$stats['total_bookings']    = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$stats['completed_bookings']= $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'completed'")->fetchColumn();

// Recent activity (last 5 bookings)
$recentBookings = $pdo->query("
    SELECT b.id, b.status, b.created_at, s.title, u.full_name AS client_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.client_id = u.id
    ORDER BY b.created_at DESC LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — HustleHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'admin_nav.php'; ?>

<div class="admin-wrap">
  <div class="container-xl py-4">
    <h2 class="admin-page-title">Dashboard</h2>
    <p class="text-muted small mb-4">Welcome back, <strong><?= e($adminName) ?></strong> (<?= e($adminRole) ?>)</p>

    <!-- STAT CARDS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
          <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
          <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
          <div class="stat-label">Total Users</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card stat-warning">
          <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
          <div class="stat-value"><?= $stats['pending_listings'] ?></div>
          <div class="stat-label">Pending Listings</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card stat-danger">
          <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
          <div class="stat-value"><?= $stats['open_disputes'] ?></div>
          <div class="stat-label">Open Disputes</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card stat-accent">
          <div class="stat-icon"><i class="bi bi-safe2-fill"></i></div>
          <div class="stat-value">R<?= number_format($stats['escrow_held'],0) ?></div>
          <div class="stat-label">Escrow Held</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
          <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
          <div class="stat-value"><?= $stats['total_bookings'] ?></div>
          <div class="stat-label">Total Bookings</div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card stat-success">
          <div class="stat-icon"><i class="bi bi-check-circle-fill"></i></div>
          <div class="stat-value"><?= $stats['completed_bookings'] ?></div>
          <div class="stat-label">Completed</div>
        </div>
      </div>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-md-4">
        <a href="listings.php" class="admin-action-card">
          <i class="bi bi-card-checklist"></i>
          <span>Review Pending Listings</span>
          <?php if ($stats['pending_listings'] > 0): ?>
            <span class="badge bg-warning text-dark ms-auto"><?= $stats['pending_listings'] ?></span>
          <?php endif; ?>
        </a>
      </div>
      <div class="col-12 col-md-4">
        <a href="disputes.php" class="admin-action-card">
          <i class="bi bi-shield-exclamation"></i>
          <span>Manage Disputes</span>
          <?php if ($stats['open_disputes'] > 0): ?>
            <span class="badge bg-danger ms-auto"><?= $stats['open_disputes'] ?></span>
          <?php endif; ?>
        </a>
      </div>
      <?php if ($isSuperAdmin): ?>
      <div class="col-12 col-md-4">
        <a href="users.php" class="admin-action-card">
          <i class="bi bi-person-gear"></i>
          <span>Manage Users</span>
        </a>
      </div>
      <?php endif; ?>
    </div>

    <!-- RECENT BOOKINGS -->
    <div class="card shadow-sm border-0">
      <div class="card-header" style="background:var(--admin-primary);color:#fff">
        <h5 class="mb-0 fw-bold" style="font-size:1rem">Recent Bookings</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>#ID</th><th>Service</th><th>Client</th><th>Status</th><th>Created</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recentBookings as $rb): ?>
              <tr>
                <td class="small text-muted">#<?= $rb['id'] ?></td>
                <td class="small fw-semibold"><?= e($rb['title']) ?></td>
                <td class="small"><?= e($rb['client_name']) ?></td>
                <td><span class="badge-<?= e($rb['status']) ?>"><?= ucfirst(str_replace('_',' ',$rb['status'])) ?></span></td>
                <td class="small text-muted"><?= date('d M Y', strtotime($rb['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

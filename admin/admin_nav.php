<?php
// FILE: admin/admin_nav.php — Shared admin sidebar navigation
// $currentFile and $isSuperAdmin set by admin_header.php
?>
<nav class="admin-sidebar d-none d-md-flex">
  <div class="admin-brand">
    <span>HustleHub</span>
    <small>Admin Portal</small>
  </div>
  <ul class="admin-nav-list">
    <li>
      <a href="index.php" class="admin-nav-link <?= $currentFile==='index.php'?'active':'' ?>">
        <i class="bi bi-grid-fill"></i> Dashboard
      </a>
    </li>
    <li>
      <a href="listings.php" class="admin-nav-link <?= $currentFile==='listings.php'?'active':'' ?>">
        <i class="bi bi-card-checklist"></i> Listings
      </a>
    </li>
    <li>
      <a href="disputes.php" class="admin-nav-link <?= $currentFile==='disputes.php'?'active':'' ?>">
        <i class="bi bi-shield-exclamation"></i> Disputes
      </a>
    </li>
    <?php if ($isSuperAdmin): ?>
    <li>
      <a href="users.php" class="admin-nav-link <?= $currentFile==='users.php'?'active':'' ?>">
        <i class="bi bi-people-fill"></i> Users
      </a>
    </li>
    <li>
      <a href="audit_log.php" class="admin-nav-link <?= $currentFile==='audit_log.php'?'active':'' ?>">
        <i class="bi bi-journal-text"></i> Audit Log
      </a>
    </li>
    <?php endif; ?>
    <li class="mt-auto">
      <a href="../pages/logout.php" class="admin-nav-link text-danger">
        <i class="bi bi-box-arrow-left"></i> Logout
      </a>
    </li>
  </ul>
</nav>

<!-- Mobile top bar -->
<nav class="navbar d-md-none" style="background:#0A2342;padding:0.6rem 1rem;">
  <span style="color:#fff;font-weight:800">HustleHub Admin</span>
  <div class="dropdown ms-auto">
    <button class="btn btn-sm btn-outline-light dropdown-toggle" data-bs-toggle="dropdown">Menu</button>
    <ul class="dropdown-menu dropdown-menu-end">
      <li><a class="dropdown-item" href="index.php"><i class="bi bi-grid-fill me-2"></i>Dashboard</a></li>
      <li><a class="dropdown-item" href="listings.php"><i class="bi bi-card-checklist me-2"></i>Listings</a></li>
      <li><a class="dropdown-item" href="disputes.php"><i class="bi bi-shield-exclamation me-2"></i>Disputes</a></li>
      <?php if ($isSuperAdmin): ?>
      <li><a class="dropdown-item" href="users.php"><i class="bi bi-people-fill me-2"></i>Users</a></li>
      <li><a class="dropdown-item" href="audit_log.php"><i class="bi bi-journal-text me-2"></i>Audit Log</a></li>
      <?php endif; ?>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="../pages/logout.php">Logout</a></li>
    </ul>
  </div>
</nav>

<?php
// FILE: admin/users.php — User Management (Super Admin ONLY)
require_once '../admin/admin_header.php';
require_once '../config/db.php';

// Super Admin gate — moderators cannot access this page
if (!$isSuperAdmin) {
    die('<h2 style="font-family:sans-serif;padding:2rem;color:#721c24">Access Denied. Super Admin only.</h2>');
}

$msg = '';

// Handle role change or deactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['csrf_token'] !== ($_POST['csrf_token'] ?? '')) die('Invalid request.');

    $targetId = (int)($_POST['target_user_id'] ?? 0);
    $action   = $_POST['action'] ?? '';

    // Cannot change own role
    if ($targetId === (int)$_SESSION['user_id']) {
        $msg = 'error_self';
    } elseif ($action === 'deactivate') {
        $pdo->prepare("UPDATE users SET role = 'client' WHERE id = ?")
            ->execute([$targetId]);
        $pdo->prepare("INSERT INTO audit_log (admin_id, action, target_type, target_id, notes) VALUES (?,?,?,?,?)")
            ->execute([$_SESSION['user_id'], 'USER_DEACTIVATED', 'user', $targetId, "Role reset to client"]);
        $msg = 'success_deactivate';
    } elseif (in_array($action, ['worker','client','moderator'], true)) {
        $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")
            ->execute([$action, $targetId]);
        $pdo->prepare("INSERT INTO audit_log (admin_id, action, target_type, target_id, notes) VALUES (?,?,?,?,?)")
            ->execute([$_SESSION['user_id'], 'USER_ROLE_CHANGED', 'user', $targetId, "Role changed to $action"]);
        $msg = 'success_role';
    }
}

// Fetch all users (excluding admins from the table for safety)
$search = trim($_GET['q'] ?? '');
$sql = "SELECT id, full_name, email, role, is_verified, created_at FROM users ORDER BY created_at DESC";
$params = [];
if ($search) {
    $sql = "SELECT id, full_name, email, role, is_verified, created_at FROM users
            WHERE full_name LIKE ? OR email LIKE ? ORDER BY created_at DESC";
    $params = ["%$search%", "%$search%"];
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management — HustleHub Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'admin_nav.php'; ?>

<div class="admin-wrap">
  <div class="container-xl py-4">
    <h2 class="admin-page-title">User Management <span class="badge bg-danger ms-2" style="font-size:0.65rem">Super Admin</span></h2>

    <?php if ($msg === 'success_role' || $msg === 'success_deactivate'): ?>
      <div class="alert alert-success">Action completed successfully.</div>
    <?php elseif ($msg === 'error_self'): ?>
      <div class="alert alert-danger">You cannot modify your own account here.</div>
    <?php endif; ?>

    <!-- Search -->
    <form method="GET" class="mb-4 d-flex gap-2">
      <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by name or email…" value="<?= e($search) ?>" style="max-width:320px">
      <button class="btn btn-primary btn-sm">Search</button>
      <?php if ($search): ?><a href="users.php" class="btn btn-outline-secondary btn-sm">Clear</a><?php endif; ?>
    </form>

    <div class="table-responsive">
      <table class="table table-hover align-middle bg-white shadow-sm rounded">
        <thead style="background:#0A2342;color:#fff">
          <tr>
            <th>#</th><th>Name</th><th>Email</th>
            <th>Role</th><th>Verified</th><th>Joined</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td class="small text-muted"><?= $u['id'] ?></td>
            <td class="fw-semibold small"><?= e($u['full_name']) ?></td>
            <td class="small"><?= e($u['email']) ?></td>
            <td>
              <span class="badge <?= match($u['role']) {
                'admin'=>'bg-dark','moderator'=>'bg-secondary',
                'worker'=>'bg-primary','client'=>'bg-info text-dark', default=>'bg-light text-dark'
              } ?>">
                <?= ucfirst($u['role']) ?>
              </span>
            </td>
            <td class="text-center">
              <?= $u['is_verified'] ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>' ?>
            </td>
            <td class="small text-muted"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td>
              <?php if ($u['role'] !== 'admin'): ?>
              <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">Action</button>
                <ul class="dropdown-menu">
                  <?php foreach (['worker','client','moderator'] as $role): ?>
                    <?php if ($role !== $u['role']): ?>
                    <li>
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                        <input type="hidden" name="action" value="<?= $role ?>">
                        <button type="submit" class="dropdown-item">
                          Set as <?= ucfirst($role) ?>
                        </button>
                      </form>
                    </li>
                    <?php endif; ?>
                  <?php endforeach; ?>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <form method="POST" class="d-inline"
                          onsubmit="return confirm('Reset this user to client role?')">
                      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                      <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                      <input type="hidden" name="action" value="deactivate">
                      <button type="submit" class="dropdown-item text-danger">Reset to Client</button>
                    </form>
                  </li>
                </ul>
              </div>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

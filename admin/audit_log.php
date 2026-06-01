<?php
// FILE: admin/audit_log.php — Audit Log (Super Admin ONLY)
require_once '../admin/admin_header.php';
require_once '../config/db.php';

if (!$isSuperAdmin) {
    die('<h2 style="font-family:sans-serif;padding:2rem">Access Denied. Super Admin only.</h2>');
}

$logs = $pdo->query("
    SELECT al.*, u.full_name AS admin_name
    FROM audit_log al
    JOIN users u ON al.admin_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 100
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Audit Log — HustleHub Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'admin_nav.php'; ?>

<div class="admin-wrap">
  <div class="container-xl py-4">
    <h2 class="admin-page-title">Audit Log <span class="badge bg-danger ms-2" style="font-size:0.65rem">Super Admin</span></h2>
    <p class="text-muted small mb-4">Every admin action is permanently recorded here with timestamp and admin ID. Showing last 100 entries.</p>

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-sm mb-0">
            <thead style="background:#0A2342;color:#fff">
              <tr>
                <th>#</th><th>Admin</th><th>Action</th>
                <th>Target</th><th>Notes</th><th>Timestamp</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log): ?>
              <tr>
                <td class="small text-muted"><?= $log['id'] ?></td>
                <td class="small fw-semibold"><?= e($log['admin_name']) ?></td>
                <td>
                  <code style="font-size:0.75rem;color:<?=
                    (strpos($log['action'],'REJECT')!==false||strpos($log['action'],'REFUND')!==false)?'#721c24':
                    ((strpos($log['action'],'APPROVED')!==false||strpos($log['action'],'RELEASE')!==false)?'#155724':'#0A2342')
                  ?>"><?= e($log['action']) ?></code>
                </td>
                <td class="small"><?= e($log['target_type'] ?? '') ?> #<?= (int)($log['target_id'] ?? 0) ?></td>
                <td class="small text-muted" style="max-width:280px"><?= e(substr($log['notes']??'',0,120)) ?></td>
                <td class="small text-muted text-nowrap"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($logs)): ?>
              <tr><td colspan="6" class="text-center text-muted py-4">No audit entries yet.</td></tr>
              <?php endif; ?>
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

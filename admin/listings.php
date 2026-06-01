<?php
// FILE: admin/listings.php — Listing Approval Panel
// Super Admin AND Moderator can view; only Super Admin and Moderator can approve/reject.
require_once '../admin/admin_header.php';
require_once '../config/db.php';

// Handle approve / reject POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if ($_SESSION['csrf_token'] !== ($_POST['csrf_token'] ?? '')) {
        die('Invalid request.');
    }

    $listingId = (int)($_POST['listing_id'] ?? 0);
    $action    = $_POST['action'] ?? '';

    if ($listingId && in_array($action, ['approved', 'rejected'], true)) {
        $pdo->prepare("UPDATE services SET approval_status = ? WHERE id = ?")
            ->execute([$action, $listingId]);
        // Audit log
        $pdo->prepare(
            "INSERT INTO audit_log (admin_id, action, target_type, target_id, notes)
             VALUES (?, ?, 'service', ?, ?)"
        )->execute([
            $_SESSION['user_id'],
            $action === 'approved' ? 'LISTING_APPROVED' : 'LISTING_REJECTED',
            $listingId,
            "Listing ID $listingId set to $action"
        ]);
        header('Location: listings.php?msg=' . $action);
        exit;
    }
}

$filter = $_GET['filter'] ?? 'pending';
$allowed = ['pending','approved','rejected'];
if (!in_array($filter, $allowed, true)) $filter = 'pending';

$stmt = $pdo->prepare("
    SELECT s.*, u.full_name AS worker_name, u.email AS worker_email
    FROM services s
    JOIN users u ON s.worker_id = u.id
    WHERE s.approval_status = ?
    ORDER BY s.created_at DESC
");
$stmt->execute([$filter]);
$listings = $stmt->fetchAll();
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listing Approval — HustleHub Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'admin_nav.php'; ?>

<div class="admin-wrap">
  <div class="container-xl py-4">
    <h2 class="admin-page-title">Service Listings</h2>

    <?php if ($msg === 'approved'): ?>
      <div class="alert alert-success">Listing approved successfully.</div>
    <?php elseif ($msg === 'rejected'): ?>
      <div class="alert alert-warning">Listing rejected.</div>
    <?php endif; ?>

    <!-- Filter tabs -->
    <ul class="nav nav-pills mb-4">
      <?php foreach (['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$label): ?>
        <li class="nav-item">
          <a class="nav-link <?= $filter===$k?'active':'' ?>" href="listings.php?filter=<?= $k ?>">
            <?= $label ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php if (empty($listings)): ?>
      <div class="text-center text-muted py-5">No <?= $filter ?> listings at the moment.</div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($listings as $l): ?>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
              <div class="card-body">
                <span class="badge-cat mb-2"><?= e($l['category']) ?></span>
                <h5 class="card-title" style="font-size:1rem"><?= e($l['title']) ?></h5>
                <p class="text-muted small mb-2"><?= e(substr($l['description'],0,120)) ?>…</p>
                <div class="d-flex justify-content-between mb-1">
                  <span class="small"><strong>Price:</strong> R<?= number_format($l['price'],0) ?></span>
                  <span class="small"><strong>Duration:</strong> <?= (int)$l['duration_hours'] ?>h</span>
                </div>
                <div class="small text-muted">
                  <i class="bi bi-person"></i> <?= e($l['worker_name']) ?><br>
                  <i class="bi bi-envelope"></i> <?= e($l['worker_email']) ?><br>
                  <i class="bi bi-calendar"></i> <?= date('d M Y', strtotime($l['created_at'])) ?>
                </div>
              </div>
              <?php if ($filter === 'pending'): ?>
              <div class="card-footer bg-transparent d-flex gap-2">
                <!-- Approve form -->
                <form method="POST" style="flex:1">
                  <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="listing_id" value="<?= (int)$l['id'] ?>">
                  <input type="hidden" name="action" value="approved">
                  <button type="submit" class="btn btn-success btn-sm w-100">
                    <i class="bi bi-check-lg"></i> Approve
                  </button>
                </form>
                <!-- Reject form -->
                <form method="POST" style="flex:1">
                  <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="listing_id" value="<?= (int)$l['id'] ?>">
                  <input type="hidden" name="action" value="rejected">
                  <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                    <i class="bi bi-x-lg"></i> Reject
                  </button>
                </form>
              </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

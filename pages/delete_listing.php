<?php
// FILE: pages/delete_listing.php — Soft-delete (only if no active bookings)
require_once '../includes/auth.php';
require_once '../config/db.php';
requireRole('worker');

$workerId  = (int)$_SESSION['user_id'];
$serviceId = (int)($_GET['id'] ?? 0);
if (!$serviceId) { header('Location: worker_dashboard.php'); exit; }

// Confirm ownership
$stmt = $pdo->prepare("SELECT id, title FROM services WHERE id = ? AND worker_id = ?");
$stmt->execute([$serviceId, $workerId]);
$service = $stmt->fetch();
if (!$service) { header('Location: worker_dashboard.php?err=not_found'); exit; }

// Block deletion if there are active bookings
$active = $pdo->prepare(
    "SELECT COUNT(*) FROM bookings WHERE service_id = ? AND status IN ('pending','confirmed','in_progress')"
);
$active->execute([$serviceId]);
if ($active->fetchColumn() > 0) {
    header('Location: worker_dashboard.php?err=has_bookings');
    exit;
}

// Confirm step — GET shows confirm page, POST deletes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $pdo->prepare("DELETE FROM services WHERE id = ? AND worker_id = ?")->execute([$serviceId, $workerId]);
    header('Location: worker_dashboard.php?msg=deleted');
    exit;
}

$pageTitle = 'Delete Listing';
include '../includes/header.php';
?>
<main class="container py-5" style="max-width:500px">
  <div class="card p-4 border-danger">
    <h3 class="fw-bold text-danger mb-3">Delete Listing</h3>
    <p>Are you sure you want to permanently delete <strong><?= e($service['title']) ?></strong>?</p>
    <p class="text-muted small">This cannot be undone. Past bookings linked to this listing will remain in history.</p>
    <form method="POST" class="d-flex gap-2">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <button type="submit" class="btn btn-danger">Yes, Delete</button>
      <a href="worker_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
    </form>
  </div>
</main>
<?php include '../includes/footer.php'; ?>

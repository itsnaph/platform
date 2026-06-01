<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

if (!isLoggedIn() || ($_SESSION['role'] !== 'client' && $_SESSION['role'] !== 'worker')) {
    header('Location: /pages/login.php');
    exit;
}

$bookingId = (int)($_GET['id'] ?? 0);
$userId    = (int)$_SESSION['user_id'];
$role      = $_SESSION['role'];

// Fetch booking — must belong to this user
$clause = $role === 'client' ? 'b.client_id = ?' : 'b.worker_id = ?';
$stmt = $pdo->prepare("
    SELECT b.*, s.title, t.escrow_status
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN transactions t ON t.booking_id = b.id
    WHERE b.id = ? AND $clause
");
$stmt->execute([$bookingId, $userId]);
$booking = $stmt->fetch();

if (!$booking) { header('Location: ' . ($role==='client'?'client_dashboard.php':'worker_dashboard.php')); exit; }

// Can only dispute if escrow is still held
if ($booking['escrow_status'] !== 'held') {
    header('Location: ' . ($role==='client'?'client_dashboard.php':'worker_dashboard.php') . '?err=cannot_dispute');
    exit;
}

// Check no existing open dispute
$existing = $pdo->prepare("SELECT id FROM disputes WHERE booking_id = ? AND status IN ('open','under_review')");
$existing->execute([$bookingId]);
if ($existing->fetch()) {
    header('Location: ' . ($role==='client'?'client_dashboard.php':'worker_dashboard.php') . '?err=already_disputed');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $reason = trim($_POST['reason'] ?? '');
    if (strlen($reason) < 20) {
        $error = 'Please describe the issue in at least 20 characters.';
    } else {
        try {
            $pdo->beginTransaction();
            $pdo->prepare(
                "INSERT INTO disputes (booking_id, raised_by, reason, status) VALUES (?,?,?,'open')"
            )->execute([$bookingId, $userId, $reason]);

            $pdo->prepare("UPDATE bookings SET status='disputed' WHERE id=?")->execute([$bookingId]);
            $pdo->commit();

            header('Location: ' . ($role==='client'?'client_dashboard.php':'worker_dashboard.php') . '?msg=disputed');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Could not raise dispute. Please try again.';
        }
    }
}

$pageTitle = 'Raise Dispute';
include '../includes/header.php';
?>
<main class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6">
      <div class="card p-4">
        <h2 class="fw-bold mb-1" style="color:var(--primary)">Raise a Dispute</h2>
        <p class="text-muted small mb-3">Booking: <strong><?= e($booking['title']) ?></strong></p>

        <div class="p-3 mb-3" style="background:#fff3cd;border-radius:8px;font-size:0.875rem">
           A neutral admin will review both sides and decide whether to release or refund the escrow.
          Your R<?= number_format($booking['price'] ?? 0, 0) ?> remains safely held until resolved.
        </div>

        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <div class="mb-3">
            <label class="form-label">Describe the problem *</label>
            <textarea name="reason" id="review-text" class="form-control" rows="5"
                      placeholder="Please explain clearly what went wrong…" required
                      maxlength="500"><?= e($_POST['reason'] ?? '') ?></textarea>
            <div id="char-count" class="mt-1 small">0/500</div>
          </div>
          <button type="submit" class="btn btn-danger w-100 fw-bold">Submit Dispute</button>
        </form>

        <p class="text-muted text-center mt-3 small">
          <a href="<?= $role==='client'?'client_dashboard.php':'worker_dashboard.php' ?>">← Back to dashboard</a>
        </p>
      </div>
    </div>
  </div>
</main>
<?php include '../includes/footer.php'; ?>

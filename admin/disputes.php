<?php
// FILE: admin/disputes.php — Dispute Resolution Panel
// BOTH admin AND moderator can access this page.
// Only escrow release/refund is performed here (not arbitrary status changes).
require_once '../admin/admin_header.php';
require_once '../config/db.php';

$msg = '';

// Handle dispute resolution POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['csrf_token'] !== ($_POST['csrf_token'] ?? '')) {
        die('Invalid request.');
    }

    $disputeId      = (int)($_POST['dispute_id'] ?? 0);
    $action         = $_POST['action'] ?? '';          // 'release' or 'refund'
    $resolutionNote = trim($_POST['resolution_note'] ?? '');

    // Server-side: resolution note is REQUIRED
    if (empty($resolutionNote)) {
        $msg = 'error_note';
    } elseif (!$disputeId || !in_array($action, ['release','refund'], true)) {
        $msg = 'error_invalid';
    } else {
        // Fetch dispute + booking + transaction
        $dispute = $pdo->prepare("
            SELECT d.*, t.escrow_status, t.id AS tx_id, b.id AS booking_id
            FROM disputes d
            JOIN bookings b ON d.booking_id = b.id
            JOIN transactions t ON t.booking_id = b.id
            WHERE d.id = ? AND d.status != 'resolved'
        ");
        $dispute->execute([$disputeId]);
        $row = $dispute->fetch();

        if (!$row) {
            $msg = 'error_not_found';
        } elseif ($row['escrow_status'] !== 'held') {
            // Cannot act on already-released/refunded escrow
            $msg = 'error_escrow';
        } else {
            $newEscrow  = $action === 'release' ? 'released' : 'refunded';
            $newBooking = $action === 'release' ? 'completed' : 'cancelled';

            try {
                $pdo->beginTransaction();

                // 1. Update transaction escrow status
                $pdo->prepare("UPDATE transactions SET escrow_status = ?, released_by = ?, released_at = NOW() WHERE id = ?")
                    ->execute([$newEscrow, $_SESSION['user_id'], $row['tx_id']]);

                // 2. Update booking status
                $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")
                    ->execute([$newBooking, $row['booking_id']]);

                // 3. Resolve dispute with note
                $pdo->prepare("
                    UPDATE disputes
                    SET status = 'resolved', admin_id = ?, resolution_note = ?, resolved_at = NOW()
                    WHERE id = ?
                ")->execute([$_SESSION['user_id'], $resolutionNote, $disputeId]);

                // 4. Audit log
                $pdo->prepare("
                    INSERT INTO audit_log (admin_id, action, target_type, target_id, notes)
                    VALUES (?, ?, 'dispute', ?, ?)
                ")->execute([
                    $_SESSION['user_id'],
                    strtoupper('DISPUTE_' . $newEscrow),
                    $disputeId,
                    "Escrow $newEscrow. Note: $resolutionNote"
                ]);

                $pdo->commit();
                $msg = 'success_' . $action;

            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log('Dispute resolution error: ' . $e->getMessage());
                $msg = 'error_db';
            }
        }
    }
}

// Load disputes
$filter  = $_GET['filter'] ?? 'open';
$allowed = ['open','under_review','resolved'];
if (!in_array($filter, $allowed, true)) $filter = 'open';

$disputes = $pdo->prepare("
    SELECT d.*,
           b.status AS booking_status, b.booking_date,
           s.title AS service_title, s.price,
           cl.full_name AS client_name, cl.email AS client_email,
           wk.full_name AS worker_name, wk.email AS worker_email,
           t.escrow_status, t.amount,
           adm.full_name AS resolved_by_name
    FROM disputes d
    JOIN bookings b ON d.booking_id = b.id
    JOIN services s ON b.service_id = s.id
    JOIN users cl  ON b.client_id  = cl.id
    JOIN users wk  ON b.worker_id  = wk.id
    JOIN transactions t ON t.booking_id = b.id
    LEFT JOIN users adm ON d.admin_id = adm.id
    WHERE d.status = ?
    ORDER BY d.created_at DESC
");
$disputes->execute([$filter]);
$rows = $disputes->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Disputes — HustleHub Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include 'admin_nav.php'; ?>

<div class="admin-wrap">
  <div class="container-xl py-4">
    <h2 class="admin-page-title">Dispute Resolution</h2>
    <p class="text-muted small mb-4">
      As <?= e($adminRole) ?>, you can release or refund escrow for open disputes.
      A resolution note is <strong>required</strong> before any decision can be saved.
    </p>

    <!-- Flash messages -->
    <?php if (str_starts_with($msg, 'success')): ?>
      <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>
        Dispute resolved. Escrow has been <?= $msg === 'success_release' ? 'released to the worker.' : 'refunded to the client.' ?>
      </div>
    <?php elseif ($msg === 'error_note'): ?>
      <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>A resolution note is required.</div>
    <?php elseif ($msg === 'error_escrow'): ?>
      <div class="alert alert-warning">Escrow has already been processed for this dispute.</div>
    <?php elseif (str_starts_with($msg, 'error')): ?>
      <div class="alert alert-danger">An error occurred. Please try again.</div>
    <?php endif; ?>

    <!-- Filter tabs -->
    <ul class="nav nav-pills mb-4">
      <?php foreach (['open'=>'Open','under_review'=>'Under Review','resolved'=>'Resolved'] as $k=>$label): ?>
        <li class="nav-item">
          <a class="nav-link <?= $filter===$k?'active':'' ?>" href="disputes.php?filter=<?= $k ?>">
            <?= $label ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php if (empty($rows)): ?>
      <div class="text-center text-muted py-5">
        <i class="bi bi-shield-check" style="font-size:3rem;opacity:0.3"></i>
        <p class="mt-2">No <?= $filter ?> disputes.</p>
      </div>
    <?php else: ?>
      <?php foreach ($rows as $d): ?>
      <div class="card mb-4 shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center"
             style="background:#f8f9fa;border-bottom:2px solid #0A2342">
          <div>
            <strong>Dispute #<?= $d['id'] ?></strong>
            <span class="badge bg-danger ms-2"><?= e($d['status']) ?></span>
            &nbsp;|&nbsp;
            <span class="small text-muted">Booking #<?= $d['booking_id'] ?> — <?= e($d['service_title']) ?></span>
          </div>
          <span class="small text-muted"><?= date('d M Y H:i', strtotime($d['created_at'])) ?></span>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <!-- Dispute detail -->
            <div class="col-12 col-md-6">
              <h6 class="fw-bold" style="color:#0A2342">Dispute Details</h6>
              <table class="table table-sm table-borderless small mb-0">
                <tr><td class="text-muted">Client</td><td><?= e($d['client_name']) ?> (<?= e($d['client_email']) ?>)</td></tr>
                <tr><td class="text-muted">Worker</td><td><?= e($d['worker_name']) ?> (<?= e($d['worker_email']) ?>)</td></tr>
                <tr><td class="text-muted">Booking Date</td><td><?= e($d['booking_date']) ?></td></tr>
                <tr><td class="text-muted">Service Price</td><td class="fw-bold" style="color:#FF6B35">R<?= number_format($d['price'],2) ?></td></tr>
                <tr>
                  <td class="text-muted">Escrow Status</td>
                  <td>
                    <span class="badge <?= $d['escrow_status']==='held'?'bg-warning text-dark':($d['escrow_status']==='released'?'bg-success':'bg-info') ?>">
                      <?= ucfirst($d['escrow_status']) ?>
                    </span>
                  </td>
                </tr>
              </table>
              <div class="mt-2 p-2 rounded" style="background:#fff3cd">
                <strong class="small">Reason raised:</strong>
                <p class="small mb-0 mt-1"><?= nl2br(e($d['reason'])) ?></p>
              </div>
            </div>

            <!-- Resolution panel -->
            <div class="col-12 col-md-6">
              <?php if ($d['status'] !== 'resolved' && $d['escrow_status'] === 'held'): ?>
              <h6 class="fw-bold" style="color:#0A2342">Admin Decision</h6>
              <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="dispute_id" value="<?= (int)$d['id'] ?>">

                <div class="mb-3">
                  <label class="form-label small fw-semibold">Resolution Note <span class="text-danger">*</span></label>
                  <textarea name="resolution_note" class="form-control form-control-sm" rows="4"
                            placeholder="Describe your decision and reasoning (required)…"
                            required minlength="10"></textarea>
                  <div class="form-text">This note is permanently saved to the audit log.</div>
                </div>

                <div class="d-flex gap-2">
                  <!-- Release = worker receives payment -->
                  <button type="submit" name="action" value="release"
                          class="btn btn-success btn-sm flex-fill"
                          onclick="return confirm('Release escrow to the worker? This cannot be undone.')">
                    <i class="bi bi-check-circle me-1"></i> Release to Worker
                  </button>
                  <!-- Refund = client gets money back -->
                  <button type="submit" name="action" value="refund"
                          class="btn btn-outline-danger btn-sm flex-fill"
                          onclick="return confirm('Refund escrow to the client? This cannot be undone.')">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Refund to Client
                  </button>
                </div>
              </form>

              <?php else: ?>
              <h6 class="fw-bold text-success"><i class="bi bi-check-circle me-1"></i>Resolved</h6>
              <p class="small text-muted">Resolved by: <strong><?= e($d['resolved_by_name'] ?? 'Admin') ?></strong>
                on <?= $d['resolved_at'] ? date('d M Y', strtotime($d['resolved_at'])) : 'N/A' ?></p>
              <div class="p-2 rounded" style="background:#d1e7dd">
                <strong class="small">Resolution Note:</strong>
                <p class="small mb-0 mt-1"><?= nl2br(e($d['resolution_note'] ?? '')) ?></p>
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

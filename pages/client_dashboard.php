<?php
// FILE: pages/client_dashboard.php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireRole('client');

$clientId = (int)$_SESSION['user_id'];

// Fetch all bookings for this client
$stmt = $pdo->prepare("
    SELECT b.*, s.title, s.category, s.price,
           u.full_name AS worker_name, t.escrow_status
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.worker_id = u.id
    LEFT JOIN transactions t ON t.booking_id = b.id
    WHERE b.client_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$clientId]);
$bookings = $stmt->fetchAll();

$pageTitle = 'My Bookings';
include '../includes/header.php';
?>
<main class="container py-4">
  <h2 class="fw-bold mb-4" style="color:var(--primary)">My Bookings</h2>

  <?php if (empty($bookings)): ?>
    <div class="text-center py-5 text-muted">
      <p>No bookings yet. <a href="browse.php">Browse services</a> to get started.</p>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle bg-white rounded shadow-sm">
        <thead style="background:var(--primary);color:#fff">
          <tr>
            <th>Service</th>
            <th>Worker</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Escrow</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
          <tr id="booking-row-<?= $b['id'] ?>">
            <td>
              <div class="fw-semibold small"><?= e($b['title']) ?></div>
              <div class="badge-cat" style="font-size:0.7rem"><?= e($b['category']) ?></div>
            </td>
            <td class="small"><?= e($b['worker_name']) ?></td>
            <td class="small"><?= e($b['booking_date']) ?></td>
            <td class="fw-bold small" style="color:var(--accent)">R<?= number_format($b['price'],0) ?></td>
            <td class="status-cell">
              <span class="badge-<?= e($b['status']) ?>"><?= ucfirst(str_replace('_',' ',$b['status'])) ?></span>
            </td>
            <td class="small">
              <?php if ($b['escrow_status']): ?>
                <span style="color:<?= $b['escrow_status']==='held'?'#856404':($b['escrow_status']==='released'?'#155724':'#721c24') ?>">
                  <?= ucfirst($b['escrow_status']) ?>
                </span>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td class="action-cell">
              <?php if ($b['status'] === 'in_progress'): ?>
                <button class="btn btn-sm btn-primary btn-confirm-complete me-1"
                        data-booking-id="<?= $b['id'] ?>">Confirm Complete</button>
                <a href="raise_dispute.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-outline-danger">Dispute</a>

              <?php elseif ($b['status'] === 'completed'): ?>
                <?php
                // Check if review already left
                $rv = $pdo->prepare("SELECT id FROM reviews WHERE booking_id=? AND reviewer_id=?");
                $rv->execute([$b['id'], $clientId]);
                ?>
                <?php if (!$rv->fetch()): ?>
                  <a href="leave_review.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-navy">Leave Review</a>
                <?php else: ?>
                  <span class="text-muted small">Reviewed ✓</span>
                <?php endif; ?>

              <?php elseif ($b['status'] === 'disputed'): ?>
                <span class="text-danger small">Under review</span>

              <?php elseif ($b['status'] === 'pending'): ?>
                <span class="text-muted small">Awaiting worker</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

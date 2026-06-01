<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireRole('worker');

$workerId = (int)$_SESSION['user_id'];

// Stats
$stats = $pdo->prepare("
    SELECT
      COUNT(*) AS total,
      SUM(status='completed') AS completed,
      SUM(status IN ('pending','confirmed','in_progress')) AS active,
      SUM(status='disputed') AS disputed
    FROM bookings WHERE worker_id = ?
");
$stats->execute([$workerId]);
$s = $stats->fetch();

// Active bookings
$stmt = $pdo->prepare("
    SELECT b.*, sv.title, sv.price, u.full_name AS client_name, t.escrow_status
    FROM bookings b
    JOIN services sv ON b.service_id = sv.id
    JOIN users u ON b.client_id = u.id
    LEFT JOIN transactions t ON t.booking_id = b.id
    WHERE b.worker_id = ? AND b.status NOT IN ('completed','cancelled')
    ORDER BY b.booking_date ASC
");
$stmt->execute([$workerId]);
$bookings = $stmt->fetchAll();

// My listings
$listings = $pdo->prepare(
    "SELECT * FROM services WHERE worker_id = ? ORDER BY created_at DESC"
);
$listings->execute([$workerId]);
$myListings = $listings->fetchAll();

$pageTitle = 'Worker Dashboard';
include '../includes/header.php';
?>
<main class="container py-4">
  <h2 class="fw-bold mb-4" style="color:var(--primary)">Worker Dashboard</h2>

  <!-- Stats row -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-number"><?= (int)$s['total'] ?></div><div class="stat-label">Total Bookings</div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-number"><?= (int)$s['completed'] ?></div><div class="stat-label">Completed</div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card"><div class="stat-number"><?= (int)$s['active'] ?></div><div class="stat-label">Active</div></div></div>
    <div class="col-6 col-md-3"><div class="stat-card" style="border-color:#dc3545"><div class="stat-number" style="color:#dc3545"><?= (int)$s['disputed'] ?></div><div class="stat-label">Disputed</div></div></div>
  </div>

  <div class="row g-4">
    <!-- Active Bookings -->
    <div class="col-12 col-lg-7">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="color:var(--primary)">Active Bookings</h5>
      </div>
      <?php if (empty($bookings)): ?>
        <p class="text-muted small">No active bookings right now.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle bg-white rounded shadow-sm small">
            <thead style="background:var(--primary);color:#fff">
              <tr><th>Service</th><th>Client</th><th>Date</th><th>Amount</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $b): ?>
              <tr id="booking-row-<?= $b['id'] ?>">
                <td><?= e($b['title']) ?></td>
                <td><?= e($b['client_name']) ?></td>
                <td><?= e($b['booking_date']) ?></td>
                <td style="color:var(--accent);font-weight:700">R<?= number_format($b['price'],0) ?></td>
                <td class="status-cell"><span class="badge-<?= e($b['status']) ?>"><?= ucfirst(str_replace('_',' ',$b['status'])) ?></span></td>
                <td class="action-cell">
                  <?php if ($b['status'] === 'pending'): ?>
                    <button class="btn btn-sm btn-primary btn-accept-booking"
                            data-booking-id="<?= $b['id'] ?>">Accept</button>
                  <?php elseif ($b['status'] === 'confirmed'): ?>
                    <button class="btn btn-sm btn-navy btn-start-job"
                            data-booking-id="<?= $b['id'] ?>">Start Job</button>
                  <?php elseif ($b['status'] === 'in_progress'): ?>
                    <span class="text-muted small">Awaiting client confirmation</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- My Listings -->
    <div class="col-12 col-lg-5">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0" style="color:var(--primary)">My Listings</h5>
        <a href="create_listing.php" class="btn btn-sm btn-primary">+ New Listing</a>
      </div>
      <?php foreach ($myListings as $l): ?>
      <div class="card mb-2 p-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-semibold small"><?= e($l['title']) ?></div>
            <span class="badge-cat" style="font-size:0.7rem"><?= e($l['category']) ?></span>
            <span class="ms-2" style="font-size:0.75rem;color:var(--accent);font-weight:700">R<?= number_format($l['price'],0) ?></span>
          </div>
          <div>
            <?php $ac = $l['approval_status']==='approved'?'badge-completed':($l['approval_status']==='rejected'?'badge-disputed':'badge-pending'); ?>
            <span class="<?= $ac ?>" style="font-size:0.7rem"><?= ucfirst($l['approval_status']) ?></span>
          </div>
        </div>
        <div class="mt-2 d-flex gap-2">
          <a href="edit_listing.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-secondary" style="font-size:0.75rem">Edit</a>
          <a href="delete_listing.php?id=<?= $l['id'] ?>" class="btn btn-sm btn-outline-danger btn-danger-confirm"
             data-confirm="Delete this listing permanently?" style="font-size:0.75rem">Delete</a>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($myListings)): ?>
        <p class="text-muted small">No listings yet. <a href="create_listing.php">Create your first service</a>.</p>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php include '../includes/footer.php'; ?>

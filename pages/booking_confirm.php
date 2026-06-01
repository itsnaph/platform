<?php
// FILE: pages/booking_confirm.php — Shown after PayFast return_url
require_once '../includes/auth.php';
require_once '../config/db.php';

requireRole('client');

$bookingId = (int)($_GET['id'] ?? 0);
if (!$bookingId) { header('Location: client_dashboard.php'); exit; }

// Fetch booking for this client only
$stmt = $pdo->prepare("
    SELECT b.*, s.title, s.price, s.category, u.full_name AS worker_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.worker_id = u.id
    WHERE b.id = ? AND b.client_id = ?
");
$stmt->execute([$bookingId, $_SESSION['user_id']]);
$booking = $stmt->fetch();

if (!$booking) { header('Location: client_dashboard.php'); exit; }

$pageTitle = 'Booking Confirmed';
include '../includes/header.php';
?>
<main class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6">
      <div class="card text-center p-4">
        <div style="font-size:3rem;margin-bottom:1rem"></div>
        <h2 class="fw-bold" style="color:var(--primary)">Booking Confirmed!</h2>
        <p class="text-muted">Your payment is safely held in escrow.</p>

        <div class="escrow-box my-4 text-start">
          <div class="small mb-1 fw-bold">Escrow status</div>
          <div class="escrow-status"> HELD — Payment secured</div>
          <div class="small mt-2" style="opacity:0.85">
            Funds will only be released to <?= e($booking['worker_name']) ?> when you confirm the job is complete.
          </div>
        </div>

        <table class="table table-sm text-start">
          <tr><td class="text-muted">Service</td><td class="fw-semibold"><?= e($booking['title']) ?></td></tr>
          <tr><td class="text-muted">Worker</td><td><?= e($booking['worker_name']) ?></td></tr>
          <tr><td class="text-muted">Date</td><td><?= e($booking['booking_date']) ?></td></tr>
          <tr><td class="text-muted">Amount</td><td class="fw-bold" style="color:var(--accent)">R<?= number_format($booking['price'],0) ?></td></tr>
          <tr><td class="text-muted">Status</td><td><span class="badge-<?= e($booking['status']) ?>"><?= ucfirst($booking['status']) ?></span></td></tr>
        </table>

        <a href="client_dashboard.php" class="btn btn-primary w-100 mt-2">View My Bookings</a>
      </div>
    </div>
  </div>
</main>
<?php include '../includes/footer.php'; ?>

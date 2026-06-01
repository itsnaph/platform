<?php
// FILE: pages/booking_cancel.php — PayFast cancel URL handler
// Called when user clicks "Cancel" on the PayFast payment page.
// The booking row was created before redirect; we cancel it here.
require_once '../includes/auth.php';
require_once '../config/db.php';

$bookingId = (int)($_GET['id'] ?? 0);

if ($bookingId && isLoggedIn()) {
    $clientId = (int)$_SESSION['user_id'];
    // Only cancel if still pending and belongs to this client
    $pdo->prepare(
        "UPDATE bookings SET status='cancelled' WHERE id=? AND client_id=? AND status='pending'"
    )->execute([$bookingId, $clientId]);
    // Also delete the held transaction row to keep data clean
    $pdo->prepare(
        "DELETE FROM transactions WHERE booking_id=? AND escrow_status='held'"
    )->execute([$bookingId]);
}

$pageTitle = 'Payment Cancelled';
include '../includes/header.php';
?>
<main class="container py-5 text-center" style="max-width:480px">
  <div class="card p-5 border-0 shadow-sm">
    <h2 class="fw-bold mb-2" style="color:var(--primary)">Payment Cancelled</h2>
    <p class="text-muted">You cancelled the PayFast payment. Your booking was not confirmed and no money was taken.</p>
    <a href="browse.php" class="btn btn-primary mt-3">Browse Services Again</a>
  </div>
</main>
<?php include '../includes/footer.php'; ?>

<?php
// Creates the booking record and transaction, then sends the user to PayFast to pay.
// PayFast sandbox used for academic testing; switch to live credentials for production.

require_once '../includes/auth.php';
require_once '../config/db.php';

requireRole('client');
verifyCsrfToken();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: browse.php');
    exit;
}

$serviceId   = (int)$_POST['service_id'];
$bookingDate = $_POST['booking_date'] ?? '';
$notes       = trim($_POST['notes'] ?? '');
$clientId    = (int)$_SESSION['user_id'];

// Validate date
if (!$bookingDate || $bookingDate < date('Y-m-d', strtotime('+1 day'))) {
    header('Location: service_detail.php?id=' . $serviceId . '&err=invalid_date');
    exit;
}

// Fetch approved service
$stmt = $pdo->prepare(
    "SELECT worker_id, price, title FROM services WHERE id = ? AND approval_status = 'approved'"
);
$stmt->execute([$serviceId]);
$service = $stmt->fetch();
if (!$service) {
    header('Location: browse.php?err=not_found');
    exit;
}

// Duplicate booking guard
$dup = $pdo->prepare(
    "SELECT COUNT(*) FROM bookings WHERE client_id = ? AND service_id = ?
     AND status IN ('pending','confirmed','in_progress')"
);
$dup->execute([$clientId, $serviceId]);
if ($dup->fetchColumn() > 0) {
    header('Location: service_detail.php?id=' . $serviceId . '&msg=duplicate');
    exit;
}

//Atomic insert: booking + escrow transaction
try {
    $pdo->beginTransaction();

    $b = $pdo->prepare(
        "INSERT INTO bookings (service_id, client_id, worker_id, booking_date, notes, status)
         VALUES (?, ?, ?, ?, ?, 'pending')"
    );
    $b->execute([$serviceId, $clientId, $service['worker_id'], $bookingDate, $notes]);
    $bookingId = (int)$pdo->lastInsertId();

    $t = $pdo->prepare(
        "INSERT INTO transactions (booking_id, amount, escrow_status)
         VALUES (?, ?, 'held')"
    );
    $t->execute([$bookingId, $service['price']]);

    $pdo->commit();

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Booking failed: ' . $e->getMessage());
    header('Location: service_detail.php?id=' . $serviceId . '&err=server');
    exit;
}

//Build PayFast form and auto-submit
// PayFast merchant credentials (use sandbox for testing)
$pfMerchantId  = '10000100';       // Replace with real merchant ID
$pfMerchantKey = '46f0cd694581a';  // Replace with real merchant key
$pfPassPhrase  = 'jt7NOE43FZPn';   // Replace with real passphrase
$pfSandbox     = true;             // Set to false for production

$baseUrl = 'http://' . $_SERVER['HTTP_HOST'];

$pfData = [
    'merchant_id'   => $pfMerchantId,
    'merchant_key'  => $pfMerchantKey,
    'return_url'    => $baseUrl . '/pages/booking_confirm.php?id=' . $bookingId,
    'cancel_url'    => $baseUrl . '/pages/booking_cancel.php?id=' . $bookingId,
    'notify_url'    => $baseUrl . '/pages/payfast_notify.php',
    'name_first'    => $_SESSION['user_name'] ?? 'Client',
    'email_address' => $_SESSION['user_email'] ?? '',
    'm_payment_id'  => $bookingId,
    'amount'        => number_format($service['price'], 2, '.', ''),
    'item_name'     => 'HustleHub: ' . substr($service['title'], 0, 100),
    'custom_int1'   => $bookingId,
];

// Generate PayFast signature
$pfString = '';
foreach ($pfData as $key => $value) {
    if ($value !== '') {
        $pfString .= $key . '=' . urlencode(trim($value)) . '&';
    }
}
$pfString = rtrim($pfString, '&');
if ($pfPassPhrase !== '') {
    $pfString .= '&passphrase=' . urlencode(trim($pfPassPhrase));
}
$pfData['signature'] = md5($pfString);

$pfHost = $pfSandbox ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Redirecting to PayFast…</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh;background:#f4f6f9">
<div class="text-center p-5">
  <div class="spinner-border text-primary mb-3" role="status"></div>
  <h4>Redirecting to PayFast…</h4>
  <p class="text-muted small">Please do not close this window.</p>
  <form id="pf-form" action="https://<?= $pfHost ?>/eng/process" method="POST">
    <?php foreach ($pfData as $key => $value): ?>
      <input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($value) ?>">
    <?php endforeach; ?>
    <noscript>
      <button type="submit" class="btn btn-primary mt-3">Continue to payment</button>
    </noscript>
  </form>
</div>
<script>document.getElementById('pf-form').submit();</script>
</body>
</html>

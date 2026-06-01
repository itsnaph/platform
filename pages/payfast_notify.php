<?php
// FILE: pages/payfast_notify.php
// PayFast ITN (Instant Transaction Notification) handler.
// PayFast posts to this URL after successful payment.
// This is server-to-server — no session available.

require_once '../config/db.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request.');
}

$pfPassPhrase  = 'jt7NOE43FZPn';   // Same as process_booking.php
$pfSandbox     = true;

// Read posted data
$pfData = [];
foreach ($_POST as $key => $value) {
    $pfData[$key] = stripslashes($value);
}

// Verify signature
$pfParamString = '';
foreach ($pfData as $key => $value) {
    if ($key !== 'signature') {
        $pfParamString .= $key . '=' . urlencode($value) . '&';
    }
}
$pfParamString = rtrim($pfParamString, '&');
if ($pfPassPhrase !== '') {
    $pfParamString .= '&passphrase=' . urlencode($pfPassPhrase);
}
$signature = md5($pfParamString);

if ($signature !== $pfData['signature']) {
    error_log('PayFast notify: signature mismatch');
    die('Invalid signature.');
}

// Verify payment status and amount
$bookingId     = (int)($pfData['custom_int1'] ?? 0);
$paymentStatus = $pfData['payment_status'] ?? '';
$amountGross   = (float)($pfData['amount_gross'] ?? 0);

if ($paymentStatus !== 'COMPLETE' || !$bookingId) {
    error_log('PayFast notify: incomplete payment or no booking ID');
    exit;
}

// Verify amount matches transaction record
$stmt = $pdo->prepare("SELECT amount FROM transactions WHERE booking_id = ?");
$stmt->execute([$bookingId]);
$tx = $stmt->fetch();

if (!$tx || round($tx['amount'], 2) != round($amountGross, 2)) {
    error_log("PayFast notify: amount mismatch booking $bookingId — expected {$tx['amount']}, got $amountGross");
    exit;
}

// Update booking status to confirmed + mark payment verified
try {
    $pdo->beginTransaction();

    $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ? AND status = 'pending'")
        ->execute([$bookingId]);

    // Store PayFast transaction ID for audit
    $pfTxId = $pfData['pf_payment_id'] ?? '';
    $pdo->prepare("UPDATE transactions SET payfast_id = ? WHERE booking_id = ?")
        ->execute([$pfTxId, $bookingId]);

    $pdo->commit();
    error_log("PayFast notify: booking $bookingId confirmed, pf_id=$pfTxId");

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('PayFast notify DB error: ' . $e->getMessage());
    http_response_code(500);
    exit;
}

http_response_code(200);
echo 'OK';

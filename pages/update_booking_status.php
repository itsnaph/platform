<?php
// FILE: pages/update_booking_status.php
// Ajax endpoint — returns JSON. Called by app.js for status transitions.
require_once '../includes/auth.php';
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit;
}

verifyCsrfToken();

$bookingId = (int)($_POST['booking_id'] ?? 0);
$newStatus = $_POST['new_status'] ?? '';
$userId    = (int)$_SESSION['user_id'];
$role      = $_SESSION['role'] ?? '';

//Check this role is allowed to make this specific transition
$allowed = false;
$expectedCurrent = '';

if ($role === 'client' && $newStatus === 'completed') {
    $allowed = true;
    $expectedCurrent = 'in_progress';
} elseif ($role === 'worker' && $newStatus === 'confirmed') {
    $allowed = true;
    $expectedCurrent = 'pending';
} elseif ($role === 'worker' && $newStatus === 'in_progress') {
    $allowed = true;
    $expectedCurrent = 'confirmed';
}

if (!$allowed) {
    echo json_encode(['success'=>false,'message'=>'Transition not permitted']); exit;
}

//Fetch booking and check ownership
if ($role === 'client') {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id=? AND client_id=?");
    $stmt->execute([$bookingId, $userId]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id=? AND worker_id=?");
    $stmt->execute([$bookingId, $userId]);
}
$booking = $stmt->fetch();
if (!$booking) {
    echo json_encode(['success'=>false,'message'=>'Booking not found']); exit;
}

//Check the booking is currently in the expected state
if ($booking['status'] !== $expectedCurrent) {
    echo json_encode(['success'=>false,'message'=>'Cannot update from current status']); exit;
}

// Perform transition
try {
    $pdo->beginTransaction();

    $upd = $pdo->prepare("UPDATE bookings SET status=? WHERE id=?");
    $upd->execute([$newStatus, $bookingId]);

    // If client confirms complete → release escrow
    if ($newStatus === 'completed') {
        $pdo->prepare(
            "UPDATE transactions SET escrow_status='released', released_by=?, released_at=NOW()
             WHERE booking_id=? AND escrow_status='held'"
        )->execute([$userId, $bookingId]);
    }

    $pdo->commit();
    echo json_encode(['success'=>true]);

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log('Status update failed: ' . $e->getMessage());
    echo json_encode(['success'=>false,'message'=>'Server error']);
}

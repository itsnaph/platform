<?php
// leave_review.php — lets both client and worker leave a star rating
require_once '../includes/auth.php';
require_once '../config/db.php';

if (!isLoggedIn() || ($_SESSION['role'] !== 'client' && $_SESSION['role'] !== 'worker')) {
    header('Location: /pages/login.php');
    exit;
}

$bookingId = (int)($_GET['id'] ?? 0);
$userId    = (int)$_SESSION['user_id'];

// Fetch booking and confirm it's completed and user is a party
$stmt = $pdo->prepare("
    SELECT b.*, s.title, s.worker_id,
           cl.full_name AS client_name, wk.full_name AS worker_name
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users cl ON b.client_id = cl.id
    JOIN users wk ON b.worker_id = wk.id
    WHERE b.id = ? AND b.status = 'completed'
    AND (b.client_id = ? OR b.worker_id = ?)
");
$stmt->execute([$bookingId, $userId, $userId]);
$booking = $stmt->fetch();
if (!$booking) {
    $back = $_SESSION['role'] === 'client' ? 'client_dashboard.php' : 'worker_dashboard.php';
    header('Location: ' . $back);
    exit;
}

// Check if already reviewed
$already = $pdo->prepare("SELECT id FROM reviews WHERE booking_id = ? AND reviewer_id = ?");
$already->execute([$bookingId, $userId]);
if ($already->fetch()) {
    header('Location: client_dashboard.php?msg=already_reviewed');
    exit;
}

// Determine who is being reviewed
$revieweeId = ($userId === (int)$booking['client_id']) ? $booking['worker_id'] : $booking['client_id'];
$revieweeName = ($userId === (int)$booking['client_id']) ? $booking['worker_name'] : $booking['client_name'];

$error = '';

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $rating  = (int)($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating from 1 to 5 stars.';
    } else {
        $pdo->prepare("
            INSERT INTO reviews (booking_id, reviewer_id, reviewee_id, rating, comment)
            VALUES (?, ?, ?, ?, ?)
        ")->execute([$bookingId, $userId, $revieweeId, $rating, $comment]);

        // Recalculate avg_rating for reviewee
        $avg = $pdo->prepare("SELECT AVG(rating) FROM reviews WHERE reviewee_id = ?");
        $avg->execute([$revieweeId]);
        $pdo->prepare("UPDATE users SET avg_rating = ? WHERE id = ?")
            ->execute([$avg->fetchColumn(), $revieweeId]);

        $redirect = $_SESSION['role'] === 'client' ? 'client_dashboard.php' : 'worker_dashboard.php';
        header("Location: $redirect?msg=reviewed");
        exit;
    }
}

$pageTitle = 'Leave a Review';
include '../includes/header.php';
?>
<main class="container py-4" style="max-width:560px">
  <h2 class="fw-bold mb-1" style="color:var(--primary)">Leave a Review</h2>
  <p class="text-muted small mb-4">
    Reviewing your experience for: <strong><?= e($booking['title']) ?></strong><br>
    Reviewing: <strong><?= e($revieweeName) ?></strong>
  </p>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm border-0 p-4">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

      <!-- Star rating -->
      <div class="mb-4">
        <label class="form-label fw-semibold">Your Rating <span class="text-danger">*</span></label>
        <div class="star-picker d-flex gap-2" id="star-picker">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>"
                   class="d-none" <?= isset($_POST['rating']) && (int)$_POST['rating'] === $i ? 'checked' : '' ?>>
            <label for="star<?= $i ?>" class="star-label" style="font-size:2rem;cursor:pointer;color:#dee2e6;transition:color 0.15s">
              ★
            </label>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Comment -->
      <div class="mb-3">
        <label for="comment" class="form-label fw-semibold">Your Comment <span class="text-muted small">(optional)</span></label>
        <textarea name="comment" id="review-text" class="form-control" rows="4"
                  maxlength="500"
                  placeholder="Tell others about your experience…"><?= e($_POST['comment'] ?? '') ?></textarea>
        <div class="d-flex justify-content-end mt-1">
          <small id="char-count" class="text-muted">0/500</small>
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">Submit Review</button>
        <a href="<?= $_SESSION['role']==='client'?'client_dashboard.php':'worker_dashboard.php' ?>"
           class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</main>

<?php include '../includes/footer.php'; ?>

<style>
.star-label:hover,
.star-label:hover ~ .star-label { color: #FF6B35; }
#star-picker:has(input:checked) .star-label { color: #dee2e6; }
#star1:checked ~ .star-label:nth-of-type(n+1),
#star2:checked ~ .star-label:nth-of-type(n+2),
#star3:checked ~ .star-label:nth-of-type(n+3),
#star4:checked ~ .star-label:nth-of-type(n+4),
#star5:checked ~ .star-label { color: #FF6B35; }
label[for="star1"] { order:1; }
label[for="star2"] { order:2; }
label[for="star3"] { order:3; }
label[for="star4"] { order:4; }
label[for="star5"] { order:5; }
</style>

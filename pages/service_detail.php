<?php
// FILE: pages/service_detail.php — View service + booking form with PayFast
require_once '../includes/auth.php';
require_once '../config/db.php';

$serviceId = (int)($_GET['id'] ?? 0);
if (!$serviceId) { header('Location: browse.php'); exit; }

// Fetch service + worker
$stmt = $pdo->prepare("
    SELECT s.*, u.full_name, u.avg_rating, u.bio, u.profile_pic
    FROM services s
    JOIN users u ON s.worker_id = u.id
    WHERE s.id = ? AND s.approval_status = 'approved'
");
$stmt->execute([$serviceId]);
$service = $stmt->fetch();
if (!$service) { header('Location: browse.php?err=not_found'); exit; }

// Fetch reviews for this worker
$revStmt = $pdo->prepare("
    SELECT r.rating, r.comment, r.created_at, u.full_name AS reviewer_name
    FROM reviews r
    JOIN users u ON r.reviewer_id = u.id
    WHERE r.reviewee_id = ?
    ORDER BY r.created_at DESC LIMIT 5
");
$revStmt->execute([$service['worker_id']]);
$reviews = $revStmt->fetchAll();

// Flash message
$msg = $_GET['msg'] ?? '';

$pageTitle = e($service['title']);
include '../includes/header.php';
?>

<main class="container py-4">
  <?php if ($msg === 'booked'): ?>
    <div class="alert alert-success alert-dismissible">Booking confirmed! Payment is held in escrow.</div>
  <?php elseif ($msg === 'duplicate'): ?>
    <div class="alert alert-warning alert-dismissible">You already have an active booking for this service.</div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- LEFT: service info -->
    <div class="col-12 col-lg-7">
      <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item"><a href="browse.php">Browse</a></li>
          <li class="breadcrumb-item active"><?= e($service['title']) ?></li>
        </ol>
      </nav>

      <?php if (!empty($service['image_path'])): ?>
        <img src="/<?= e($service['image_path']) ?>" alt="<?= e($service['title']) ?>"
             style="width:100%;max-height:320px;object-fit:cover;border-radius:12px;margin-bottom:1.25rem">
      <?php endif; ?>
      <span class="badge-cat mb-2"><?= e($service['category']) ?></span>
      <h1 class="h2 fw-bold mb-3" style="color:var(--primary)"><?= e($service['title']) ?></h1>
      <p><?= nl2br(e($service['description'])) ?></p>

      <div class="d-flex align-items-center gap-3 my-3 p-3" style="background:#f8f9fa;border-radius:12px">
        <div class="worker-avatar" style="width:56px;height:56px;font-size:1rem">
          <?= e(strtoupper(substr($service['full_name'],0,2))) ?>
        </div>
        <div>
          <div class="fw-bold"><?= e($service['full_name']) ?></div>
          <div class="star-rating">
            <?php for ($i=1;$i<=5;$i++): ?>
              <span class="<?= $i<=round($service['avg_rating'])?'star-filled':'star-empty' ?>">★</span>
            <?php endfor; ?>
          </div>
          <div class="text-muted small"><?= e($service['bio'] ?? '') ?></div>
        </div>
      </div>

      <!-- Reviews -->
      <?php if ($reviews): ?>
        <h5 class="fw-bold mt-4 mb-3">Recent Reviews</h5>
        <?php foreach ($reviews as $r): ?>
          <div class="border rounded p-3 mb-2 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <span class="fw-semibold small"><?= e($r['reviewer_name']) ?></span>
              <span class="text-warning small">
                <?php for ($i=1;$i<=5;$i++): ?>
                  <?= $i<=$r['rating'] ? '★' : '☆' ?>
                <?php endfor; ?>
              </span>
            </div>
            <?php if ($r['comment']): ?>
              <p class="mb-0 small text-muted"><?= e($r['comment']) ?></p>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- RIGHT: booking card -->
    <div class="col-12 col-lg-5">
      <div class="card" style="position:sticky;top:80px">
        <div class="card-body">
          <div class="price-tag mb-1">R<?= number_format($service['price'],0) ?></div>
          <div class="text-muted small mb-3">Estimated: <?= (int)$service['duration_hours'] ?> hour<?= $service['duration_hours']>1?'s':'' ?></div>

          <!-- Escrow notice -->
          <div class="escrow-box mb-3">
            <div class="small mb-1"> Escrow protected</div>
            <div class="small" style="opacity:0.85">
              Your payment is held securely and only released when you confirm the job is done.
            </div>
          </div>

          <?php if (!isLoggedIn()): ?>
            <a href="login.php?redirect=service_detail.php%3Fid%3D<?= $serviceId ?>"
               class="btn btn-primary w-100 mb-2">Login to Book</a>
            <a href="register.php" class="btn btn-outline-secondary w-100">Create Account</a>

          <?php elseif ($_SESSION['role'] === 'worker'): ?>
            <p class="text-muted text-center small">Workers cannot book services.</p>

          <?php else: ?>
            <!-- BOOKING FORM -->
            <form action="process_booking.php" method="POST">
              <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
              <input type="hidden" name="service_id" value="<?= $serviceId ?>">

              <div class="mb-3">
                <label class="form-label">Service date</label>
                <input type="date" name="booking_date" class="form-control"
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Notes (optional)</label>
                <textarea name="notes" class="form-control" rows="2"
                          placeholder="Any special instructions…" maxlength="500"></textarea>
              </div>

              <!-- PayFast payment section -->
              <div class="p-3 mb-3" style="background:#e8f4fb;border-radius:8px;border:1px solid #b8d9ec">
                <div class="fw-bold small mb-2" style="color:#00618e"> Pay via PayFast</div>
                <p class="small text-muted mb-2">
                  R<?= number_format($service['price'],0) ?> will be held in escrow via PayFast
                  and only released when you confirm completion.
                </p>
                <div class="small text-muted">Accepted: Visa, Mastercard, Instant EFT, Capitec Pay</div>
              </div>

              <button type="submit" class="btn-payfast">
                 Pay R<?= number_format($service['price'],0) ?> — Book Securely
              </button>
              <p class="text-muted text-center mt-2" style="font-size:0.75rem">
                Powered by PayFast &mdash; South Africa's trusted payment gateway
              </p>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include '../includes/footer.php'; ?>

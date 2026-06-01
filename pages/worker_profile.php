<?php
//FIle: pages/worker_profile.php
require_once'../includes/auth.php';
require_once '../config/db.php';

$workerId=(int)($_GET['id'] ?? 0);
if(!$workerId) {header('Location: browse.php');exit;}

//Fetch worker details
$stmt=$pdo->prepare("SELECT id, full_name, email, profile_pic, bio, avg_rating, created_at FROM users WHERE id=? AND role='worker'");
$stmt->execute([$workerId]);
$worker=$stmt->fetch();
if(!$worker) {header('Location: browse.php?err=worker_not_found');exit;}

//Fetch worker's approved services
$listingsStmt=$pdo->prepare("SELECT id, title, description, category, price FROM services WHERE worker_id=? AND status='approved'");
$listingsStmt->execute([$workerId]);
$listings=$listingsStmt->fetchAll();

//Fetch completed jobs count
$jobsCount=$pdo->prepare("SELECT COUNT(*) FROM bookings WHERE worker_id=? AND status = 'completed'");
$jobsCount->execute([$workerId]);
$completedJobs=$jobsCount->fetchColumn();

//Fetch all reviews
$revs=$pdo->prepare("SELECT r.rating, r.comment, r.created_at, u.full_name AS reviewer_name FROM reviews r JOIN users u on reviewer_id=u.id WHERE r.reviewee_id=? ORDER BY r.created_at DESC LIMIT 10");
$revs->execute([$workerId]);
$reviews=$revs->fetchAll();

$pageTitle=$worker['full_name'] . "'s Profile";
include '../includes/header.php';
?>
<main class="container py-4">
  <div class="row g-4">
    <!-- LEFT: Profile card -->
    <div class="col-12 col-lg-4">
      <div class="card border-0 shadow-sm p-4 text-center sticky-top" style="top:80px">
        <div class="worker-avatar mx-auto mb-3"
             style="width:80px;height:80px;font-size:1.8rem">
          <?= e(strtoupper(substr($worker['full_name'], 0, 2))) ?>
        </div>
        <h3 class="fw-bold mb-1" style="color:var(--primary)"><?= e($worker['full_name']) ?></h3>
        <div class="star-rating mb-2" style="font-size:1.2rem;justify-content:center;display:flex;gap:2px">
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="<?= $i <= round($worker['avg_rating']) ? 'star-filled' : 'star-empty' ?>">★</span>
          <?php endfor; ?>
          <span class="text-muted ms-1 small">(<?= number_format($worker['avg_rating'], 1) ?>)</span>
        </div>
        <div class="d-flex justify-content-center gap-3 mb-3">
          <div class="text-center">
            <div class="fw-bold" style="color:var(--accent);font-size:1.3rem"><?= count($reviews) ?></div>
            <div class="text-muted" style="font-size:0.75rem">Reviews</div>
          </div>
          <div class="text-center">
            <div class="fw-bold" style="color:var(--primary);font-size:1.3rem"><?= $completedJobs ?></div>
            <div class="text-muted" style="font-size:0.75rem">Jobs Done</div>
          </div>
          <div class="text-center">
            <div class="fw-bold" style="color:var(--primary);font-size:1.3rem"><?= count($listings) ?></div>
            <div class="text-muted" style="font-size:0.75rem">Services</div>
          </div>
        </div>
        <?php if ($worker['bio']): ?>
          <p class="text-muted small"><?= e($worker['bio']) ?></p>
        <?php endif; ?>
        <div class="text-muted" style="font-size:0.75rem">
          Member since <?= date('F Y', strtotime($worker['created_at'])) ?>
        </div>
      </div>
    </div>
 
    <!-- RIGHT: Services + Reviews -->
    <div class="col-12 col-lg-8">
      <!-- Services -->
      <h4 class="fw-bold mb-3" style="color:var(--primary)">Services Offered</h4>
      <?php if (empty($listings)): ?>
        <p class="text-muted small">No active services listed.</p>
      <?php else: ?>
        <div class="row g-3 mb-4">
          <?php foreach ($listings as $l): ?>
            <div class="col-12 col-md-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                  <span class="badge-cat mb-2"><?= e($l['category']) ?></span>
                  <h6 class="fw-bold mt-1"><?= e($l['title']) ?></h6>
                  <p class="text-muted small mb-2"><?= e(substr($l['description'], 0, 80)) ?>…</p>
                  <div class="price-tag" style="font-size:1.1rem">R<?= number_format($l['price'], 0) ?></div>
                </div>
                <div class="card-footer bg-transparent border-0 pb-3">
                  <a href="service_detail.php?id=<?= (int)$l['id'] ?>" class="btn btn-primary btn-sm w-100">Book Now</a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
 
      <!-- Reviews -->
      <h4 class="fw-bold mb-3" style="color:var(--primary)">Reviews</h4>
      <?php if (empty($reviews)): ?>
        <p class="text-muted small">No reviews yet.</p>
      <?php else: ?>
        <?php foreach ($reviews as $r): ?>
          <div class="p-3 mb-2 border-0 shadow-sm rounded"
               style="border-left:4px solid var(--accent)!important;background:#fff">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <strong class="small"><?= e($r['reviewer_name']) ?></strong>
              <span class="star-rating small">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="<?= $i <= $r['rating'] ? 'star-filled' : 'star-empty' ?>">★</span>
                <?php endfor; ?>
              </span>
            </div>
            <?php if ($r['comment']): ?>
              <p class="mb-0 small text-muted"><?= e($r['comment']) ?></p>
            <?php endif; ?>
            <div class="text-muted mt-1" style="font-size:0.72rem">
              <?= date('d M Y', strtotime($r['created_at'])) ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php include '../includes/footer.php'; ?>
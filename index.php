<?php
// FILE: index.php — HustleHub landing page
require_once 'includes/auth.php';
require_once 'config/db.php';
$pageTitle = 'Find trusted local workers';

// Fetch 6 most recent approved services for homepage preview
$stmt = $pdo->prepare("
    SELECT s.*, u.full_name, u.avg_rating
    FROM services s
    JOIN users u ON s.worker_id = u.id
    WHERE s.approval_status = 'approved'
    ORDER BY s.created_at DESC
    LIMIT 6
");
$stmt->execute();
$featured = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <h1>Find trusted local workers<br>in your area</h1>
  <p>Cleaners, gardeners, painters, movers and more — all with escrow payment protection</p>
  <div class="search-bar">
    <input type="text" id="hero-search" placeholder="Search services, e.g. cleaning…" autocomplete="off">
    <button onclick="window.location='/pages/browse.php?q='+encodeURIComponent(document.getElementById('hero-search').value)">
      Search
    </button>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="container py-5">
  <h2 class="text-center fw-bold mb-4" style="color:var(--primary)">How HustleHub works</h2>
  <div class="row g-4 text-center">
    <div class="col-12 col-md-4">
      <div class="step-number mx-auto mb-3">1</div>
      <h5 class="fw-bold">Browse & Book</h5>
      <p class="text-muted small">Find a verified worker, check their ratings, and book at a fixed price.</p>
    </div>
    <div class="col-12 col-md-4">
      <div class="step-number mx-auto mb-3">2</div>
      <h5 class="fw-bold">Escrow Protection</h5>
      <p class="text-muted small">Your payment is held securely — released only when you confirm the job is done.</p>
    </div>
    <div class="col-12 col-md-4">
      <div class="step-number mx-auto mb-3">3</div>
      <h5 class="fw-bold">Review & Trust</h5>
      <p class="text-muted small">Both sides leave honest reviews, building a verified reputation over time.</p>
    </div>
  </div>
</section>

<!-- FEATURED SERVICES -->
<?php if (!empty($featured)): ?>
<section class="container pb-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold" style="color:var(--primary)">Available services near you</h2>
    <a href="/pages/browse.php" class="btn btn-sm btn-navy">View all</a>
  </div>
  <div class="row g-3">
    <?php foreach ($featured as $s): ?>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-3">
            <div class="worker-avatar me-3"><?= e(strtoupper(substr($s['full_name'], 0, 2))) ?></div>
            <div>
              <div class="fw-semibold"><?= e($s['full_name']) ?></div>
              <div class="star-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="<?= $i <= round($s['avg_rating']) ? 'star-filled' : 'star-empty' ?>">★</span>
                <?php endfor; ?>
              </div>
            </div>
          </div>
          <span class="badge-cat"><?= e($s['category']) ?></span>
          <h5 class="card-title mt-2 mb-1"><?= e($s['title']) ?></h5>
          <p class="text-muted small mb-2"><?= e(substr($s['description'], 0, 80)) ?>…</p>
          <div class="price-tag">R<?= number_format($s['price'], 0) ?></div>
        </div>
        <div class="card-footer bg-transparent border-0 pb-3">
          <a href="/pages/service_detail.php?id=<?= (int)$s['id'] ?>" class="btn btn-primary w-100">Book Now</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- TRUST STATS -->
<section style="background:var(--primary);color:#fff;padding:3rem 1.5rem;text-align:center;">
  <div class="container">
    <div class="row g-4">
      <div class="col-12 col-md-4">
        <div style="font-size:2.5rem;font-weight:800;color:var(--accent)">100%</div>
        <div>Escrow-protected payments</div>
      </div>
      <div class="col-12 col-md-4">
        <div style="font-size:2.5rem;font-weight:800;color:var(--accent)">0 fees</div>
        <div>No hidden charges for workers</div>
      </div>
      <div class="col-12 col-md-4">
        <div style="font-size:2.5rem;font-weight:800;color:var(--accent)">Neutral</div>
        <div>Admin dispute resolution</div>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

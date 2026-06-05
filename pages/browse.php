<?php
// Browse page - shows approved listings with search and category filter
require_once '../includes/auth.php';
require_once '../config/db.php';
$pageTitle = 'Browse Services';

$cat   = $_GET['cat'] ?? 'all';
$q     = trim($_GET['q'] ?? '');
$maxP  = (int)($_GET['max_price'] ?? 2000);

// Build query — only show approved services
$sql    = "SELECT s.*, u.full_name, u.avg_rating FROM services s JOIN users u ON s.worker_id = u.id WHERE s.approval_status = 'approved'";
$params = [];

if ($cat !== 'all' && $cat !== '') {
    $sql .= " AND s.category = ?";
    $params[] = $cat;
}
if ($q !== '') {
    $sql .= " AND (s.title LIKE ? OR s.description LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$sql .= " ORDER BY s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

$categories = ['cleaning','gardening','painting','moving','repairs','other'];

include '../includes/header.php';
?>

<main>
<!-- FILTER PILLS -->
<div class="filter-pills" id="category-filters">
  <button class="btn-filter <?= $cat === 'all' ? 'active' : '' ?>" data-cat="all">All</button>
  <?php foreach ($categories as $c): ?>
    <button class="btn-filter <?= $cat === $c ? 'active' : '' ?>" data-cat="<?= $c ?>">
      <?= ucfirst($c) ?>
    </button>
  <?php endforeach; ?>
</div>

<!-- SEARCH + PRICE BAR -->
<div class="container-xl py-3">
  <div class="row g-2 align-items-center mb-3">
    <div class="col-12 col-md-5">
      <input type="text" id="search-input" class="form-control" placeholder="Search services…"
             value="<?= e($q) ?>">
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label mb-1 small">Max price: <span id="price-display">R0 – R<?= $maxP ?></span></label>
      <input type="range" class="form-range" id="price-range" min="0" max="2000" step="50" value="<?= $maxP ?>">
    </div>
    <div class="col-12 col-md-3">
      <span id="results-count" class="text-muted small"><?= count($services) ?> services found</span>
    </div>
  </div>

  <!-- SERVICE GRID -->
  <div class="row g-3" id="services-grid">
    <?php if (empty($services)): ?>
      <div class="col-12 text-center py-5 text-muted">
        <p>No approved services found. <a href="register.php">Register as a worker</a> to list yours!</p>
      </div>
    <?php else: ?>
      <?php foreach ($services as $s): ?>
        <div class="col-12 col-md-6 col-lg-4 service-card"
             data-cat="<?= e($s['category']) ?>"
             data-price="<?= (int)$s['price'] ?>">
          <div class="card h-100">
            <?php if (!empty($s['image_path'])): ?>
              <img src="/<?= e($s['image_path']) ?>" alt="<?= e($s['title']) ?>"
                   style="width:100%;height:180px;object-fit:cover;border-radius:8px 8px 0 0">
            <?php endif; ?>
            <div class="card-body">
              <div class="d-flex align-items-center mb-3">
                <div class="worker-avatar me-3"><?= e(strtoupper(substr($s['full_name'],0,2))) ?></div>
                <div>
                  <div class="fw-semibold small"><?= e($s['full_name']) ?></div>
                  <div class="star-rating" style="font-size:0.8rem">
                    <?php for ($i=1;$i<=5;$i++): ?>
                      <span class="<?= $i<=round($s['avg_rating'])?'star-filled':'star-empty' ?>">★</span>
                    <?php endfor; ?>
                  </div>
                </div>
              </div>
              <span class="badge-cat"><?= e($s['category']) ?></span>
              <h5 class="card-title mt-2 mb-1" style="font-size:1rem"><?= e($s['title']) ?></h5>
              <p class="text-muted small mb-2" style="font-size:0.8rem">
                <?= e(substr($s['description'],0,90)) ?>…
              </p>
              <div class="price-tag">R<?= number_format($s['price'],0) ?></div>
            </div>
            <div class="card-footer bg-transparent border-0 pb-3">
              <a href="service_detail.php?id=<?= (int)$s['id'] ?>" class="btn btn-primary w-100">Book Now</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
</main>

<?php include '../includes/footer.php'; ?>

<?php
//File: pages/search.php
require_once '../includes/auth.php';
require_once '../config/db.php';

$query = trim($_GET['q'] ?? '');
$q = $query; // alias used in template
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
$results = [];

if ($query) {
    $stmt = $pdo->prepare("SELECT s.id, s.title, s.description, s.category, s.price, u.full_name, u.avg_rating FROM services s JOIN users u ON s.worker_id = u.id WHERE s.approval_status='approved' AND (s.title LIKE ? OR s.description LIKE ? OR s.category LIKE ?) ORDER BY s.created_at DESC LIMIT 20");
    $likeQuery = '%' . $query . '%';
    $stmt->execute([$likeQuery, $likeQuery, $likeQuery]);
    $results = $stmt->fetchAll();
}

//Ajac returns JSON
if($isAjax) {
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

//NORMAL page Render
$pageTitle = $query? 'Search for Results: ' . htmlspecialchars($query) : 'Search Services';
include '../includes/header.php';
?>
<main class="container py-4">
  <!-- Search bar -->
  <form action="search.php" method="GET" class="mb-4">
    <div class="input-group input-group-lg" style="max-width:600px">
      <input type="text" name="q" class="form-control" placeholder="Search services…"
             value="<?= e($q) ?>" autofocus>
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </form>
 
  <?php if ($q === ''): ?>
    <p class="text-muted">Enter a keyword to search for services.</p>
  <?php elseif (empty($results)): ?>
    <p class="text-muted">No services found for "<strong><?= e($q) ?></strong>".
       <a href="browse.php">Browse all services</a>.</p>
  <?php else: ?>
    <p class="text-muted mb-3"><?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?>
       for "<strong><?= e($q) ?></strong>"</p>
    <div class="row g-3">
      <?php foreach ($results as $s): ?>
        <div class="col-12 col-md-6 col-lg-4">
          <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
              <span class="badge-cat mb-2"><?= e($s['category']) ?></span>
              <h6 class="fw-bold mt-1"><?= e($s['title']) ?></h6>
              <div class="small text-muted mb-1"><?= e($s['full_name']) ?></div>
              <div class="star-rating mb-2">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <span class="<?= $i <= round($s['avg_rating']) ? 'star-filled' : 'star-empty' ?>">★</span>
                <?php endfor; ?>
              </div>
              <div class="price-tag" style="font-size:1.1rem">R<?= number_format($s['price'], 0) ?></div>
            </div>
            <div class="card-footer bg-transparent border-0 pb-3">
              <a href="service_detail.php?id=<?= (int)$s['id'] ?>" class="btn btn-primary btn-sm w-100">View & Book</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>
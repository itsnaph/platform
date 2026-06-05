<?php
// FILE: pages/create_listing.php — Worker creates a new service listing
require_once '../includes/auth.php';
require_once '../config/db.php';
requireRole('worker');

$error = '';
$categories = ['cleaning','gardening','painting','moving','repairs','other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = $_POST['category'] ?? '';
    $price       = (float)($_POST['price'] ?? 0);
    $duration    = (int)($_POST['duration_hours'] ?? 1);

    // Validate
    if (strlen($title) < 5)              $error = 'Title must be at least 5 characters.';
    elseif (strlen($description) < 20)   $error = 'Description must be at least 20 characters.';
    elseif (!in_array($category, $categories, true)) $error = 'Please choose a valid category.';
    elseif ($price < 50 || $price > 50000) $error = 'Price must be between R50 and R50,000.';
    elseif ($duration < 1 || $duration > 24)$error = 'Duration must be between 1 and 24 hours.';

    // Handle image upload
    $imagePath = null;
    if (!$error && !empty($_FILES['image']['name'])) {
        $file    = $_FILES['image'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo        = new finfo(FILEINFO_MIME_TYPE);
        $mime         = $finfo->file($file['tmp_name']);

        if ($file['size'] > $maxSize) {
            $error = 'Image must be under 2MB.';
        } elseif (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
            $error = 'Only JPG, PNG, or WebP images are accepted.';
        } else {
            $filename  = uniqid('', true) . '.' . $ext;
            $uploadDir = '../assets/images/listings/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
            $imagePath = 'assets/images/listings/' . $filename;
        }
    }

    if (!$error) {
        $pdo->prepare("
            INSERT INTO services (worker_id, title, description, category, price, duration_hours, image_path)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ")->execute([(int)$_SESSION['user_id'], $title, $description, $category, $price, $duration, $imagePath]);

        header('Location: worker_dashboard.php?msg=listing_created');
        exit;
    }
}

$pageTitle = 'Create Listing';
include '../includes/header.php';
?>
<main class="container py-4" style="max-width:640px">
  <h2 class="fw-bold mb-1" style="color:var(--primary)">Create a Service Listing</h2>
  <p class="text-muted small mb-4">
    Your listing will be reviewed by an admin before appearing on the marketplace.
  </p>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm border-0 p-4">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

      <div class="mb-3">
        <label class="form-label fw-semibold">Service Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" maxlength="160"
               placeholder="e.g. Professional Deep Home Cleaning"
               value="<?= e($_POST['title'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
        <select name="category" class="form-select" required>
          <option value="">— Select category —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= $c ?>" <?= ($_POST['category'] ?? '') === $c ? 'selected' : '' ?>>
              <?= ucfirst($c) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="4" minlength="20" maxlength="1000"
                  placeholder="Describe what you offer, your experience, tools you use…"
                  required><?= e($_POST['description'] ?? '') ?></textarea>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-6">
          <label class="form-label fw-semibold">Price (ZAR) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">R</span>
            <input type="number" name="price" class="form-control" min="50" max="50000" step="10"
                   value="<?= (int)($_POST['price'] ?? 100) ?>" required>
          </div>
        </div>
        <div class="col-6">
          <label class="form-label fw-semibold">Duration (hours) <span class="text-danger">*</span></label>
          <input type="number" name="duration_hours" class="form-control" min="1" max="24"
                 value="<?= (int)($_POST['duration_hours'] ?? 2) ?>" required>
        </div>
      </div>

      <!-- Image upload with preview -->
      <div class="mb-4">
        <label class="form-label fw-semibold">Listing Photo <span class="text-muted small">(optional, max 2MB)</span></label>
        <input type="file" name="image" id="service-image" class="form-control" accept="image/jpeg,image/png,image/webp">
        <div id="img-preview-wrap" class="mt-2 text-center d-none">
          <img id="img-preview" src="" alt="Preview" class="rounded"
               style="max-height:180px;max-width:100%;object-fit:cover">
        </div>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">Submit for Approval</button>
        <a href="worker_dashboard.php" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>

  <div class="mt-3 p-3 rounded" style="background:#fff3cd;font-size:0.85rem">
    <strong>Note:</strong> Listings are reviewed by our admin team before going live.
    You will see the approval status in your dashboard.
  </div>
</main>

<?php include '../includes/footer.php'; ?>

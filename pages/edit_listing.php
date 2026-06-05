<?php
// FILE: pages/edit_listing.php
require_once '../includes/auth.php';
require_once '../config/db.php';
requireRole('worker');

$workerId  = (int)$_SESSION['user_id'];
$serviceId = (int)($_GET['id'] ?? 0);
if (!$serviceId) { header('Location: worker_dashboard.php'); exit; }

// Fetch — must belong to this worker
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND worker_id = ?");
$stmt->execute([$serviceId, $workerId]);
$service = $stmt->fetch();
if (!$service) { header('Location: worker_dashboard.php?err=not_found'); exit; }

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = $_POST['category'] ?? '';
    $price       = (float)($_POST['price'] ?? 0);
    $duration    = (int)($_POST['duration_hours'] ?? 1);
    $validCats   = ['cleaning','gardening','painting','moving','repairs','other'];

    if (!$title || !$description || !$category || $price <= 0) {
        $error = 'Please fill in all required fields.';
    } elseif (!in_array($category, $validCats, true)) {
        $error = 'Invalid category.';
    } else {
        // Handle image upload
        $imagePath = $service['image_path']; // keep old image unless new one uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $ext          = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExts  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo        = new finfo(FILEINFO_MIME_TYPE);
            $mime         = $finfo->file($_FILES['image']['tmp_name']);

            if (!in_array($ext, $allowedExts) || !in_array($mime, $allowedMimes)) {
                $error = 'Only JPEG, PNG, WebP or GIF images are allowed.';
            } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $error = 'Image must be under 2 MB.';
            } else {
                $filename  = uniqid('', true) . '.' . $ext;
                $uploadDir = __DIR__ . '/../assets/images/listings/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
                move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
                $imagePath = 'assets/images/listings/' . $filename;
            }
        }

        if (!$error) {
            $upd = $pdo->prepare(
                "UPDATE services SET title=?, description=?, category=?, price=?, duration_hours=?,
                 image_path=?, approval_status='pending' WHERE id=? AND worker_id=?"
            );
            $upd->execute([$title, $description, $category, $price, $duration, $imagePath, $serviceId, $workerId]);
            $success = 'Listing updated. It will be re-reviewed by an admin.';
            // Refresh service data
            $stmt->execute([$serviceId, $workerId]);
            $service = $stmt->fetch();
        }
    }
}

$pageTitle = 'Edit Listing';
include '../includes/header.php';
?>
<main class="container py-4" style="max-width:640px">
  <a href="worker_dashboard.php" class="btn btn-outline-secondary btn-sm mb-3">← Back to Dashboard</a>
  <h2 class="fw-bold mb-4" style="color:var(--primary)">Edit Listing</h2>

  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>

  <div class="card p-4">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

      <div class="mb-3">
        <label class="form-label">Service Title *</label>
        <input type="text" name="title" class="form-control" maxlength="160"
               value="<?= e($service['title']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Category *</label>
        <select name="category" class="form-select" required>
          <?php foreach (['cleaning','gardening','painting','moving','repairs','other'] as $c): ?>
            <option value="<?= $c ?>" <?= $service['category'] === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Description *</label>
        <textarea name="description" class="form-control" rows="4" required><?= e($service['description']) ?></textarea>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-6">
          <label class="form-label">Price (R) *</label>
          <input type="number" name="price" class="form-control" min="1" step="0.01"
                 value="<?= $service['price'] ?>" required>
        </div>
        <div class="col-6">
          <label class="form-label">Est. Duration (hours)</label>
          <input type="number" name="duration_hours" class="form-control" min="1" max="24"
                 value="<?= $service['duration_hours'] ?>">
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Update Image (optional — max 2 MB)</label>
        <?php if ($service['image_path']): ?>
          <div class="mb-2">
            <img id="img-preview" src="/<?= e($service['image_path']) ?>"
                 style="max-width:200px;border-radius:8px">
          </div>
        <?php else: ?>
          <img id="img-preview" src="" class="d-none mb-2" style="max-width:200px;border-radius:8px">
        <?php endif; ?>
        <input type="file" name="image" id="service-image" class="form-control" accept="image/*">
      </div>

      <button type="submit" class="btn btn-primary w-100">Save Changes</button>
    </form>
  </div>
</main>
<?php include '../includes/footer.php'; ?>

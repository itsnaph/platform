<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

if (isLoggedIn()) { header('Location: ../index.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $name     = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? 'client';

    // Basic validation
    if (!$name || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!in_array($role, ['client','worker'])) {
        $error = 'Invalid role selected.';
    } else {
        // Check email not already used
        $chk = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $error = 'This email address is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $ins = $pdo->prepare(
                "INSERT INTO users (full_name, email, phone, password, role, is_verified)
                 VALUES (?, ?, ?, ?, ?, 1)"
            );
            $ins->execute([$name, $email, $phone, $hash, $role]);

            $newId = (int)$pdo->lastInsertId();

            // Log them straight in
            $_SESSION['user_id']    = $newId;
            $_SESSION['user_name']  = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['role']       = $role;
            $_SESSION['last_active'] = time();

            $redirect = $role === 'worker' ? 'worker_dashboard.php' : 'browse.php';
            header("Location: $redirect");
            exit;
        }
    }
}

$pageTitle = 'Create Account';
include '../includes/header.php';
?>
<main class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-9 col-md-6">
      <div class="card p-4">
        <h2 class="fw-bold text-center mb-1" style="color:var(--primary)">Create Account</h2>
        <p class="text-center text-muted small mb-4">Join HustleHub as a worker or client</p>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if (!$error): ?>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

          <div class="mb-3">
            <label class="form-label">I want to join as</label>
            <div class="d-flex gap-3">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="role" value="client"
                       id="roleClient" <?= ($_POST['role'] ?? 'client') === 'client' ? 'checked' : '' ?>>
                <label class="form-check-label" for="roleClient">Client — book services</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="role" value="worker"
                       id="roleWorker" <?= ($_POST['role'] ?? '') === 'worker' ? 'checked' : '' ?>>
                <label class="form-check-label" for="roleWorker">Worker — offer services</label>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Full name *</label>
            <input type="text" name="full_name" class="form-control"
                   value="<?= e($_POST['full_name'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Email address *</label>
            <input type="email" name="email" class="form-control"
                   value="<?= e($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Phone number</label>
            <input type="tel" name="phone" class="form-control"
                   value="<?= e($_POST['phone'] ?? '') ?>" placeholder="e.g. 0821234567">
          </div>
          <div class="mb-3">
            <label class="form-label">Password * <small class="text-muted">(min 8 characters)</small></label>
            <input type="password" name="password" class="form-control" minlength="8" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirm password *</label>
            <input type="password" name="confirm_password" class="form-control" minlength="8" required>
          </div>
          <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>
        <?php endif; // !$error ?>

        <p class="text-center mt-3 small text-muted">
          Already have an account? <a href="login.php">Login here</a>
        </p>
      </div>
    </div>
  </div>
</main>
<?php include '../includes/footer.php'; ?>

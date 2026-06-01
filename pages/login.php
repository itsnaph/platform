<?php
require_once '../includes/auth.php';
require_once '../config/db.php';

if (isLoggedIn()) { header('Location: ../index.php'); exit; }

$error    = '';
$redirect = $_GET['redirect'] ?? '../index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['last_active'] = time();

            // Role-based redirect
            if (in_array($user['role'], ['admin','moderator'])) {
                header('Location: ../admin/dashboard.php'); exit;
            } elseif ($user['role'] === 'worker') {
                header('Location: worker_dashboard.php'); exit;
            } else {
                header('Location: ' . ($redirect ?: '../index.php')); exit;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
include '../includes/header.php';
?>
<main class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-8 col-md-5">
      <div class="card p-4">
        <h2 class="fw-bold text-center mb-1" style="color:var(--primary)">Welcome back</h2>
        <p class="text-center text-muted small mb-4">Log in to HustleHub</p>

        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
          <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control"
                   value="<?= e($_POST['email'] ?? '') ?>" required autocomplete="email">
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required autocomplete="current-password">
          </div>
          <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="text-center mt-3 small text-muted">
          Don't have an account? <a href="register.php">Register here</a>
        </p>
      </div>
    </div>
  </div>
</main>
<?php include '../includes/footer.php'; ?>

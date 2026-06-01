<?php
// FILE: pages/verify_otp.php — Email OTP verification after registration
require_once '../includes/auth.php';
require_once '../config/db.php';

// Must be logged in but unverified
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
if ($_SESSION['is_verified'] ?? false) {
    header('Location: ' . ($_SESSION['role'] === 'worker' ? 'worker_dashboard.php' : 'client_dashboard.php'));
    exit;
}

$userId = (int)$_SESSION['user_id'];
$error  = '';
$info   = '';

// Handle OTP submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    verifyCsrfToken();
    $inputOtp = trim($_POST['otp'] ?? '');

    $stmt = $pdo->prepare("SELECT otp_code, otp_expires FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user || !$user['otp_code']) {
        $error = 'No OTP found. Please request a new one.';
    } elseif (time() > strtotime($user['otp_expires'])) {
        $error = 'Your OTP has expired. Please request a new one.';
    } elseif ($user['otp_code'] !== $inputOtp) {
        $error = 'Incorrect OTP. Please try again.';
    } else {
        // Mark verified, clear OTP
        $pdo->prepare("UPDATE users SET is_verified = 1, otp_code = NULL, otp_expires = NULL WHERE id = ?")
            ->execute([$userId]);
        $_SESSION['is_verified'] = true;

        $redirect = $_SESSION['role'] === 'worker' ? 'worker_dashboard.php' : 'browse.php';
        header("Location: $redirect?msg=verified");
        exit;
    }
}

// Handle resend OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend'])) {
    verifyCsrfToken();
    $newOtp     = rand(100000, 999999);
    $newExpires = date('Y-m-d H:i:s', time() + 600); // 10 min

    $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires = ? WHERE id = ?")
        ->execute([$newOtp, $newExpires, $userId]);

    // In a real app, send email here via PHPMailer/SMTP
    // mail($_SESSION['user_email'], 'Your HustleHub OTP', "Your code is: $newOtp");
    // For demo: show it (remove in production)
    $info = "Demo OTP: $newOtp (expires in 10 minutes — in production this would be emailed)";
}

$pageTitle = 'Verify Your Account';
include '../includes/header.php';
?>
<main class="container py-5" style="max-width:420px">
  <div class="text-center mb-4">
    <h2 class="fw-bold" style="color:var(--primary)">Verify Your Email</h2>
    <p class="text-muted small">
      We sent a 6-digit code to your email address.<br>
      Enter it below to activate your account.
    </p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>
  <?php if ($info): ?>
    <div class="alert alert-info"><?= e($info) ?></div>
  <?php endif; ?>

  <div class="card shadow-sm border-0 p-4">
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <div class="mb-3">
        <label class="form-label fw-semibold">6-Digit OTP Code</label>
        <input type="text" name="otp" class="form-control form-control-lg text-center fw-bold"
               maxlength="6" placeholder="000000"
               inputmode="numeric" pattern="[0-9]{6}" required
               style="letter-spacing:0.5rem;font-size:1.5rem">
      </div>
      <button type="submit" class="btn btn-primary w-100">Verify Account</button>
    </form>

    <hr class="my-3">

    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <button type="submit" name="resend" value="1" class="btn btn-outline-secondary btn-sm w-100">
        Resend OTP Code
      </button>
    </form>
  </div>
</main>
<?php include '../includes/footer.php'; ?>

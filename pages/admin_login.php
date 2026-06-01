<?php
//FILE: pages/admin_login.php
require_once '../includes/auth.php';

//If already logged in as admin/moderator, go straight to admin dashboard
if(isset($_SESSION['role']) && in_array($_SESSION['role'],['admin','moderator'],true)){
    header('Location: ../admin/dashboard.php');exit;
}

require_once '../config/db.php';

$error='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $token=$_POST['csrf_token'] ?? '';
    if($_SESSION['csrf_token'] !== $token){
        $error='Invalid CSRF token';
    } else {
        $email=trim($_POST['email'] ?? '');
        $password=$_POST['password'] ?? '';

        if(!$email || !$password){
            $error='Email and password are required';
        }else{
            $stmt=$pdo->prepare("SELECT id, full_name, email, password, role FROM users WHERE email=? AND role IN ('admin','moderator')");
            $stmt->execute([$email]);
            $user=$stmt->fetch();

            if($user && password_verify($password, $user['password'])){
                $_SESSION['user_id']=$user['id'];
                $_SESSION['user_name']=$user['full_name'];
                $_SESSION['role']=$user['role'];
                header('Location: ../admin/dashboard.php');exit;
            }else{
                $error='Invalid email or password';
            }
        }
    }
}
$pageTitle='Admin Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — HustleHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
  <style>
    body { background: var(--primary); min-height: 100vh;
           display:flex; align-items:center; justify-content:center; }
    .login-card { background:#fff; border-radius:16px; padding:2.5rem;
                  box-shadow:0 20px 60px rgba(0,0,0,0.3); width:100%; max-width:400px; }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="text-center mb-4">
      <div class="fw-bold mb-1" style="color:var(--primary);font-size:1.6rem">
        <span style="color:var(--accent)">H</span>ustleHub
      </div>
      <div class="text-muted small">Admin Portal</div>
    </div>
 
    <?php if ($error): ?>
      <div class="alert alert-danger small"><?= e($error) ?></div>
    <?php endif; ?>
 
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <div class="mb-3">
        <label class="form-label small fw-semibold">Admin Email</label>
        <input type="email" name="email" class="form-control"
               value="<?= e($_POST['email'] ?? '') ?>" required autocomplete="email">
      </div>
      <div class="mb-3">
        <label class="form-label small fw-semibold">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Sign In to Admin</button>
    </form>
    <div class="text-center mt-3">
      <a href="../pages/login.php" class="text-muted small">← Main Site Login</a>
    </div>
  </div>
</body>
</html>
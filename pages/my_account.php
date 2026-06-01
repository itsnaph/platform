<?php
//File: pages/my_account.php
require_once '../includes/auth.php';
require_once '../config/db.php';

if(!isLoggedIn()){header('Location: login.php');exit;}

$userId=(int)$_SESSION['user_id'];
$error=$_GET['err'] ?? '';
$success=$_GET['success'] ?? '';

//Fetch current user details
$stmt=$pdo->prepare("SELECT id, full_name, email, phone, profile_pic, bio, is_verified, role, password, created_at FROM users WHERE id=?");
$stmt->execute([$userId]);
$user=$stmt->fetch();

if($_SERVER['REQUEST_METHOD']==='POST'){
    //CSRF TOKEN CHECKER
    verifyCsrfToken();
    
    $action=$_POST['action'] ?? '';

    //update profile details
    if($action==='update_profile'){
        $name=trim($_POST['full_name'] ?? '');
        $phone=trim($_POST['phone'] ?? '');
        $bio=trim($_POST['bio'] ?? ''); 

        if(!$name){
            header('Location: my_account.php?err=Full+Name+is+required');exit;
        }else{
            $pdo->prepare("UPDATE users SET full_name=?, phone=?, bio=? WHERE id=?")->execute([$name,$phone,$bio,$userId]);
            $_SESSION['user_name']=$name;
            header('Location: my_account.php?success=Profile+updated+successfully');exit;
        }
    }

    //Change password
    if($action==='change_password'){
        $currentPassword=$_POST['current_password']??'';
        $newPassword=$_POST['new_password']??'';
        $confirmPassword=$_POST['confirm_password']??'';

        if(!password_verify($currentPassword, $user['password'] ?? '')){
            header('Location: my_account.php?err=Current+password+is+incorrect');exit;
        }elseif($newPassword !== $confirmPassword){
            header('Location: my_account.php?err=New+password+and+confirmation+do+not+match');exit;
        }elseif(strlen($newPassword)<8){
            header('Location: my_account.php?err=New+password+must+be+at+least+8+characters+long');exit;
        }elseif(password_verify($newPassword, $user['password'] ?? '')){
            header('Location: my_account.php?err=New+password+cannot+be+the+same+as+current');exit;
        }else{
            $newHash=password_hash($newPassword, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$newHash,$userId]);
            header('Location: my_account.php?success=Password+changed+successfully');exit;
        }
    }


}
$pageTitle = 'My Account';
include '../includes/header.php';
?>
<main class="container py-4" style="max-width:640px">
  <h2 class="fw-bold mb-4" style="color:var(--primary)">My Account</h2>
 
  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
 
  <!-- Profile section -->
  <div class="card border-0 shadow-sm p-4 mb-4">
    <h5 class="fw-bold mb-3" style="color:var(--primary)">Profile Details</h5>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="action" value="update_profile">
      <div class="mb-3">
        <label class="form-label">Full Name *</label>
        <input type="text" name="full_name" class="form-control"
               value="<?= e($user['full_name']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="phone" class="form-control"
               value="<?= e($user['phone'] ?? '') ?>">
      </div>
      <?php if ($_SESSION['role'] === 'worker'): ?>
      <div class="mb-3">
        <label class="form-label">Bio <small class="text-muted">(shown on your profile)</small></label>
        <textarea name="bio" class="form-control" rows="3" maxlength="400"
                  placeholder="Tell clients about your experience…"><?= e($user['bio'] ?? '') ?></textarea>
      </div>
      <?php endif; ?>
      <div class="mb-2 text-muted small">
        <strong>Email:</strong> <?= e($user['email']) ?>
        <span class="ms-2 text-success small"><?= $user['is_verified'] ? '✓ Verified' : '' ?></span>
      </div>
      <div class="mb-2 text-muted small">
        <strong>Role:</strong> <?= ucfirst(e($user['role'])) ?>
      </div>
      <button type="submit" class="btn btn-primary mt-2">Save Changes</button>
    </form>
  </div>
 
  <!-- Password section -->
  <div class="card border-0 shadow-sm p-4">
    <h5 class="fw-bold mb-3" style="color:var(--primary)">Change Password</h5>
    <form method="POST">
      <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="action" value="change_password">
      <div class="mb-3">
        <label class="form-label">Current Password</label>
        <input type="password" name="current_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">New Password <small class="text-muted">(min 8 chars)</small></label>
        <input type="password" name="new_password" class="form-control" minlength="8" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-control" minlength="8" required>
      </div>
      <button type="submit" class="btn btn-outline-secondary">Change Password</button>
    </form>
  </div>
</main>
<?php include '../includes/footer.php'; ?>
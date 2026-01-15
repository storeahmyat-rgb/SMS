<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if (loginUser($u, $p)) {
        // Redirect based on role
        $role = $_SESSION['role'];
        if ($role === 'super_admin') header('Location: ' . BASE_URL . 'admin/dashboard.php');
        elseif ($role === 'teacher') header('Location: ' . BASE_URL . 'teacher/dashboard.php');
        elseif ($role === 'accountant') header('Location: ' . BASE_URL . 'accountant/dashboard.php');
        else header('Location: ' . BASE_URL . 'admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>

<div class="row justify-content-center">
  <div class="col-md-4">
    <h3>Login</h3>
    <?php if ($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button class="btn btn-primary">Login</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/db.php';

// Redirect based on role
if (isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin') {
    header('Location: ' . BASE_URL . 'admin/dashboard_enhanced.php');
    exit;
} elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'accountant') {
    header('Location: ' . BASE_URL . 'admin/finance_hub.php');
    exit;
} elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
    header('Location: ' . BASE_URL . 'teacher/dashboard.php');
    exit;
}

requireRole(['super_admin','accountant','teacher']);
$pdo = getPDO();
$counts = [];
$counts['students'] = $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$counts['teachers'] = $pdo->query('SELECT COUNT(*) FROM teachers')->fetchColumn();
$counts['fees'] = $pdo->query('SELECT COUNT(*) FROM fee_payments')->fetchColumn();

?>
<h1>Dashboard</h1>
<div class="row">
  <div class="col-md-4">
    <div class="stats-card bg-gradient-primary mb-4">
      <h3><?=intval($counts['students'])?></h3>
      <p>Total Students</p>
      <a href="<?=BASE_URL?>admin/students.php" class="text-white text-decoration-none small">Manage <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stats-card bg-gradient-success mb-4">
      <h3><?=intval($counts['teachers'])?></h3>
      <p>Total Teachers</p>
      <a href="<?=BASE_URL?>admin/teachers.php" class="text-white text-decoration-none small">Manage <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
  <div class="col-md-4">
    <div class="stats-card bg-gradient-warning mb-4">
      <h3><?=intval($counts['fees'])?></h3>
      <p>Payments Recorded</p>
      <a href="<?=BASE_URL?>admin/fee_pay.php" class="text-white text-decoration-none small">Collect Fee <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();

// Get statistics
$stats = [];
$stats['students'] = $pdo->query('SELECT COUNT(*) FROM students WHERE status="Active"')->fetchColumn();
$stats['teachers'] = $pdo->query('SELECT COUNT(*) FROM teachers WHERE status="Active"')->fetchColumn();
$stats['classes'] = $pdo->query('SELECT COUNT(*) FROM classes')->fetchColumn();
$stats['sections'] = $pdo->query('SELECT COUNT(*) FROM sections')->fetchColumn();

// Financial data
$stats['income'] = $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM fee_payments WHERE MONTH(paid_on)=MONTH(NOW()) AND YEAR(paid_on)=YEAR(NOW())')->fetchColumn() ?? 0;
$stats['salaries'] = $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM salaries WHERE paid_status="Paid" AND MONTH(paid_on)=MONTH(NOW()) AND YEAR(paid_on)=YEAR(NOW())')->fetchColumn() ?? 0;

// Class-wise student count
$class_stats = $pdo->query('SELECT c.name, COUNT(s.id) as count FROM classes c LEFT JOIN students s ON c.id=s.class_id GROUP BY c.id ORDER BY c.name')->fetchAll();

?>
<h1>Overview</h1>
<div class="row mb-4">
  <div class="col-md-3">
    <div class="stats-card bg-gradient-primary">
      <h3><?=intval($stats['students'])?></h3>
      <p>Students</p>
      <a href="<?=BASE_URL?>admin/students.php" class="text-white text-decoration-none small">View Details <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card bg-gradient-success">
      <h3><?=intval($stats['teachers'])?></h3>
      <p>Teachers</p>
      <a href="<?=BASE_URL?>admin/teachers.php" class="text-white text-decoration-none small">View Details <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card bg-gradient-warning">
      <h3><?=intval($stats['classes'])?></h3>
      <p>Classes</p>
    </div>
  </div>
  <div class="col-md-3">
    <div class="stats-card bg-gradient-danger">
      <h3><?=intval($stats['sections'])?></h3>
      <p>Sections</p>
    </div>
  </div>
</div>

<div class="row mb-4">
  <div class="col-md-8">
      <div class="card h-100">
          <div class="card-header">
              <i class="fas fa-chart-pie me-2"></i> Financial Overview This Month
          </div>
          <div class="card-body">
              <div class="row text-center">
                  <div class="col-md-6 border-end">
                      <h4 class="text-success">Rs. <?=number_format($stats['income'], 0)?></h4>
                      <p class="text-muted">Income Collected</p>
                      <a href="<?=BASE_URL?>admin/income_report.php" class="btn btn-sm btn-outline-success">View Report</a>
                  </div>
                  <div class="col-md-6">
                      <h4 class="text-danger">Rs. <?=number_format($stats['salaries'], 0)?></h4>
                      <p class="text-muted">Salaries Paid</p>
                      <a href="<?=BASE_URL?>admin/salaries_manage.php" class="btn btn-sm btn-outline-danger">View Salaries</a>
                  </div>
              </div>
          </div>
      </div>
  </div>
  <div class="col-md-4">
    <div class="card h-100">
      <div class="card-header bg-white">
        <i class="fas fa-bolt me-2"></i> Quick Actions
      </div>
      <div class="list-group list-group-flush">
        <a href="<?=BASE_URL?>admin/attendance.php" class="list-group-item list-group-item-action"><i class="fas fa-user-clock text-primary me-2"></i> Attendance</a>
        <a href="<?=BASE_URL?>admin/fees.php" class="list-group-item list-group-item-action"><i class="fas fa-file-invoice-dollar text-success me-2"></i> Fee Structures</a>
        <a href="<?=BASE_URL?>admin/fee_pay.php" class="list-group-item list-group-item-action"><i class="fas fa-cash-register text-warning me-2"></i> Collect Fees</a>
        <a href="<?=BASE_URL?>admin/pending_fees.php" class="list-group-item list-group-item-action"><i class="fas fa-exclamation-circle text-danger me-2"></i> Pending Fees</a>
        <a href="<?=BASE_URL?>admin/biometric_import.php" class="list-group-item list-group-item-action"><i class="fas fa-fingerprint text-secondary me-2"></i> Biometric</a>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

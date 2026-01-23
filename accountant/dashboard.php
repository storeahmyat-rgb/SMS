<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['accountant']);
require_once __DIR__ . '/../includes/header.php';
?>
<?php
$pdo = getPDO();
$month = date('Y-m');
$monthly = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM fee_payments WHERE DATE_FORMAT(paid_on, "%Y-%m") = :m');
$monthly->execute([':m'=>$month]);
$monthly_total = $monthly->fetchColumn() ?? 0;

$pending_count = $pdo->query('SELECT COUNT(*) FROM students WHERE status="Active"')->fetchColumn();
?>
<h1>Financial Control Center</h1>
<p class="text-muted">Managed financial operations and fee collection oversight.</p>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="stats-card bg-success text-white p-4 rounded-3 h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mb-0 text-white-50">Current Month Revenue</h5>
                    <h2 class="display-6 fw-bold mb-0">Rs. <?=number_format($monthly_total, 2)?></h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fas fa-hand-holding-usd"></i></div>
            </div>
            <p class="mt-3 mb-0 small"><i class="fas fa-chart-line me-1"></i> Data for <?=date('F Y')?></p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stats-card bg-warning text-dark p-4 rounded-3 h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mb-0 text-dark-50">Active Students</h5>
                    <h2 class="display-6 fw-bold mb-0"><?=intval($pending_count)?></h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fas fa-users"></i></div>
            </div>
            <a href="<?=BASE_URL?>admin/pending_fees.php" class="mt-3 d-inline-block text-dark fw-bold text-decoration-none small">
                Check Pending Dues <i class="fas fa-chevron-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white"><i class="fas fa-rocket me-2"></i>Primary Accountant Actions</div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <a href="<?=BASE_URL?>admin/fee_pay.php" class="btn btn-primary btn-lg w-100 p-3 text-start h-100">
                    <i class="fas fa-cash-register d-block mb-3 fs-3"></i>
                    <strong>Collect Fees</strong>
                    <div class="small opacity-75">Process new student payments</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?=BASE_URL?>admin/profitloss.php" class="btn btn-outline-info btn-lg w-100 p-3 text-start h-100">
                    <i class="fas fa-file-invoice-dollar d-block mb-3 fs-3 text-info"></i>
                    <strong>Financial Analytics</strong>
                    <div class="small opacity-75">View detailed profit & loss logs</div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="<?=BASE_URL?>admin/fees.php" class="btn btn-outline-secondary btn-lg w-100 p-3 text-start h-100">
                    <i class="fas fa-cog d-block mb-3 fs-3"></i>
                    <strong>Fee Settings</strong>
                    <div class="small opacity-75">Adjust institution fee structure</div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

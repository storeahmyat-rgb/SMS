<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();

// Daily and monthly collection
$today = date('Y-m-d');
$month = date('Y-m');

$daily = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) as total FROM fee_payments WHERE DATE(paid_on) = :d');
$daily->execute([':d'=>$today]);
$daily_total = $daily->fetchColumn() ?? 0;

$monthly = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) as total FROM fee_payments WHERE DATE_FORMAT(paid_on, "%Y-%m") = :m');
$monthly->execute([':m'=>$month]);
$monthly_total = $monthly->fetchColumn() ?? 0;

// Class-wise breakdown
$class_wise = $pdo->query('SELECT c.name AS class_name, COALESCE(SUM(fp.amount), 0) AS total FROM classes c LEFT JOIN students s ON c.id=s.class_id LEFT JOIN fee_payments fp ON s.id=fp.student_id WHERE DATE_FORMAT(fp.paid_on, "%Y-%m") = "'.$month.'" OR fp.id IS NULL GROUP BY c.id ORDER BY c.name')->fetchAll();

?>
<h1>Financial Collection Report</h1>
<p class="text-muted">A comprehensive overview of fee collections and institutional income.</p>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="stats-card bg-primary text-white p-4 rounded-3 h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mb-0 text-white-50">Today's Total</h5>
                    <h2 class="display-6 fw-bold mb-0">Rs. <?=number_format($daily_total, 2)?></h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fas fa-calendar-day"></i></div>
            </div>
            <p class="mt-3 mb-0"><small>Collection for <?=date('d M Y')?></small></p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stats-card bg-info text-white p-4 rounded-3 h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mb-0 text-white-50">Monthly Total</h5>
                    <h2 class="display-6 fw-bold mb-0">Rs. <?=number_format($monthly_total, 2)?></h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fas fa-calendar-alt"></i></div>
            </div>
            <p class="mt-3 mb-0"><small>Collection for <?=date('F Y')?></small></p>
        </div>
    </div>
</div>

<div class="card h-100">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-chart-pie me-2 text-primary"></i>Class-wise Breakdown (<?=date('F Y')?>)</span>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-1"></i> Print Report</button>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Academic Class</th>
                    <th class="text-end pe-4">Total Collected</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($class_wise as $cw): ?>
                <tr>
                    <td class="ps-4 fw-bold text-dark"><?=htmlspecialchars($cw['class_name'])?></td>
                    <td class="text-end pe-4 fw-bold text-success">Rs. <?=number_format($cw['total'], 2)?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

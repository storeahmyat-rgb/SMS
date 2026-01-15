<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();

// Calculate income, salaries, expenses
$income = $pdo->query('SELECT COALESCE(SUM(amount), 0) as total FROM fee_payments')->fetchColumn() ?? 0;
$salaries = $pdo->query('SELECT COALESCE(SUM(amount), 0) as total FROM salaries WHERE paid_status="Paid"')->fetchColumn() ?? 0;
$expenses = $pdo->query('SELECT COALESCE(SUM(amount), 0) as total FROM expenses')->fetchColumn() ?? 0;
$profit = $income - $salaries - $expenses;

?>
<h1>Financial Statement (Profit & Loss)</h1>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card bg-gradient-success">
            <h3>Rs. <?=number_format($income, 0)?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-danger">
            <h3>Rs. <?=number_format($salaries, 0)?></h3>
            <p>Salaries Paid</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-warning">
            <h3>Rs. <?=number_format($expenses, 0)?></h3>
            <p>Shared Expenses</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card <?=$profit>=0 ? 'bg-gradient-primary' : 'bg-gradient-danger'?>">
            <h3>Rs. <?=number_format(abs($profit), 0)?></h3>
            <p><?=$profit>=0 ? 'Net Profit' : 'Net Loss'?></p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white"><i class="fas fa-list me-2"></i>Breakdown Summary</div>
            <div class="card-body">
                <table class="table no-hover">
                    <tr>
                        <td>Gross Income (Fees)</td>
                        <td class="text-end text-success">+ <?=number_format($income, 2)?></td>
                    </tr>
                    <tr>
                        <td>Staff Salaries</td>
                        <td class="text-end text-danger">- <?=number_format($salaries, 2)?></td>
                    </tr>
                    <tr>
                        <td>Operational Expenses</td>
                        <td class="text-end text-danger">- <?=number_format($expenses, 2)?></td>
                    </tr>
                    <tr class="fw-bold fs-5 border-top">
                        <td>Net <?=($profit >= 0 ? 'Profit' : 'Loss')?></td>
                        <td class="text-end <?=($profit >= 0 ? 'text-success' : 'text-danger')?>"><?=number_format($profit, 2)?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white"><i class="fas fa-info-circle me-2"></i>Notes</div>
            <div class="card-body">
                <p class="text-muted small">This report considers all verified fee collections, paid staff salaries, and recorded administrative expenses. It provides a real-time overview of the school's financial health.</p>
                <div class="d-grid gap-2 mt-3">
                    <a href="<?=BASE_URL?>admin/income_report.php" class="btn btn-sm btn-outline-primary">View Detailed Income</a>
                    <a href="<?=BASE_URL?>admin/expenses.php" class="btn btn-sm btn-outline-secondary">View All Expenses</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

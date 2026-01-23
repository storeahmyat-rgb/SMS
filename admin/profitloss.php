<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$active_tab = $_GET['tab'] ?? 'overview'; // overview, income, expenses
$range = $_GET['range'] ?? 'month'; // today, week, month, year, custom

// Date Range Calculation
$start_date = '';
$end_date = '';
$date_label = '';

switch($range) {
    case 'today':
        $start_date = date('Y-m-d 00:00:00');
        $end_date = date('Y-m-d 23:59:59');
        $date_label = "Today (" . date('d M Y') . ")";
        break;
    case 'week':
        $start_date = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $end_date = date('Y-m-d 23:59:59', strtotime('sunday this week'));
        $date_label = "This Week (" . date('d M', strtotime($start_date)) . " - " . date('d M', strtotime($end_date)) . ")";
        break;
    case 'year':
        $start_date = date('Y-01-01 00:00:00');
        $end_date = date('Y-12-31 23:59:59');
        $date_label = "This Year (" . date('Y') . ")";
        break;
    case 'month':
    default:
        $start_date = date('Y-m-01 00:00:00');
        $end_date = date('Y-m-t 23:59:59');
        $date_label = "This Month (" . date('F Y') . ")";
        break;
}

// 1. CALCULATE CORE STATS
// Income
$stmt = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM fee_payments WHERE paid_on BETWEEN :s AND :e');
$stmt->execute([':s'=>$start_date, ':e'=>$end_date]);
$total_income = $stmt->fetchColumn() ?? 0;

// Salaries
$stmt = $pdo->prepare('SELECT COALESCE(SUM(total_payout), 0) FROM salaries WHERE paid_status="Paid" AND paid_on BETWEEN :s AND :e');
$stmt->execute([':s'=>$start_date, ':e'=>$end_date]);
$total_salaries = $stmt->fetchColumn() ?? 0;

// General Expenses
$stmt = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM expenses WHERE expense_date BETWEEN :s AND :e');
$stmt->execute([':s'=>date('Y-m-d', strtotime($start_date)), ':e'=>date('Y-m-d', strtotime($end_date))]);
$total_expenses = $stmt->fetchColumn() ?? 0;

$total_outflow = $total_salaries + $total_expenses;
$net_profit = $total_income - $total_outflow;

?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h1 class="h3 mb-1">Financial Intelligence & Analytics</h1>
        <p class="text-muted mb-0">Unified Profit & Loss statement with detailed revenue and expenditure auditing.</p>
    </div>
    <div class="dropdown">
        <button class="btn btn-white border shadow-sm dropdown-toggle px-4 text-primary fw-bold" type="button" data-bs-toggle="dropdown">
            <i class="fas fa-calendar-alt me-2"></i> <?=$date_label?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
            <li><a class="dropdown-item" href="?tab=<?=$active_tab?>&range=today">Today</a></li>
            <li><a class="dropdown-item" href="?tab=<?=$active_tab?>&range=week">This Week</a></li>
            <li><a class="dropdown-item" href="?tab=<?=$active_tab?>&range=month">This Month</a></li>
            <li><a class="dropdown-item" href="?tab=<?=$active_tab?>&range=year">This Year</a></li>
        </ul>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-4 no-print border-0 gap-2 bg-light p-2 rounded shadow-sm">
    <li class="nav-item">
        <a class="nav-link rounded border-0 px-4 py-2 <?= $active_tab == 'overview' ? 'active bg-primary text-white shadow' : 'text-muted' ?>" href="?tab=overview&range=<?=$range?>">
            <i class="fas fa-chart-pie me-2"></i> Financial Overview
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded border-0 px-4 py-2 <?= $active_tab == 'income' ? 'active bg-success text-white shadow' : 'text-muted' ?>" href="?tab=income&range=<?=$range?>">
            <i class="fas fa-hand-holding-usd me-2"></i> Revenue Analysis
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link rounded border-0 px-4 py-2 <?= $active_tab == 'expenses' ? 'active bg-danger text-white shadow' : 'text-muted' ?>" href="?tab=expenses&range=<?=$range?>">
            <i class="fas fa-file-invoice-dollar me-2"></i> Expenditure Audit
        </a>
    </li>
</ul>

<div class="tab-content">
    <?php if ($active_tab == 'overview'): ?>
        <!-- TAB: OVERVIEW -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 border-start border-5 border-success card-stats h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Total Revenue</div>
                        <div class="h2 fw-bold text-success mb-0">Rs. <?=number_format($total_income, 0)?></div>
                        <div class="mt-2 small text-muted">Total fee collections in period</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 border-start border-5 border-danger card-stats h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Total Outflow</div>
                        <div class="h2 fw-bold text-danger mb-0">Rs. <?=number_format($total_outflow, 0)?></div>
                        <div class="mt-2 small text-muted">Salaries + Operating Expenses</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 border-start border-5 border-<?=$net_profit >= 0 ? 'primary' : 'warning'?> card-stats h-100">
                    <div class="card-body">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Net <?= $net_profit >= 0 ? 'Profit' : 'Loss' ?></div>
                        <div class="h2 fw-bold text-<?=$net_profit >= 0 ? 'primary' : 'warning'?> mb-0">Rs. <?=number_format(abs($net_profit), 0)?></div>
                        <div class="mt-2 small text-muted">Institutional net position</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-list-alt me-2 text-primary"></i>Executive Summary (<?=$date_label?>)</h6>
                <button onclick="window.print()" class="btn btn-sm btn-light border no-print"><i class="fas fa-print me-1"></i> Print</button>
            </div>
            <div class="card-body">
                <table class="table table-hover align-middle mb-0">
                    <tr class="fs-5">
                        <td class="ps-3">Total Gross Revenue (Student Fees)</td>
                        <td class="text-end pe-3 text-success fw-bold">+ Rs. <?=number_format($total_income, 2)?></td>
                    </tr>
                    <tr>
                        <td class="ps-3">Staff Payroll (Salaries Paid)</td>
                        <td class="text-end pe-3 text-danger">- Rs. <?=number_format($total_salaries, 2)?></td>
                    </tr>
                    <tr>
                        <td class="ps-3">Administrative / Operating Expenses</td>
                        <td class="text-end pe-3 text-danger">- Rs. <?=number_format($total_expenses, 2)?></td>
                    </tr>
                    <tr class="bg-light fw-bold fs-4 border-top">
                        <td class="ps-3">Net Institutional Position</td>
                        <td class="text-end pe-3 text-<?=$net_profit >= 0 ? 'primary' : 'warning'?>">Rs. <?=number_format($net_profit, 2)?></td>
                    </tr>
                </table>
            </div>
        </div>

    <?php elseif ($active_tab == 'income'): 
        // TAB: INCOME DETAILS
        $stmt = $pdo->prepare('SELECT fp.*, s.full_name, s.admission_no, f.name as fee_name, c.name as class_name 
                               FROM fee_payments fp 
                               JOIN students s ON fp.student_id = s.id 
                               LEFT JOIN fees f ON fp.fee_id = f.id 
                               LEFT JOIN classes c ON s.class_id = c.id
                               WHERE fp.paid_on BETWEEN :s AND :e 
                               ORDER BY fp.paid_on DESC');
        $stmt->execute([':s'=>$start_date, ':e'=>$end_date]);
        $details = $stmt->fetchAll();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-success"><i class="fas fa-dollar-sign me-2"></i>Detailed Income Journal</h6>
            <span class="badge bg-success-subtle text-success px-3 border border-success">Total: Rs. <?=number_format($total_income, 0)?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small text-uppercase">
                        <th class="ps-4">Date</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Fee Type</th>
                        <th>Method</th>
                        <th class="text-end pe-4">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($details as $d): ?>
                    <tr>
                        <td class="ps-4 small fw-bold"><?=date('d M, Y', strtotime($d['paid_on']))?></td>
                        <td>
                            <div class="fw-bold"><?=$d['full_name']?></div>
                            <div class="small text-muted"><?=$d['admission_no']?></div>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?=$d['class_name']?></span></td>
                        <td><span class="small text-muted"><?=$d['fee_name'] ?: 'Custom Contribution'?></span></td>
                        <td><span class="badge bg-primary-subtle text-primary border border-primary px-2"><?=$d['payment_method']?></span></td>
                        <td class="text-end pe-4 fw-bold text-success">Rs. <?=number_format($d['amount'], 2)?></td>
                    </tr>
                    <?php endforeach; if(empty($details)) echo '<tr><td colspan="6" class="text-center py-5">No collections recorded in this period.</td></tr>'; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php elseif ($active_tab == 'expenses'): 
        // TAB: EXPENSE DETAILS (Salaries + General Expenses)
        $query = "
            SELECT 'Salary' as type, t.full_name as head, sa.total_payout as amount, sa.paid_on as record_date, sa.payment_method, t.teacher_id as code
            FROM salaries sa
            JOIN teachers t ON sa.teacher_id = t.id
            WHERE sa.paid_status = 'Paid' AND sa.paid_on BETWEEN :s1 AND :e1
            UNION ALL
            SELECT 'Expense' as type, category as head, amount, expense_date as record_date, 'Cash/Other' as payment_method, '' as code
            FROM expenses
            WHERE expense_date BETWEEN :s2 AND :e2
            ORDER BY record_date DESC
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':s1'=>$start_date, ':e1'=>$end_date,
            ':s2'=>date('Y-m-d', strtotime($start_date)), ':e2'=>date('Y-m-d', strtotime($end_date))
        ]);
        $outflows = $stmt->fetchAll();
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-danger"><i class="fas fa-file-invoice-dollar me-2"></i>Detailed Expenditure Audit</h6>
            <span class="badge bg-danger-subtle text-danger px-3 border border-danger">Total Outflow: Rs. <?=number_format($total_outflow, 0)?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small text-uppercase">
                        <th class="ps-4">Date</th>
                        <th>Category</th>
                        <th>Description / Payee</th>
                        <th>Ref Code</th>
                        <th>Method</th>
                        <th class="text-end pe-4">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($outflows as $o): ?>
                    <tr>
                        <td class="ps-4 small fw-bold"><?=date('d M, Y', strtotime($o['record_date']))?></td>
                        <td>
                            <span class="badge bg-<?=$o['type']=='Salary'?'warning':'danger'?>-subtle text-<?=$o['type']=='Salary'?'warning':'danger'?> border border-<?=$o['type']=='Salary'?'warning':'danger'?> px-2">
                                <?=$o['type']?>
                            </span>
                        </td>
                        <td class="fw-bold"><?=$o['head']?></td>
                        <td class="small text-muted"><?=$o['code'] ?: '-'?></td>
                        <td><span class="badge bg-light text-dark border"><?=$o['payment_method']?></span></td>
                        <td class="text-end pe-4 fw-bold text-danger">Rs. <?=number_format($o['amount'], 2)?></td>
                    </tr>
                    <?php endforeach; if(empty($outflows)) echo '<tr><td colspan="6" class="text-center py-5">No expenditures recorded in this period.</td></tr>'; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.card-stats:hover { transform: translateY(-5px); transition: 0.3s; }
.nav-tabs .nav-link:hover { background-color: rgba(0,0,0,0.05); }
.nav-tabs .nav-link.active { font-weight: bold; }
@media print {
    .sidebar, .top-navbar, .no-print, .nav-tabs { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    .card-header { background: #f8f9fa !important; -webkit-print-color-adjust: exact; }
    body { background: white !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

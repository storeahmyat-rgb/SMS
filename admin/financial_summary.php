<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin', 'accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();

// Get filter parameters
$filter_type = $_GET['filter_type'] ?? 'daily'; // daily, monthly, or yearly
$selected_date = $_GET['selected_date'] ?? date('Y-m-d');
$selected_month = $_GET['selected_month'] ?? date('Y-m');
$selected_year = $_GET['selected_year'] ?? date('Y');

if ($filter_type === 'daily') {
    $date_query = $selected_date;
    $date_label = date('d M Y', strtotime($selected_date));
    
    // Income
    $stmtIncome = $pdo->prepare('SELECT fp.*, f.name as fee_name, s.full_name as student_name, c.name as class_name 
                                 FROM fee_payments fp 
                                 LEFT JOIN fees f ON fp.fee_id = f.id 
                                 LEFT JOIN students s ON fp.student_id = s.id 
                                 LEFT JOIN classes c ON s.class_id = c.id
                                 WHERE DATE(fp.paid_on) = :d');
    $stmtIncome->execute([':d' => $selected_date]);
    
    // Expenses
    $stmtExp = $pdo->prepare('SELECT * FROM expenses WHERE expense_date = :d');
    $stmtExp->execute([':d' => $selected_date]);
    
    // Salaries
    $stmtSal = $pdo->prepare('SELECT sa.*, t.full_name as teacher_name 
                              FROM salaries sa 
                              LEFT JOIN teachers t ON sa.teacher_id = t.id 
                              WHERE DATE(sa.paid_on) = :d AND sa.paid_status = "Paid"');
    $stmtSal->execute([':d' => $selected_date]);
} elseif ($filter_type === 'monthly') {
    $date_query = $selected_month;
    $date_label = date('F Y', strtotime($selected_month . '-01'));
    
    // Income
    $stmtIncome = $pdo->prepare('SELECT fp.*, f.name as fee_name, s.full_name as student_name, c.name as class_name 
                                 FROM fee_payments fp 
                                 LEFT JOIN fees f ON fp.fee_id = f.id 
                                 LEFT JOIN students s ON fp.student_id = s.id 
                                 LEFT JOIN classes c ON s.class_id = c.id
                                 WHERE DATE_FORMAT(fp.paid_on, "%Y-%m") = :m');
    $stmtIncome->execute([':m' => $selected_month]);
    
    // Expenses
    $stmtExp = $pdo->prepare('SELECT * FROM expenses WHERE DATE_FORMAT(expense_date, "%Y-%m") = :m');
    $stmtExp->execute([':m' => $selected_month]);
    
    // Salaries
    $stmtSal = $pdo->prepare('SELECT sa.*, t.full_name as teacher_name 
                              FROM salaries sa 
                              LEFT JOIN teachers t ON sa.teacher_id = t.id 
                              WHERE DATE_FORMAT(sa.paid_on, "%Y-%m") = :m AND sa.paid_status = "Paid"');
    $stmtSal->execute([':m' => $selected_month]);
} else {
    // Yearly Report
    $date_label = "Year " . $selected_year;
    
    // Income
    $stmtIncome = $pdo->prepare('SELECT fp.*, f.name as fee_name, s.full_name as student_name, c.name as class_name 
                                 FROM fee_payments fp 
                                 LEFT JOIN fees f ON fp.fee_id = f.id 
                                 LEFT JOIN students s ON fp.student_id = s.id 
                                 LEFT JOIN classes c ON s.class_id = c.id
                                 WHERE YEAR(fp.paid_on) = :y');
    $stmtIncome->execute([':y' => $selected_year]);
    
    // Expenses
    $stmtExp = $pdo->prepare('SELECT * FROM expenses WHERE YEAR(expense_date) = :y');
    $stmtExp->execute([':y' => $selected_year]);
    
    // Salaries
    $stmtSal = $pdo->prepare('SELECT sa.*, t.full_name as teacher_name 
                              FROM salaries sa 
                              LEFT JOIN teachers t ON sa.teacher_id = t.id 
                              WHERE YEAR(sa.paid_on) = :y AND sa.paid_status = "Paid"');
    $stmtSal->execute([':y' => $selected_year]);
}

$income_list = $stmtIncome->fetchAll();
$expense_list = $stmtExp->fetchAll();
$salary_list = $stmtSal->fetchAll();

$total_income = array_sum(array_column($income_list, 'amount'));
$total_expense = array_sum(array_column($expense_list, 'amount'));
$total_salary = array_sum(array_column($salary_list, 'amount'));
$net_profit = $total_income - ($total_expense + $total_salary);

?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h1 class="h3 mb-0">Financial Summary Report</h1>
        <p class="text-muted mb-0">Detailed breakdown of income and expenditures.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="fas fa-print me-1"></i> Print Report
        </button>
    </div>
</div>

<!-- Filters Section -->
<div class="card shadow-sm border-0 mb-4 no-print">
    <div class="card-body">
        <form method="GET" id="filterForm" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-bold">Report Type</label>
                <select name="filter_type" class="form-select" onchange="toggleInputs(this.value)">
                    <option value="daily" <?= $filter_type === 'daily' ? 'selected' : '' ?>>Daily Report</option>
                    <option value="monthly" <?= $filter_type === 'monthly' ? 'selected' : '' ?>>Monthly Report</option>
                    <option value="yearly" <?= $filter_type === 'yearly' ? 'selected' : '' ?>>Yearly Report</option>
                </select>
            </div>
            
            <div class="col-md-3 filter-input" id="dailyInput" style="<?= $filter_type !== 'daily' ? 'display:none' : '' ?>">
                <label class="form-label fw-bold">Select Date</label>
                <input type="date" name="selected_date" class="form-control" value="<?= $selected_date ?>">
            </div>
            
            <div class="col-md-3 filter-input" id="monthlyInput" style="<?= $filter_type !== 'monthly' ? 'display:none' : '' ?>">
                <label class="form-label fw-bold">Select Month</label>
                <input type="month" name="selected_month" class="form-control" value="<?= $selected_month ?>">
            </div>

            <div class="col-md-3 filter-input" id="yearlyInput" style="<?= $filter_type !== 'yearly' ? 'display:none' : '' ?>">
                <label class="form-label fw-bold">Select Year</label>
                <select name="selected_year" class="form-select">
                    <?php for($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                        <option value="<?= $y ?>" <?= $selected_year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Generate
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Print Header (only visible on print) -->
<div class="d-none d-print-block mb-4 text-center">
    <h2 class="fw-bold">Financial Summary Report</h2>
    <h4 class="text-muted"><?= $date_label ?></h4>
    <hr>
</div>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="p-4 rounded-3 bg-white shadow-sm border-start border-4 border-success">
            <div class="text-muted small fw-bold text-uppercase mb-1">Total Income</div>
            <div class="h3 fw-bold text-success mb-0">Rs. <?= number_format($total_income) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-4 rounded-3 bg-white shadow-sm border-start border-4 border-danger">
            <div class="text-muted small fw-bold text-uppercase mb-1">Operational Exp.</div>
            <div class="h3 fw-bold text-danger mb-0">Rs. <?= number_format($total_expense) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-4 rounded-3 bg-white shadow-sm border-start border-4 border-warning">
            <div class="text-muted small fw-bold text-uppercase mb-1">Salaries Paid</div>
            <div class="h3 fw-bold text-warning mb-0">Rs. <?= number_format($total_salary) ?></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="p-4 rounded-3 <?= $net_profit >= 0 ? 'bg-success text-white' : 'bg-danger text-white' ?> shadow-sm">
            <div class="text-white-50 small fw-bold text-uppercase mb-1"><?= $net_profit >= 0 ? 'Net Profit' : 'Net Loss' ?></div>
            <div class="h3 fw-bold mb-0">Rs. <?= number_format(abs($net_profit)) ?></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Income Breakdown -->
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 text-success"><i class="fas fa-arrow-down me-2"></i>Income / Fee Collection</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Student / Class</th>
                                <th>Fee Category</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($income_list)): ?>
                                <tr><td colspan="4" class="text-center py-4 text-muted">No income recorded for this period.</td></tr>
                            <?php endif; ?>
                            <?php foreach($income_list as $inc): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold"><?= htmlspecialchars($inc['student_name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($inc['class_name']) ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($inc['fee_name'] ?: 'Fee') ?></span></td>
                                <td><?= date('d M Y', strtotime($inc['paid_on'])) ?></td>
                                <td class="text-end pe-4 fw-bold">Rs. <?= number_format($inc['amount']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Expenses Breakdown -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 text-danger"><i class="fas fa-arrow-up me-2"></i>Operational Expenses</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Category</th>
                            <th>Description</th>
                            <th class="text-end pe-4">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($expense_list)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">No expenses recorded.</td></tr>
                        <?php endif; ?>
                        <?php foreach($expense_list as $exp): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= htmlspecialchars($exp['category']) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($exp['description']) ?></td>
                            <td class="text-end pe-4 fw-bold text-danger">Rs. <?= number_format($exp['amount']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Salaries Breakdown -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 text-warning"><i class="fas fa-user-tie me-2"></i>Staff Salaries Paid</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Staff Member</th>
                            <th>Month</th>
                            <th class="text-end pe-4">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($salary_list)): ?>
                            <tr><td colspan="3" class="text-center py-4 text-muted">No salaries paid in this period.</td></tr>
                        <?php endif; ?>
                        <?php foreach($salary_list as $sal): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= htmlspecialchars($sal['teacher_name']) ?></td>
                            <td class="small text-muted"><?= date('M Y', strtotime($sal['month_year'].'-01')) ?></td>
                            <td class="text-end pe-4 fw-bold">Rs. <?= number_format($sal['amount']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleInputs(type) {
    document.querySelectorAll('.filter-input').forEach(el => el.style.display = 'none');
    if (type === 'daily') document.getElementById('dailyInput').style.display = 'block';
    if (type === 'monthly') document.getElementById('monthlyInput').style.display = 'block';
    if (type === 'yearly') document.getElementById('yearlyInput').style.display = 'block';
}
</script>

<style>
@media print {
    .no-print, .sidebar, .top-navbar { display: none !important; }
    .card { border: 1px solid #eee !important; box-shadow: none !important; }
    .bg-success, .bg-danger { border: 1px solid #000 !important; color: #000 !important; background: none !important; }
    .text-white { color: #000 !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

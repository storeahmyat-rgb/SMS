<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
$pdo = getPDO();

// Comprehensive salary calculation
function calcSalary($teacher_id, $month_year, $pdo) {
    [$y, $m] = explode('-', $month_year);
    $start = "$month_year-01";
    $end = date('Y-m-t', strtotime($start));
    
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM teacher_attendance WHERE teacher_id=:t AND attendance_date BETWEEN :s AND :e AND status IN ("Present", "Leave", "Late")');
    $stmt->execute([':t'=>$teacher_id, ':s'=>$start, ':e'=>$end]);
    $workedDays = $stmt->fetchColumn();
    
    $daysInMonth = date('t', strtotime($start));
    $teacher = $pdo->prepare('SELECT salary FROM teachers WHERE id=:t'); 
    $teacher->execute([':t'=>$teacher_id]); 
    $row = $teacher->fetch();
    
    $monthlyBase = floatval($row['salary'] ?? 0);
    $amount = ($monthlyBase / $daysInMonth) * $workedDays;
    return round($amount, 2);
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (isset($_POST['generate'])) {
        $month = $_POST['month'];
        $teachers = $pdo->query('SELECT id FROM teachers WHERE status="Active"')->fetchAll();
        $ins = $pdo->prepare('INSERT INTO salaries (teacher_id, month_year, amount, total_payout, paid_status) VALUES (:t, :m, :a, :a, "Unpaid")');
        foreach ($teachers as $t) {
            $amt = calcSalary($t['id'], $month, $pdo);
            $ins->execute([':t'=>$t['id'], ':m'=>$month, ':a'=>$amt]);
        }
        $msg = 'Salaries generated for '.$month;
    }

    if (isset($_POST['process_payment'])) {
        $id = intval($_POST['salary_id']);
        $method = $_POST['payment_method'];
        $bonus_deduction = floatval($_POST['bonus_deduction'] ?? 0);
        $total_payout = floatval($_POST['total_payout']);
        $notes = $_POST['notes'];

        $stmt = $pdo->prepare('UPDATE salaries SET paid_status="Paid", paid_on=NOW(), payment_method=:m, bonus_deduction=:bd, total_payout=:tp, payment_notes=:n WHERE id=:id');
        $stmt->execute([':m'=>$method, ':bd'=>$bonus_deduction, ':tp'=>$total_payout, ':n'=>$notes, ':id'=>$id]);
        $msg = "Salary processed successfully.";
    }
}

$salaries = $pdo->query('SELECT sa.*, t.full_name, t.teacher_id as t_code FROM salaries sa LEFT JOIN teachers t ON sa.teacher_id=t.id ORDER BY sa.id DESC LIMIT 200')->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Faculty Payouts</h1>
        <p class="text-muted mb-0">Manage monthly salaries and payroll records.</p>
    </div>
    <div class="no-print">
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#generateModal">
            <i class="fas fa-magic me-2"></i> Generate Monthly Payroll
        </button>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-uppercase">
                    <th class="ps-4">Teacher</th>
                    <th>Pay Period</th>
                    <th>Salary (Calculated)</th>
                    <th class="text-center">Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($salaries as $s): ?>
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold"><?=htmlspecialchars($s['full_name'])?></div>
                        <div class="small text-muted"><?=htmlspecialchars($s['t_code'])?></div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark shadow-sm border">
                            <?=date('M Y', strtotime($s['month_year'].'-01'))?>
                        </span>
                    </td>
                    <td class="fw-bold text-primary">Rs. <?=number_format($s['total_payout'] ?? $s['amount'], 2)?></td>
                    <td class="text-center">
                        <?php if($s['paid_status'] === 'Paid'): ?>
                            <span class="badge bg-success-subtle text-success px-3 py-2 border border-success">
                                <i class="fas fa-check-circle me-1"></i> Paid (<?=$s['payment_method']?>)
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning px-3 py-2 border border-warning">
                                <i class="fas fa-clock me-1"></i> Unpaid
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                        <?php if ($s['paid_status']!=='Paid'): ?>
                            <button class="btn btn-sm btn-dark px-3 shadow-sm pay-btn" 
                                    data-id="<?=$s['id']?>" 
                                    data-name="<?=$s['full_name']?>" 
                                    data-amount="<?=$s['amount']?>"
                                    data-bs-toggle="modal" data-bs-target="#payModal">
                                <i class="fas fa-wallet me-1"></i> Pay Now
                            </button>
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <small class="text-muted d-none d-lg-block">On <?=date('d M y', strtotime($s['paid_on']))?></small>
                                <a href="<?=BASE_URL?>admin/salary_slip.php?id=<?=$s['id']?>" target="_blank" class="btn btn-sm btn-light border">
                                    <i class="fas fa-file-invoice text-primary me-1"></i> Slip
                                </a>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Generate Payroll -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Generate Monthly Payroll</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-muted small mb-4">This will calculate salaries for all active teachers based on their attendance records for the selected month.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Target Month</label>
                        <input type="month" name="month" class="form-control form-control-lg" required value="<?=date('Y-m')?>">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="generate" class="btn btn-primary px-4">Start Calculation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Pay Salary -->
<div class="modal fade" id="payModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="post">
                <input type="hidden" name="salary_id" id="modal_salary_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Process Salary Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div id="modal_teacher_info" class="p-3 bg-light rounded mb-3">
                        <div class="small text-muted mb-1">Paying To</div>
                        <div class="h5 fw-bold mb-0" id="modal_teacher_name">---</div>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Base Salary (Attendance)</label>
                            <input type="text" id="modal_base_amount" class="form-control bg-white" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Adjustment (+/-)</label>
                            <input type="number" step="0.01" name="bonus_deduction" id="modal_adj" class="form-control" value="0.00" oninput="recalcTotal()">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Final Amount to Pay</label>
                            <input type="number" step="0.01" name="total_payout" id="modal_total" class="form-control form-control-lg fw-bold text-success" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="Cash">Cash Payment</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Paid via ATM or Deduction due to..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="process_payment" class="btn btn-success px-4 shadow-sm">Confirm & Finalize</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.pay-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.getElementById('modal_salary_id').value = this.dataset.id;
        document.getElementById('modal_teacher_name').innerText = this.dataset.name;
        document.getElementById('modal_base_amount').value = parseFloat(this.dataset.amount).toFixed(2);
        document.getElementById('modal_total').value = parseFloat(this.dataset.amount).toFixed(2);
        document.getElementById('modal_adj').value = "0.00";
    });
});

function recalcTotal() {
    const base = parseFloat(document.getElementById('modal_base_amount').value) || 0;
    const adj = parseFloat(document.getElementById('modal_adj').value) || 0;
    document.getElementById('modal_total').value = (base + adj).toFixed(2);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

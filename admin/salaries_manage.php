<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$teachers = $pdo->query('SELECT id, full_name, salary FROM teachers WHERE status="Active"')->fetchAll();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid = intval($_POST['teacher_id']);
    $month = $_POST['month_year'];
    $amount = floatval($_POST['amount']);
    $stmt = $pdo->prepare('SELECT id FROM salaries WHERE teacher_id = :t AND month_year = :m');
    $stmt->execute([':t'=>$tid, ':m'=>$month]);
    if ($stmt->fetch()) {
        $upd = $pdo->prepare('UPDATE salaries SET amount=:a, paid_status=:ps, paid_on=NOW() WHERE teacher_id=:t AND month_year=:m');
        $upd->execute([':a'=>$amount, ':ps'=>'Paid', ':t'=>$tid, ':m'=>$month]);
    } else {
        $ins = $pdo->prepare('INSERT INTO salaries (teacher_id, month_year, amount, paid_status, paid_on) VALUES (:t, :m, :a, :ps, NOW())');
        $ins->execute([':t'=>$tid, ':m'=>$month, ':a'=>$amount, ':ps'=>'Paid']);
    }
    $msg = 'Salary recorded';
}
$salaries = $pdo->query('SELECT s.*, t.full_name FROM salaries s LEFT JOIN teachers t ON s.teacher_id=t.id ORDER BY s.id DESC LIMIT 100')->fetchAll();

?>
<h1>Manual Salary Payments</h1>
<div class="card p-4 mb-4">
    <h5 class="card-title mb-3"><i class="fas fa-hand-holding-usd me-1"></i> Record Individual Payment</h5>
    <form method="post">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Teacher</label>
                <select name="teacher_id" class="form-select" required>
                    <option value="">-- Select Teacher --</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?=$t['id']?>"><?=htmlspecialchars($t['full_name'].' ($'.$t['salary'].')')?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Month</label>
                <input type="month" name="month_year" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Amount</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" required>
                </div>
            </div>
            <div class="col-md-3 align-self-end">
                <button class="btn btn-primary w-100"><i class="fas fa-check-circle me-1"></i> Record Payment</button>
            </div>
        </div>
    </form>
</div>

<?php if ($msg): ?>
    <div class="alert alert-info shadow-sm border-0 mb-4"><i class="fas fa-info-circle me-1"></i> <?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-history me-2"></i>Recent Payout History</span>
        <span class="badge bg-primary">Last 100 entries</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Teacher Name</th>
                    <th>Pay Period</th>
                    <th>Amount Paid</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Date Recorded</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($salaries as $s): ?>
                <tr>
                    <td class="ps-4"><?=htmlspecialchars($s['id'])?></td>
                    <td class="fw-bold"><?=htmlspecialchars($s['full_name'])?></td>
                    <td><span class="badge bg-light text-dark"><?=date('M Y', strtotime($s['month_year'].'-01'))?></span></td>
                    <td class="fw-bold">Rs. <?=number_format($s['amount'], 2)?></td>
                    <td><span class="badge bg-success">Paid</span></td>
                    <td class="text-end pe-4 text-muted small"><?=date('d M Y, H:i', strtotime($s['paid_on']))?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

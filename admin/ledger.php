<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$students = $pdo->query('SELECT id, admission_no, full_name FROM students WHERE status="Active"')->fetchAll();
$selected_student = intval($_GET['student_id'] ?? 0);

if ($selected_student) {
    $stmt = $pdo->prepare('SELECT s.*, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id=c.id WHERE s.id = :id');
    $stmt->execute([':id'=>$selected_student]);
    $student = $stmt->fetch();

    // Get all fee payments for this student
    $stmt = $pdo->prepare('SELECT fp.*, f.name AS fee_name FROM fee_payments fp LEFT JOIN fees f ON fp.fee_id=f.id WHERE fp.student_id = :sid ORDER BY fp.paid_on DESC');
    $stmt->execute([':sid'=>$selected_student]);
    $payments = $stmt->fetchAll();

    // Calculate totals
    $total_paid = array_reduce($payments, function($carry, $p) { return $carry + floatval($p['amount']); }, 0);
}

?>
<h1>Student Ledger</h1>
<div class="row mb-4">
    <div class="col-md-6">
        <label class="form-label">Search Student</label>
        <select id="student_select" class="form-select">
            <option value="">-- Start typing student name --</option>
            <?php foreach ($students as $s): ?>
                <option value="<?=$s['id']?>" <?=$s['id']==$selected_student?'selected':''?>><?=htmlspecialchars($s['admission_no'].' - '.$s['full_name'])?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<?php if ($selected_student): ?>
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header bg-white"><i class="fas fa-user-circle me-2"></i>Student Information</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4 text-muted">Full Name</div>
                    <div class="col-sm-8 fw-bold"><?=htmlspecialchars($student['full_name'])?></div>
                </div>
                <hr class="my-2">
                <div class="row">
                    <div class="col-sm-4 text-muted">Admission No</div>
                    <div class="col-sm-8"><?=htmlspecialchars($student['admission_no'])?></div>
                </div>
                <hr class="my-2">
                <div class="row">
                    <div class="col-sm-4 text-muted">Class</div>
                    <div class="col-sm-8"><?=htmlspecialchars($student['class_name'])?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card bg-gradient-success h-100 d-flex flex-column justify-content-center">
            <p>Total Contribution</p>
            <h3>Rs. <?=number_format($total_paid, 0)?></h3>
            <small>All time paid fees</small>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-history me-2"></i>Payment History</span>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print"></i></button>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Fee Category</th>
                    <th>Amount</th>
                    <th>Paid On</th>
                    <th>Method</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="6" class="text-center text-muted">No payments recorded yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?=htmlspecialchars($p['id'])?></td>
                    <td><span class="badge bg-light text-dark"><?=htmlspecialchars($p['fee_name'] ?: 'Custom')?></span></td>
                    <td class="fw-bold"><?=number_format($p['amount'], 2)?></td>
                    <td><?=date('d M Y', strtotime($p['paid_on']))?></td>
                    <td><?=htmlspecialchars($p['payment_method'])?></td>
                    <td class="small text-muted"><?=htmlspecialchars($p['note'])?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('student_select').addEventListener('change', function() {
  if (this.value) window.location = '?student_id=' + this.value;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
$pdo = getPDO();
// Comprehensive salary calculation: count Present, Leave, and Late as working days
function calcSalary($teacher_id, $month_year, $pdo) {
    [$y, $m] = explode('-', $month_year);
    $start = "$month_year-01";
    $end = date('Y-m-t', strtotime($start));
    
    // Pakistani context: approved Leave and Late arrivals are usually not deducted from base salary
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM teacher_attendance WHERE teacher_id=:t AND attendance_date BETWEEN :s AND :e AND status IN ("Present", "Leave", "Late")');
    $stmt->execute([':t'=>$teacher_id, ':s'=>$start, ':e'=>$end]);
    $workedDays = $stmt->fetchColumn();
    
    $daysInMonth = date('t', strtotime($start));
    $teacher = $pdo->prepare('SELECT salary FROM teachers WHERE id=:t'); 
    $teacher->execute([':t'=>$teacher_id]); 
    $row = $teacher->fetch();
    
    $monthlyBase = floatval($row['salary'] ?? 0);
    // Prorate by worked days
    $amount = ($monthlyBase / $daysInMonth) * $workedDays;
    return round($amount, 2);
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['generate'])) {
    $month = $_POST['month'];
    // generate salary records for all teachers
    $teachers = $pdo->query('SELECT id FROM teachers')->fetchAll();
    $ins = $pdo->prepare('INSERT INTO salaries (teacher_id, month_year, amount, paid_status) VALUES (:t, :m, :a, "Unpaid")');
    foreach ($teachers as $t) {
        $amt = calcSalary($t['id'], $month, $pdo);
        $ins->execute([':t'=>$t['id'], ':m'=>$month, ':a'=>$amt]);
    }
    $msg = 'Salaries generated for '.$month;
}

if (isset($_POST['mark_paid'])) {
    $id = intval($_POST['salary_id']);
    $stmt = $pdo->prepare('UPDATE salaries SET paid_status="Paid", paid_on=NOW() WHERE id=:id');
    $stmt->execute([':id'=>$id]);
}

$salaries = $pdo->query('SELECT sa.*, t.full_name FROM salaries sa LEFT JOIN teachers t ON sa.teacher_id=t.id ORDER BY sa.id DESC LIMIT 200')->fetchAll();
$teachers = $pdo->query('SELECT id, full_name FROM teachers')->fetchAll();

?>
<div class="row align-items-center mb-4">
    <div class="col">
        <h1>Teacher Salaries</h1>
        <p class="text-muted mb-0">Generate and manage monthly payouts for faculty members.</p>
    </div>
    <div class="col-auto">
        <form method="post" class="d-flex gap-2">
            <input type="month" name="month" class="form-control" required>
            <button name="generate" class="btn btn-primary whitespace-nowrap">
                <i class="fas fa-magic me-1"></i> Generate Salaries
            </button>
        </form>
    </div>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-money-check-alt me-2"></i>Salary Records</span>
        <span class="badge bg-primary">Showing last 200</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Teacher Name</th>
                    <th>Pay Period</th>
                    <th>Calculated Amount</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($salaries as $s): ?>
                <tr>
                    <td class="ps-4"><?=htmlspecialchars($s['id'])?></td>
                    <td class="fw-bold"><?=htmlspecialchars($s['full_name'])?></td>
                    <td><span class="badge bg-light text-dark"><?=date('M Y', strtotime($s['month_year'].'-01'))?></span></td>
                    <td class="fw-bold">Rs. <?=number_format($s['amount'], 2)?></td>
                    <td>
                        <span class="badge <?=($s['paid_status']=='Paid'?'bg-success':'bg-warning')?>">
                            <?=htmlspecialchars($s['paid_status'])?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <?php if ($s['paid_status']!=='Paid'): ?>
                            <form method="post" style="display:inline">
                                <input type="hidden" name="salary_id" value="<?=$s['id']?>">
                                <button name="mark_paid" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-check me-1"></i> Mark Paid
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <small class="text-muted">Paid on <?=date('d M Y', strtotime($s['paid_on']))?></small>
                                <a href="<?=BASE_URL?>admin/salary_slip.php?id=<?=$s['id']?>" target="_blank" class="btn btn-sm btn-light">
                                    <i class="fas fa-print text-primary"></i> Slip
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

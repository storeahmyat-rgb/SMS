<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$students = $pdo->query('SELECT id, admission_no, full_name FROM students WHERE status="Active"')->fetchAll();
$selected_student = intval($_GET['student_id'] ?? 0);
$selected_month = $_GET['month'] ?? date('Y-m');

if ($selected_student) {
    $stmt = $pdo->prepare('SELECT s.*, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id=c.id WHERE s.id = :id');
    $stmt->execute([':id'=>$selected_student]);
    $student = $stmt->fetch();

    // Get attendance for the month with recorded_by user name
    $stmt = $pdo->prepare('SELECT sa.*, u.full_name AS recorder_name FROM student_attendance sa LEFT JOIN users u ON sa.recorded_by = u.id WHERE sa.student_id = :sid AND DATE_FORMAT(sa.attendance_date, "%Y-%m") = :m ORDER BY sa.attendance_date');
    $stmt->execute([':sid'=>$selected_student, ':m'=>$selected_month]);
    $attendance = $stmt->fetchAll();

    $summary = ['Present'=>0, 'Absent'=>0, 'Leave'=>0, 'Late'=>0];
    foreach ($attendance as $a) {
        $summary[$a['status']]++;
    }
}

?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Monthly Attendance Report</h1>
    <?php if ($selected_student): ?>
    <button onclick="window.print()" class="btn btn-outline-primary no-print">
        <i class="fas fa-print me-1"></i> Print Official Report
    </button>
    <?php endif; ?>
</div>
<div class="card p-4 mb-4">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Search Student</label>
            <select id="student_select" class="form-select">
                <option value="">-- Start typing student name --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?=$s['id']?>" <?=$s['id']==$selected_student?'selected':''?>><?=htmlspecialchars($s['admission_no'].' - '.$s['full_name'])?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Select Month</label>
            <input type="month" id="month_select" class="form-control" value="<?=htmlspecialchars($selected_month)?>">
        </div>
    </div>
</div>

<?php if ($selected_student): ?>
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-light p-3 rounded-circle">
                <i class="fas fa-user-graduate fa-2x text-primary"></i>
            </div>
            <div>
                <h4 class="mb-0"><?=htmlspecialchars($student['full_name'])?></h4>
                <p class="text-muted mb-0">Admission: <?=htmlspecialchars($student['admission_no'])?> | Class: <?=htmlspecialchars($student['class_name'])?></p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card bg-gradient-success">
            <h3><?=$summary['Present']?></h3>
            <p>Days Present</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-danger">
            <h3><?=$summary['Absent']?></h3>
            <p>Days Absent</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-warning">
            <h3><?=$summary['Leave']?></h3>
            <p>On Leave</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-primary">
            <h3><?=$summary['Late']?></h3>
            <p>Late Arrival</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white"><i class="fas fa-calendar-alt me-2"></i>Detailed Records (<?=htmlspecialchars($selected_month)?>)</div>
    <div class="card-body">
        <table class="table">
            <thead><tr><th>Date</th><th>Status</th><th>Recorded By</th></tr></thead>
            <tbody>
            <?php if (empty($attendance)): ?>
                <tr><td colspan="3" class="text-center text-muted">No attendance records found for this month.</td></tr>
            <?php endif; ?>
            <?php foreach ($attendance as $a): ?>
                <tr>
                    <td><?=date('d M Y, D', strtotime($a['attendance_date']))?></td>
                    <td>
                        <span class="badge <?php 
                            echo ($a['status']=='Present' ? 'bg-success' : 
                                 ($a['status']=='Absent' ? 'bg-danger' : 'bg-warning')); 
                        ?>">
                            <?=htmlspecialchars($a['status'])?>
                        </span>
                    </td>
                    <td class="small text-muted"><?=htmlspecialchars($a['recorder_name'] ?: 'Auto System')?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<style>
@media print {
    .sidebar, .top-navbar, .no-print, .card-header button {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    body {
        background: white !important;
    }
}
</style>
<?php endif; ?>

<script>
document.getElementById('student_select').addEventListener('change', function() {
  const month = document.getElementById('month_select').value;
  if (this.value) window.location = '?student_id=' + this.value + '&month=' + month;
});
document.getElementById('month_select').addEventListener('change', function() {
  const sid = document.getElementById('student_select').value;
  if (sid) window.location = '?student_id=' + sid + '&month=' + this.value;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

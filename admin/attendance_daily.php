<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$date = $_GET['date'] ?? date('Y-m-d');

// Get all classes
$classes = $pdo->query('SELECT * FROM classes')->fetchAll();

// Get absentees for today
$stmt = $pdo->prepare('
    SELECT sa.*, s.full_name, s.admission_no, c.name AS class_name, se.name AS section_name 
    FROM student_attendance sa 
    JOIN students s ON sa.student_id = s.id 
    JOIN classes c ON sa.class_id = c.id 
    LEFT JOIN sections se ON sa.section_id = se.id
    WHERE sa.attendance_date = :d AND sa.status = "Absent"
    ORDER BY c.id, se.id, s.full_name
');
$stmt->execute([':d' => $date]);
$absentees = $stmt->fetchAll();

// Get summary counts
$summaryStmt = $pdo->prepare('
    SELECT status, COUNT(*) as count 
    FROM student_attendance 
    WHERE attendance_date = :d 
    GROUP BY status
');
$summaryStmt->execute([':d' => $date]);
$counts = $summaryStmt->fetchAll(PDO::FETCH_KEY_PAIR);

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Daily Attendance Summary</h1>
        <p class="text-muted mb-0">Overview of student presence across the institution.</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <input type="date" id="date_picker" class="form-control" value="<?=htmlspecialchars($date)?>">
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print me-1"></i> Print Report</button>
    </div>
</div>

<div class="row mb-4 no-print">
    <div class="col-md-3">
        <div class="stats-card bg-gradient-success">
            <h3><?=($counts['Present'] ?? 0)?></h3>
            <p>Present Students</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-danger">
            <h3><?=($counts['Absent'] ?? 0)?></h3>
            <p>Absent Students</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-warning">
            <h3><?=($counts['Leave'] ?? 0)?></h3>
            <p>On Approved Leave</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-primary">
            <h3><?=($counts['Late'] ?? 0)?></h3>
            <p>Late Arrivals</p>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-danger"><i class="fas fa-user-times me-2"></i>Absentee List (<?=date('d M Y', strtotime($date))?>)</h5>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Admission #</th>
                    <th>Student Name</th>
                    <th>Class & Section</th>
                    <th class="text-center">Status</th>
                    <th class="no-print">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($absentees)): ?>
                    <tr><td colspan="5" class="text-center p-5 text-muted">MashaAllah! All students are present today.</td></tr>
                <?php endif; ?>
                <?php foreach ($absentees as $a): ?>
                    <tr>
                        <td class="ps-4 text-muted"><?=htmlspecialchars($a['admission_no'])?></td>
                        <td class="fw-bold"><?=htmlspecialchars($a['full_name'])?></td>
                        <td><?=htmlspecialchars($a['class_name'])?> - <?=htmlspecialchars($a['section_name'] ?: 'No Section')?></td>
                        <td class="text-center">
                            <span class="badge bg-danger">Absent</span>
                        </td>
                        <td class="no-print">
                            <a href="<?=BASE_URL?>admin/attendance_report.php?student_id=<?=$a['student_id']?>" class="btn btn-sm btn-outline-info">Ledger</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('date_picker').addEventListener('change', function() {
    window.location.href = '?date=' + this.value;
});
</script>

<style>
@media print {
    .sidebar, .top-navbar, .no-print { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    .card { border: 1px solid #ddd !important; box-shadow: none !important; }
    body { background: white !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['teacher']);
require_once __DIR__ . '/../includes/header.php';
?>
<?php
$pdo = getPDO();
$teacher_id_raw = $_SESSION['user_id']; // This might need mapping to teachers table id
// Get teacher's own details
$stmt = $pdo->prepare('SELECT id FROM teachers WHERE full_name = :n LIMIT 1');
$stmt->execute([':n' => $_SESSION['username']]);
$teacher_id = $stmt->fetchColumn();

$counts = [];
$counts['students'] = $pdo->query('SELECT COUNT(*) FROM students WHERE status="Active"')->fetchColumn();
$counts['attendance'] = $teacher_id ? $pdo->prepare('SELECT COUNT(*) FROM teacher_attendance WHERE teacher_id = ? AND status="Present" AND MONTH(attendance_date) = MONTH(NOW())')->execute([$teacher_id]) : 0;
// Note: execute returns bool, need fetchColumn
if ($teacher_id) {
    $st = $pdo->prepare('SELECT COUNT(*) FROM teacher_attendance WHERE teacher_id = ? AND status="Present" AND MONTH(attendance_date) = MONTH(NOW())');
    $st->execute([$teacher_id]);
    $counts['attendance'] = $st->fetchColumn();
} else {
    $counts['attendance'] = 0;
}
?>
<h1>Faculty Control Panel</h1>
<p class="text-muted">Welcome back, <?=htmlspecialchars($_SESSION['username'])?>. Manage your academic responsibilities.</p>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="stats-card bg-primary text-white p-4 rounded-3 h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mb-0 text-white-50">Accessible Students</h5>
                    <h2 class="display-6 fw-bold mb-0"><?=intval($counts['students'])?></h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fas fa-user-graduate"></i></div>
            </div>
            <a href="<?=BASE_URL?>admin/students.php" class="mt-3 d-inline-block text-white fw-bold text-decoration-none small">
                View Student Directory <i class="fas fa-chevron-right ms-1"></i>
            </a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stats-card bg-success text-white p-4 rounded-3 h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mb-0 text-white-50">My Monthly Attendance</h5>
                    <h2 class="display-6 fw-bold mb-0"><?=intval($counts['attendance'])?> <small class="fs-5 opacity-50">Days</small></h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fas fa-calendar-check"></i></div>
            </div>
            <p class="mt-3 mb-0 small"><i class="fas fa-info-circle me-1"></i> Attendance records for <?=date('F Y')?></p>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white"><i class="fas fa-bolt me-2"></i>Quick Faculty Actions</div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <a href="<?=BASE_URL?>admin/attendance.php" class="btn btn-outline-primary btn-lg w-100 p-4 text-start">
                    <i class="fas fa-user-clock d-block mb-3 fs-3"></i>
                    <strong>Mark Student Attendance</strong>
                    <div class="small opacity-75">Daily presence tracking for assigned classes</div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="<?=BASE_URL?>admin/exams.php" class="btn btn-outline-success btn-lg w-100 p-4 text-start">
                    <i class="fas fa-file-signature d-block mb-3 fs-3"></i>
                    <strong>Examination Portal</strong>
                    <div class="small opacity-75">Schedule exams and record performance marks</div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

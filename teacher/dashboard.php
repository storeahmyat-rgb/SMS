<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['teacher']);
require_once __DIR__ . '/../includes/header.php';
?>
<?php
$pdo = getPDO();

// Get teacher's ID from username mapping
$stmt = $pdo->prepare('SELECT id FROM teachers WHERE full_name = :n LIMIT 1');
$stmt->execute([':n' => $_SESSION['username']]);
$teacher_id = $stmt->fetchColumn();

// Count assigned sections
$assigned_sections = 0;
if ($teacher_id) {
    $stSections = $pdo->prepare('SELECT COUNT(*) FROM sections WHERE class_teacher_id = :t');
    $stSections->execute([':t' => $teacher_id]);
    $assigned_sections = $stSections->fetchColumn();
}

// Get today's attendance count
$today_attendance = 0;
if ($teacher_id) {
    $stToday = $pdo->prepare('SELECT COUNT(DISTINCT s.student_id) 
                              FROM student_attendance s 
                              JOIN sections sec ON s.section_id = sec.id 
                              WHERE sec.class_teacher_id = :t 
                              AND DATE(s.attendance_date) = CURDATE()');
    $stToday->execute([':t' => $teacher_id]);
    $today_attendance = $stToday->fetchColumn();
}
?>
<h1>Faculty Control Panel</h1>
<p class="text-muted">Welcome back, <?=htmlspecialchars($_SESSION['username'])?>. Manage your class attendance.</p>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="stats-card bg-primary text-white p-4 rounded-3 h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mb-0 text-white-50">Assigned Sections</h5>
                    <h2 class="display-6 fw-bold mb-0"><?=intval($assigned_sections)?></h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fas fa-chalkboard"></i></div>
            </div>
            <p class="mt-3 mb-0 small"><i class="fas fa-info-circle me-1"></i> You are responsible for these class sections</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stats-card bg-success text-white p-4 rounded-3 h-100">
            <div class="d-flex justify-content-between">
                <div>
                    <h5 class="mb-0 text-white-50">Today's Records</h5>
                    <h2 class="display-6 fw-bold mb-0"><?=intval($today_attendance)?> <small class="fs-5 opacity-50">Students</small></h2>
                </div>
                <div class="fs-1 opacity-50"><i class="fas fa-calendar-check"></i></div>
            </div>
            <p class="mt-3 mb-0 small"><i class="fas fa-clock me-1"></i> Attendance marked for <?=date('d M Y')?></p>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white"><i class="fas fa-bolt me-2"></i>Quick Actions</div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-6">
                <a href="<?=BASE_URL?>admin/attendance.php" class="btn btn-outline-primary btn-lg w-100 p-4 text-start">
                    <i class="fas fa-user-clock d-block mb-3 fs-3"></i>
                    <strong>Mark Student Attendance</strong>
                    <div class="small opacity-75">Daily presence tracking for your assigned sections</div>
                </a>
            </div>
            <div class="col-md-6">
                <a href="<?=BASE_URL?>admin/attendance_report.php" class="btn btn-outline-success btn-lg w-100 p-4 text-start">
                    <i class="fas fa-clipboard-list d-block mb-3 fs-3"></i>
                    <strong>View Reports</strong>
                    <div class="small opacity-75">Check attendance logs and generate reports</div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

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

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0 border-start border-5 border-primary h-100">
            <div class="card-body p-3 p-md-4">
                <div class="text-muted small text-uppercase fw-bold mb-1">Assigned Sections</div>
                <div class="h2 fw-bold text-primary mb-0"><?=intval($assigned_sections)?></div>
                <div class="mt-2 small text-muted">Groups under your supervision</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card shadow-sm border-0 border-start border-5 border-success h-100">
            <div class="card-body p-3 p-md-4">
                <div class="text-muted small text-uppercase fw-bold mb-1">Today's Presence</div>
                <div class="h2 fw-bold text-success mb-0"><?=intval($today_attendance)?> <small class="fs-6 opacity-75">Students</small></div>
                <div class="mt-2 small text-muted">Marked for <?=date('d M Y')?></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white"><i class="fas fa-bolt me-2"></i>Quick Actions</div>
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <a href="<?=BASE_URL?>admin/attendance.php" class="btn btn-outline-primary btn-lg w-100 p-3 p-md-4 text-start h-100 shadow-sm border-2">
                    <i class="fas fa-user-clock d-block mb-2 mb-md-3 fs-3"></i>
                    <strong class="d-block mb-1">Mark Student Attendance</strong>
                    <div class="small opacity-75">Daily presence tracking for your classes</div>
                </a>
            </div>
            <div class="col-12 col-md-6">
                <a href="<?=BASE_URL?>admin/attendance_center.php" class="btn btn-outline-success btn-lg w-100 p-3 p-md-4 text-start h-100 shadow-sm border-2">
                    <i class="fas fa-chart-line d-block mb-2 mb-md-3 fs-3 text-success"></i>
                    <strong class="d-block mb-1">Attendance Analytics</strong>
                    <div class="small opacity-75">Track trends and generate detailed reports</div>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

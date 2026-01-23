<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin', 'teacher', 'accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
?>
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single { height: 38px; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 5px; }
.select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
</style>
<?php

$pdo = getPDO();
$active_tab = $_GET['tab'] ?? 'student'; // student, faculty
$range = $_GET['range'] ?? 'daily'; // daily, weekly, monthly
$scope = $_GET['scope'] ?? 'all'; // all, single

// Date/Period parameters
$date = $_GET['date'] ?? date('Y-m-d');
$week = $_GET['week'] ?? date('Y-\WW'); // e.g. 2024-W12
$month = $_GET['month'] ?? date('Y-m');

// Entity parameters
$class_id = intval($_GET['class_id'] ?? 0);
$section_id = intval($_GET['section_id'] ?? 0);
$student_id = intval($_GET['student_id'] ?? 0);
$teacher_id = intval($_GET['teacher_id'] ?? 0);
$absent_only = isset($_GET['absent_only']);

// Data Fetching
// TEACHER ROLE RESTRICTIONS
$is_teacher = ($_SESSION['role'] === 'teacher');
$logged_teacher_id = 0;
if ($is_teacher) {
    $stmt = $pdo->prepare('SELECT id FROM teachers WHERE full_name = :n LIMIT 1');
    $stmt->execute([':n' => $_SESSION['username']]);
    $logged_teacher_id = $stmt->fetchColumn();
    
    // If they are on faculty tab, force single scope and their own record
    if ($active_tab == 'faculty') { 
        $scope = 'single'; 
        $teacher_id = $logged_teacher_id;
    }
}

if ($is_teacher) {
    $classes = $pdo->prepare('SELECT DISTINCT c.* FROM classes c JOIN sections s ON c.id = s.class_id WHERE s.class_teacher_id = ? ORDER BY c.name');
    $classes->execute([$logged_teacher_id]);
    $classes = $classes->fetchAll();
} else {
    $classes = $pdo->query('SELECT * FROM classes ORDER BY name')->fetchAll();
}

$sections = null;
if ($class_id) {
    if ($is_teacher) {
        $sections = $pdo->prepare('SELECT * FROM sections WHERE class_id = ? AND class_teacher_id = ?');
        $sections->execute([$class_id, $logged_teacher_id]);
    } else {
        $sections = $pdo->prepare('SELECT * FROM sections WHERE class_id = ?');
        $sections->execute([$class_id]);
    }
    $sections = ($sections) ? $sections->fetchAll() : [];
}

// Helper for Weekly range
function getWeekRange($weekStr) {
    if (preg_match('/(\d+)-W(\d+)/', $weekStr, $matches)) {
        $year = $matches[1];
        $week = $matches[2];
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $ret['start'] = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $ret['end'] = $dto->format('Y-m-d');
        return $ret;
    }
    return ['start' => date('Y-m-d'), 'end' => date('Y-m-d')];
}
$weekRange = getWeekRange($week);

?>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h1 class="h3 mb-1">Attendance Intelligence Center</h1>
        <p class="text-muted mb-0">Premium unified dashboard for institutional attendance analytics.</p>
    </div>
    <div>
        <button onclick="window.print()" class="btn btn-primary shadow-sm">
            <i class="fas fa-print me-2"></i> Print Official Report
        </button>
    </div>
</div>

<!-- Main Navigation Tabs -->
<ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm no-print" id="attendanceMainTabs">
    <li class="nav-item">
        <a class="nav-link <?= $active_tab == 'student' ? 'active shadow' : '' ?>" href="?tab=student&range=<?=$range?>&scope=<?=$scope?>">
            <i class="fas fa-user-graduate me-2"></i> Student Center
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $active_tab == 'faculty' ? 'active shadow' : '' ?>" href="?tab=faculty&range=<?=$range?>&scope=<?=$scope?>">
            <i class="fas fa-chalkboard-teacher me-2"></i> Faculty Center
        </a>
    </li>
</ul>

<!-- Filter Bar -->
<div class="card shadow-sm border-0 mb-4 no-print bg-light">
    <div class="card-body p-2 p-md-3">
        <form method="GET" class="row g-2 g-md-3 align-items-end">
            <input type="hidden" name="tab" value="<?=$active_tab?>">
            
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold">Report Range</label>
                <select name="range" class="form-select" onchange="this.form.submit()">
                    <option value="daily" <?=$range=='daily'?'selected':''?>>Daily View</option>
                    <option value="weekly" <?=$range=='weekly'?'selected':''?>>Weekly Ledger</option>
                    <option value="monthly" <?=$range=='monthly'?'selected':''?>>Monthly Ledger</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold">Scope</label>
                <select name="scope" class="form-select" onchange="this.form.submit()">
                    <option value="all" <?=$scope=='all'?'selected':''?>>All Members</option>
                    <option value="single" <?=$scope=='single'?'selected':''?>>Single Person</option>
                </select>
            </div>

            <!-- Time Selectors -->
            <?php if ($range == 'daily'): ?>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold">Pick Date</label>
                <input type="date" name="date" class="form-control" value="<?=$date?>" onchange="this.form.submit()">
            </div>
            <?php elseif ($range == 'weekly'): ?>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold">Pick Week</label>
                <input type="week" name="week" class="form-control" value="<?=$week?>" onchange="this.form.submit()">
            </div>
            <?php elseif ($range == 'monthly'): ?>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold">Pick Month</label>
                <input type="month" name="month" class="form-control" value="<?=$month?>" onchange="this.form.submit()">
            </div>
            <?php endif; ?>

            <!-- Entity Selectors (Student Specific) -->
            <?php if ($active_tab == 'student'): ?>
                <?php if ($scope == 'all'): ?>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold">Class</label>
                    <select name="class_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Classes --</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?=$c['id']?>" <?=$c['id']==$class_id?'selected':''?>><?=$c['name']?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold">Section</label>
                    <select name="section_id" class="form-select" <?=$class_id?'':'disabled'?> onchange="this.form.submit()">
                        <option value="">-- All --</option>
                        <?php if($sections) foreach($sections as $s): ?>
                            <option value="<?=$s['id']?>" <?=$s['id']==$section_id?'selected':''?>><?=$s['name']?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: 
                    $q = 'SELECT id, full_name, admission_no FROM students WHERE status="Active"';
                    $p = [];
                    if ($is_teacher) {
                        $q .= ' AND section_id IN (SELECT id FROM sections WHERE class_teacher_id = ?)';
                        $p[] = $logged_teacher_id;
                    }
                    $q .= ' ORDER BY full_name';
                    $students_list = $pdo->prepare($q);
                    $students_list->execute($p);
                    $students_list = $students_list->fetchAll();
                ?>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Search Student</label>
                    <select name="student_id" class="form-select select2" onchange="this.form.submit()">
                        <option value="">-- Select Student --</option>
                        <?php foreach($students_list as $sl): ?>
                            <option value="<?=$sl['id']?>" <?=$sl['id']==$student_id?'selected':''?>><?=$sl['admission_no']?> - <?=$sl['full_name']?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <!-- Faculty Selectors -->
                <?php if ($scope == 'single'): 
                    $q = 'SELECT id, full_name, teacher_id FROM teachers WHERE status="Active"';
                    $p = [];
                    if ($is_teacher) {
                        $q .= ' AND id = ?';
                        $p[] = $logged_teacher_id;
                    }
                    $q .= ' ORDER BY full_name';
                    $teachers_list = $pdo->prepare($q);
                    $teachers_list->execute($p);
                    $teachers_list = $teachers_list->fetchAll();
                ?>
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Search Faculty</label>
                    <select name="teacher_id" class="form-select select2" onchange="this.form.submit()" <?=$is_teacher?'disabled':''?>>
                        <?php if(!$is_teacher): ?><option value="">-- Select Faculty --</option><?php endif; ?>
                        <?php foreach($teachers_list as $tl): ?>
                            <option value="<?=$tl['id']?>" <?=$tl['id']==$teacher_id?'selected':''?>><?=$tl['full_name']?> (<?=$tl['teacher_id']?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <?php if($is_teacher): ?><input type="hidden" name="teacher_id" value="<?=$logged_teacher_id?>"><?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-primary w-100 shadow-sm py-2"><i class="fas fa-sync-alt me-1"></i> Apply</button>
            </div>
        </form>
    </div>
</div>

<div class="tab-content border-0">
    <!-- STUDENT CENTER LOGIC -->
    <?php if ($active_tab == 'student'): ?>
        <?php
        if ($range == 'daily' && $scope == 'all'): 
            // Existing Daily All Students logic
            $query = "SELECT s.id, s.full_name, s.admission_no, s.roll_no, c.name AS class_name, se.name AS section_name, sa.status, sa.recorded_at, sa.in_time, sa.out_time
                      FROM students s
                      JOIN classes c ON s.class_id = c.id
                      LEFT JOIN sections se ON s.section_id = se.id
                      LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = :date
                      WHERE s.status = 'Active'";
            $params = [':date' => $date];
            if ($class_id) { $query .= " AND s.class_id = :cid"; $params[':cid'] = $class_id; }
            if ($section_id) { $query .= " AND s.section_id = :sid"; $params[':sid'] = $section_id; }
            if ($is_teacher) { $query .= " AND se.class_teacher_id = :tid"; $params[':tid'] = $logged_teacher_id; }
            $query .= " ORDER BY c.id, se.id, s.roll_no, s.full_name";
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $attendance = $stmt->fetchAll();
            include 'tabs/student_daily_all.php';
        
        elseif ($range == 'weekly' && $scope == 'all'):
            // Grid for all students in class/section for the week
            $query = "SELECT s.id, s.full_name, s.roll_no, c.name AS class_name, se.name AS section_name
                      FROM students s
                      JOIN classes c ON s.class_id = c.id
                      LEFT JOIN sections se ON s.section_id = se.id
                      WHERE s.status = 'Active'";
            $params = [];
            if ($class_id) { $query .= " AND s.class_id = :cid"; $params[':cid'] = $class_id; }
            if ($section_id) { $query .= " AND s.section_id = :sid"; $params[':sid'] = $section_id; }
            if ($is_teacher) { $query .= " AND se.class_teacher_id = :tid"; $params[':tid'] = $logged_teacher_id; }
            $query .= " ORDER BY c.id, se.id, s.roll_no";
            $students_list = $pdo->prepare($query);
            $students_list->execute($params);
            $students = $students_list->fetchAll();
            
            $att_stmt = $pdo->prepare("SELECT student_id, attendance_date, status FROM student_attendance WHERE attendance_date BETWEEN :s AND :e");
            $att_stmt->execute([':s'=>$weekRange['start'], ':e'=>$weekRange['end']]);
            $att_data = [];
            while($row = $att_stmt->fetch()) { $att_data[$row['student_id']][$row['attendance_date']] = $row['status']; }
            
            include 'tabs/student_weekly_all.php';

        elseif ($scope == 'single' && $student_id):
            // Single Student Ledger (Weekly or Monthly)
            $st_stmt = $pdo->prepare('SELECT s.*, c.name as class_name FROM students s JOIN classes c ON s.class_id=c.id WHERE s.id=?');
            $st_stmt->execute([$student_id]);
            $member_meta = $st_stmt->fetch();
            
            if ($range == 'daily') {
                $lg_stmt = $pdo->prepare('SELECT * FROM student_attendance WHERE student_id=? AND attendance_date = ?');
                $lg_stmt->execute([$student_id, $date]);
            } elseif ($range == 'weekly') {
                $lg_stmt = $pdo->prepare('SELECT * FROM student_attendance WHERE student_id=? AND attendance_date BETWEEN ? AND ? ORDER BY attendance_date');
                $lg_stmt->execute([$student_id, $weekRange['start'], $weekRange['end']]);
            } else {
                $lg_stmt = $pdo->prepare('SELECT * FROM student_attendance WHERE student_id=? AND DATE_FORMAT(attendance_date, "%Y-%m") = ? ORDER BY attendance_date');
                $lg_stmt->execute([$student_id, $month]);
            }
            $ledger_data = $lg_stmt->fetchAll();
            include 'tabs/single_ledger.php';
        
        elseif ($range == 'monthly' && $scope == 'all'):
            // Monthly Summary Grid logic
            include 'tabs/student_monthly_summary.php';
        endif; ?>

    <!-- FACULTY CENTER LOGIC -->
    <?php else: ?>
        <?php
        if ($range == 'daily' && $scope == 'all'): 
            $query = "SELECT t.id, t.full_name, t.teacher_id, t.designation, ta.status, ta.in_time, ta.out_time
                      FROM teachers t
                      LEFT JOIN teacher_attendance ta ON t.id = ta.teacher_id AND ta.attendance_date = :date
                      WHERE t.status = 'Active'";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':date' => $date]);
            $attendance = $stmt->fetchAll();
            include 'tabs/faculty_daily_all.php';
        
        elseif ($range == 'weekly' && $scope == 'all'):
            $query = "SELECT id, full_name, teacher_id FROM teachers WHERE status = 'Active'";
            $faculty = $pdo->query($query)->fetchAll();
            $att_stmt = $pdo->prepare("SELECT teacher_id, attendance_date, status FROM teacher_attendance WHERE attendance_date BETWEEN :s AND :e");
            $att_stmt->execute([':s'=>$weekRange['start'], ':e'=>$weekRange['end']]);
            $att_data = [];
            while($row = $att_stmt->fetch()) { $att_data[$row['teacher_id']][$row['attendance_date']] = $row['status']; }
            include 'tabs/faculty_weekly_all.php';

        elseif ($scope == 'single' && $teacher_id):
            $t_stmt = $pdo->prepare('SELECT * FROM teachers WHERE id=?');
            $t_stmt->execute([$teacher_id]);
            $member_meta = $t_stmt->fetch();
            
            if ($range == 'daily') {
                $lg_stmt = $pdo->prepare('SELECT * FROM teacher_attendance WHERE teacher_id=? AND attendance_date = ?');
                $lg_stmt->execute([$teacher_id, $date]);
            } elseif ($range == 'weekly') {
                $lg_stmt = $pdo->prepare('SELECT * FROM teacher_attendance WHERE teacher_id=? AND attendance_date BETWEEN ? AND ? ORDER BY attendance_date');
                $lg_stmt->execute([$teacher_id, $weekRange['start'], $weekRange['end']]);
            } else {
                $lg_stmt = $pdo->prepare('SELECT * FROM teacher_attendance WHERE teacher_id=? AND DATE_FORMAT(attendance_date, "%Y-%m") = ? ORDER BY attendance_date');
                $lg_stmt->execute([$teacher_id, $month]);
            }
            $ledger_data = $lg_stmt->fetchAll();
            include 'tabs/single_ledger.php';
        endif; ?>
    <?php endif; ?>
</div>

<style>
.bg-gradient-light { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); }
.stats-card-premium { padding: 1.25rem; border-radius: 12px; transition: 0.3s; }
.stats-card-premium:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
.border-dashed { border: 2px dashed #dee2e6 !important; }
.avatar { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; }
.att-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
.att-P { background-color: #198754; }
.att-A { background-color: #dc3545; }
.att-L { background-color: #ffc107; }
.table-grid th, .table-grid td { text-align: center; padding: 10px 5px; border: 1px solid #eee; }
@media print {
    .sidebar, .top-navbar, .no-print { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
}
</style>

<!-- Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({
        placeholder: "Search list...",
        allowClear: true,
        width: '100%'
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

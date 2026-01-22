<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin', 'teacher']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$date = $_GET['date'] ?? date('Y-m-d');
$class_id = intval($_GET['class_id'] ?? 0);
$section_id = intval($_GET['section_id'] ?? 0);

// Get classes for filter
$classes = $pdo->query('SELECT * FROM classes')->fetchAll();

// Get sections for filtered class
$sections = [];
if ($class_id) {
    $stmt = $pdo->prepare('SELECT * FROM sections WHERE class_id = :c');
    $stmt->execute([':c' => $class_id]);
    $sections = $stmt->fetchAll();
}

// Main Query: Get all students and join their attendance status for the selected date
$query = "
    SELECT s.id, s.full_name, s.admission_no, s.roll_no, c.name AS class_name, se.name AS section_name, sa.status, sa.recorded_at
    FROM students s
    JOIN classes c ON s.class_id = c.id
    LEFT JOIN sections se ON s.section_id = se.id
    LEFT JOIN student_attendance sa ON s.id = sa.student_id AND sa.attendance_date = :date
    WHERE s.status = 'Active'
";

$params = [':date' => $date];

if ($class_id) {
    $query .= " AND s.class_id = :cid";
    $params[':cid'] = $class_id;
}
if ($section_id) {
    $query .= " AND s.section_id = :sid";
    $params[':sid'] = $section_id;
}

$query .= " ORDER BY c.id, se.id, s.roll_no, s.full_name";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$attendance = $stmt->fetchAll();

// Summary stats for the view
$present = 0; $absent = 0; $leave = 0; $late = 0; $pending = 0;
foreach ($attendance as $a) {
    if (!$a['status']) $pending++;
    elseif ($a['status'] == 'Present') $present++;
    elseif ($a['status'] == 'Absent') $absent++;
    elseif ($a['status'] == 'Leave') $leave++;
    elseif ($a['status'] == 'Late') $late++;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Detailed Daily Attendance</h1>
        <p class="text-muted mb-0">Complete list of all students and their attendance status for today.</p>
    </div>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-outline-primary shadow-sm px-4">
            <i class="fas fa-print me-2"></i> Print Report
        </button>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 no-print">
    <div class="card-body p-4">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-bold">Select Date</label>
                <input type="date" name="date" class="form-control" value="<?=htmlspecialchars($date)?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Filter Class</label>
                <select name="class_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- All Classes --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?=$c['id']?>" <?=$c['id']==$class_id?'selected':''?>><?=htmlspecialchars($c['name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Filter Section</label>
                <select name="section_id" class="form-select" <?=$class_id?'':'disabled'?> onchange="this.form.submit()">
                    <option value="">-- All Sections --</option>
                    <?php foreach ($sections as $s): ?>
                        <option value="<?=$s['id']?>" <?=$s['id']==$section_id?'selected':''?>><?=htmlspecialchars($s['name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100 shadow-sm">Load Data</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col">
        <div class="stats-card mini-stat bg-success">
            <span class="value"><?=$present?></span>
            <span class="label">Present</span>
        </div>
    </div>
    <div class="col">
        <div class="stats-card mini-stat bg-danger">
            <span class="value"><?=$absent?></span>
            <span class="label">Absent</span>
        </div>
    </div>
    <div class="col">
        <div class="stats-card mini-stat bg-warning">
            <span class="value"><?=$leave?></span>
            <span class="label">Leave</span>
        </div>
    </div>
    <div class="col">
        <div class="stats-card mini-stat bg-primary">
            <span class="value"><?=$late?></span>
            <span class="label">Late</span>
        </div>
    </div>
    <div class="col">
        <div class="stats-card mini-stat bg-secondary">
            <span class="value"><?=$pending?></span>
            <span class="label">Pending</span>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Roll</th>
                        <th>Student Name</th>
                        <th>Admission #</th>
                        <th>Class & Section</th>
                        <th class="text-center">Status</th>
                        <th>Recorded At</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($attendance)): ?>
                    <tr><td colspan="6" class="text-center p-5 text-muted">No students found for the selected filters.</td></tr>
                <?php endif; ?>
                <?php foreach ($attendance as $a): ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?=$a['roll_no'] ?: '-'?></td>
                        <td>
                            <div class="fw-bold"><?=htmlspecialchars($a['full_name'])?></div>
                        </td>
                        <td class="text-muted small"><?=htmlspecialchars($a['admission_no'])?></td>
                        <td><?=htmlspecialchars($a['class_name'])?> - <?=htmlspecialchars($a['section_name'] ?: 'A')?></td>
                        <td class="text-center">
                            <?php if (!$a['status']): ?>
                                <span class="badge bg-light text-dark border"><i class="fas fa-clock me-1"></i>Pending</span>
                            <?php else: ?>
                                <span class="badge <?php 
                                    echo ($a['status']=='Present' ? 'bg-success' : 
                                         ($a['status']=='Absent' ? 'bg-danger' : 
                                         ($a['status']=='Late' ? 'bg-info' : 'bg-warning'))); 
                                ?> px-3">
                                    <?=htmlspecialchars($a['status'])?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted italic">
                            <?= $a['recorded_at'] ? date('H:i A', strtotime($a['recorded_at'])) : '-' ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.mini-stat { padding: 15px; text-align: center; color: white; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.mini-stat .value { display: block; font-size: 1.5rem; font-weight: 800; line-height: 1; }
.mini-stat .label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; }

@media print {
    .sidebar, .top-navbar, .no-print { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .table thead th { background-color: #f8f9fa !important; border-bottom: 2px solid #dee2e6 !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
$pdo = getPDO();
$context = $_SESSION['context'] ?? 'School';

$classes = $pdo->prepare('SELECT * FROM classes WHERE institution_type = :ctx');
$classes->execute([':ctx' => $context]);
$classes = $classes->fetchAll();

$subjects = $pdo->prepare('SELECT * FROM subjects WHERE institution_type IN (:ctx, "Both")');
$subjects->execute([':ctx' => $context]);
$subjects = $subjects->fetchAll();

$teachers = $pdo->prepare('SELECT id, full_name FROM teachers WHERE institution_type = :ctx');
$teachers->execute([':ctx' => $context]);
$teachers = $teachers->fetchAll();

$sections = $pdo->prepare('SELECT * FROM sections WHERE institution_type = :ctx');
$sections->execute([':ctx' => $context]);
$sections = $sections->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['class_id'])) {
    $stmt = $pdo->prepare('INSERT INTO timetable (class_id, section_id, day, period, subject_id, teacher_id, start_time, end_time) VALUES (:c, :s, :d, :p, :sub, :t, :st, :en)');
    $stmt->execute([':c'=>$_POST['class_id'], ':s'=>$_POST['section_id'], ':d'=>$_POST['day'], ':p'=>$_POST['period'], ':sub'=>$_POST['subject_id'], ':t'=>$_POST['teacher_id'], ':st'=>$_POST['start_time'], ':en'=>$_POST['end_time']]);
}

$tt = $pdo->prepare('SELECT tt.*, c.name AS class_name, sec.name AS section_name, s.name AS subject_name, t.full_name AS teacher_name 
                    FROM timetable tt 
                    LEFT JOIN classes c ON tt.class_id=c.id 
                    LEFT JOIN sections sec ON tt.section_id=sec.id 
                    LEFT JOIN subjects s ON tt.subject_id=s.id 
                    LEFT JOIN teachers t ON tt.teacher_id=t.id 
                    WHERE c.institution_type = :ctx
                    ORDER BY tt.class_id, tt.day, tt.period');
$tt->execute([':ctx' => $context]);
$tt = $tt->fetchAll();
?>
<h1><?= $context === 'Coaching' ? 'Batch Schedule' : 'School Timetable' ?></h1>
<p class="text-muted">Master schedule of classes, subjects, and faculty assignments.</p>

<div class="card p-4 mb-4 shadow-sm border-0">
    <h5 class="card-title mb-3"><i class="fas fa-plus-circle me-1"></i> Add Timetable Entry</h5>
    <form method="post">
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Class</label>
                <select class="form-select" name="class_id" required>
                    <?php foreach ($classes as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Section</label>
                <select class="form-select" name="section_id" required>
                    <?php foreach ($sections as $s): ?><option value="<?=$s['id']?>"><?=htmlspecialchars($s['name'])?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Day</label>
                <select class="form-select" name="day" required>
                    <option>Monday</option><option>Tuesday</option><option>Wednesday</option><option>Thursday</option><option>Friday</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Subject</label>
                <select class="form-select" name="subject_id" required>
                    <?php foreach ($subjects as $s): ?><option value="<?=$s['id']?>"><?=htmlspecialchars($s['name'])?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Teacher</label>
                <select class="form-select" name="teacher_id">
                    <option value="">-- No Teacher --</option>
                    <?php foreach ($teachers as $t): ?><option value="<?=$t['id']?>"><?=htmlspecialchars($t['full_name'])?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Period</label>
                <input class="form-control" name="period" placeholder="1" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Start Time</label>
                <input type="time" name="start_time" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">End Time</label>
                <input type="time" name="end_time" class="form-control" required>
            </div>
            <div class="col-md-2 align-self-end">
                <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Add Entry</button>
            </div>
        </div>
    </form>
</div>

<div class="card h-100">
    <div class="card-header bg-white"><i class="fas fa-calendar-alt me-2 text-primary"></i>Weekly Schedule Matrix</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Class / Section</th>
                    <th>Day</th>
                    <th>Period</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th class="text-end pe-4">Time Slot</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($tt as $r): ?>
                <tr>
                    <td class="ps-4 fw-bold">
                        <?=htmlspecialchars($r['class_name'])?> 
                        <span class="badge bg-light text-dark ms-2"><?=htmlspecialchars($r['section_name'] ?: 'N/A')?></span>
                    </td>
                    <td><?=htmlspecialchars($r['day'])?></td>
                    <td><span class="badge bg-primary rounded-pill"><?=htmlspecialchars($r['period'])?></span></td>
                    <td><span class="fw-bold text-dark"><?=htmlspecialchars($r['subject_name'])?></span></td>
                    <td><?=htmlspecialchars($r['teacher_name'] ?: 'Not Assigned')?></td>
                    <td class="text-end pe-4 text-muted">
                        <i class="far fa-clock me-1"></i> <?=date('H:i', strtotime($r['start_time']))?> - <?=date('H:i', strtotime($r['end_time']))?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

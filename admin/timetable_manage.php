<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO timetable (class_id, section_id, day, period, subject_id, teacher_id, start_time, end_time) VALUES (:c, :s, :d, :p, :su, :t, :st, :et)');
    $stmt->execute([':c'=>$_POST['class_id'], ':s'=>$_POST['section_id'], ':d'=>$_POST['day'], ':p'=>$_POST['period'], ':su'=>$_POST['subject_id'], ':t'=>$_POST['teacher_id'], ':st'=>$_POST['start_time'], ':et'=>$_POST['end_time']]);
    $msg = 'Timetable entry added';
}
$classes = $pdo->query('SELECT * FROM classes')->fetchAll();
$sections = $pdo->query('SELECT * FROM sections')->fetchAll();
$subjects = $pdo->query('SELECT * FROM subjects')->fetchAll();
$teachers = $pdo->query('SELECT id, full_name FROM teachers')->fetchAll();
$timetable = $pdo->query('SELECT t.*, c.name AS class_name, sec.name AS section_name, su.name AS subject_name, te.full_name AS teacher_name FROM timetable t LEFT JOIN classes c ON t.class_id=c.id LEFT JOIN sections sec ON t.section_id=sec.id LEFT JOIN subjects su ON t.subject_id=su.id LEFT JOIN teachers te ON t.teacher_id=te.id ORDER BY t.id LIMIT 100')->fetchAll();

?>
<h1>Timetable Administration</h1>
<p class="text-muted">Global scheduling management and faculty period assignments.</p>

<?php if ($msg): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<div class="card p-4 mb-4 shadow-sm border-0">
    <h5 class="card-title mb-3"><i class="fas fa-plus-circle me-1"></i> Add Timetable Entry</h5>
    <form method="post">
        <div class="row g-3">
            <div class="col-md-2">
                <label class="form-label fw-bold">Class</label>
                <select name="class_id" class="form-select" required>
                    <option value="">Class</option>
                    <?php foreach ($classes as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Section</label>
                <select name="section_id" class="form-select" required>
                    <option value="">Section</option>
                    <?php foreach ($sections as $s): ?><option value="<?=$s['id']?>"><?=htmlspecialchars($s['name'])?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Day</label>
                <select name="day" class="form-select" required>
                    <option>Mon</option><option>Tuesday</option><option>Wednesday</option><option>Thursday</option><option>Friday</option><option>Saturday</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label fw-bold">Period</label>
                <input type="number" name="period" class="form-control" placeholder="#" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Subject</label>
                <select name="subject_id" class="form-select" required>
                    <option value="">Subject</option>
                    <?php foreach ($subjects as $su): ?><option value="<?=$su['id']?>"><?=htmlspecialchars($su['name'])?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold">Teacher</label>
                <select name="teacher_id" class="form-select">
                    <option value="">Teacher</option>
                    <?php foreach ($teachers as $te): ?><option value="<?=$te['id']?>"><?=htmlspecialchars($te['full_name'])?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">Start</label>
                <input type="time" name="start_time" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-bold">End</label>
                <input type="time" name="end_time" class="form-control" required>
            </div>
            <div class="col-md-2 align-self-end">
                <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Save Entry</button>
            </div>
        </div>
    </form>
</div>

<div class="card h-100">
    <div class="card-header bg-white"><i class="fas fa-list me-2"></i>Global Schedule Matrix</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Class / Section</th>
                    <th>Day / Period</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th class="text-end pe-4">Time</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($timetable as $t): ?>
                <tr>
                    <td class="ps-4"><?=htmlspecialchars($t['id'])?></td>
                    <td>
                        <span class="fw-bold"><?=htmlspecialchars($t['class_name'])?></span>
                        <div class="small text-muted"><?=htmlspecialchars($t['section_name'])?></div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark"><?=htmlspecialchars($t['day'])?></span>
                        <span class="badge bg-primary rounded-pill ms-1"><?=htmlspecialchars($t['period'])?></span>
                    </td>
                    <td class="fw-bold"><?=htmlspecialchars($t['subject_name'])?></td>
                    <td><?=htmlspecialchars($t['teacher_name'] ?: 'N/A')?></td>
                    <td class="text-end pe-4 text-muted small">
                        <?=date('H:i', strtotime($t['start_time']))?> - <?=date('H:i', strtotime($t['end_time']))?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

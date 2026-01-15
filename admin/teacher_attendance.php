<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid = intval($_POST['teacher_id']);
    $date = $_POST['date'] ?: date('Y-m-d');
    $in = $_POST['in_time'] ?: null;
    $out = $_POST['out_time'] ?: null;
    $status = $_POST['status'] ?: 'Present';
    $stmt = $pdo->prepare('SELECT id FROM teacher_attendance WHERE teacher_id = :t AND attendance_date = :d');
    $stmt->execute([':t'=>$tid, ':d'=>$date]);
    if ($stmt->fetch()) {
        $pdo->prepare('UPDATE teacher_attendance SET in_time=:in_time, out_time=:out_time, status=:st WHERE teacher_id=:t AND attendance_date=:d')->execute([':in_time'=>$in, ':out_time'=>$out, ':st'=>$status, ':t'=>$tid, ':d'=>$date]);
    } else {
        $pdo->prepare('INSERT INTO teacher_attendance (teacher_id, attendance_date, in_time, out_time, status, recorded_at) VALUES (:t, :d, :in_time, :out_time, :st, NOW())')->execute([':t'=>$tid, ':d'=>$date, ':in_time'=>$in, ':out_time'=>$out, ':st'=>$status]);
    }
    $msg = 'Saved';
}
$teachers = $pdo->query('SELECT id, full_name FROM teachers')->fetchAll();

?>
<h1>Teacher Attendance</h1>
<div class="card p-4">
    <form method="post">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Select Teacher</label>
                <select name="teacher_id" class="form-select" required>
                    <option value="">-- Select --</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?=$t['id']?>"><?=htmlspecialchars($t['full_name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="<?=date('Y-m-d')?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">In Time</label>
                <input type="time" name="in_time" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Out Time</label>
                <input type="time" name="out_time" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option>Present</option>
                    <option>Absent</option>
                    <option>Leave</option>
                    <option>Late</option>
                </select>
            </div>
            <div class="col-12 mt-4 text-end">
                <button class="btn btn-primary px-5"><i class="fas fa-save me-1"></i> Save Attendance</button>
            </div>
        </div>
    </form>
</div>

<?php if (!empty($msg)): ?>
    <div class="alert alert-info mt-3"><i class="fas fa-check-circle me-1"></i> Attendance records updated.</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

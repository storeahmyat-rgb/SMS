<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$exam_id = intval($_GET['exam_id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM exams WHERE id = :id');
$stmt->execute([':id'=>$exam_id]);
$exam = $stmt->fetch();
if (!$exam) { echo 'Exam not found'; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = intval($_POST['student_id']);
    $subject_id = intval($_POST['subject_id']);
    $marks = floatval($_POST['marks']);
    $total = floatval($_POST['total'] ?? 100);
    $grade = '';
    $pct = $total ? ($marks / $total) * 100 : 0;
    if ($pct >= 90) $grade = 'A+';
    elseif ($pct >= 80) $grade = 'A';
    elseif ($pct >= 70) $grade = 'B';
    elseif ($pct >= 60) $grade = 'C';
    elseif ($pct >= 50) $grade = 'D';
    else $grade = 'F';
    $ins = $pdo->prepare('INSERT INTO results (exam_id, student_id, subject_id, marks_obtained, total_marks, grade, created_at) VALUES (:e, :s, :su, :m, :t, :g, NOW())');
    $ins->execute([':e'=>$exam_id, ':s'=>$student_id, ':su'=>$subject_id, ':m'=>$marks, ':t'=>$total, ':g'=>$grade]);
}

$students = $pdo->query('SELECT id, admission_no, full_name FROM students WHERE status="Active" LIMIT 200')->fetchAll();
$subjects = $pdo->query('SELECT * FROM subjects')->fetchAll();

?>
<h1>Enter Exam Marks</h1>
<p class="text-muted">Recording academic performance for: <strong><?=htmlspecialchars($exam['name'])?></strong></p>

<div class="card p-4 mb-4 shadow-sm border-0">
    <h5 class="card-title mb-3"><i class="fas fa-edit me-1"></i> Record Individual Marks</h5>
    <form method="post">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Student</label>
                <select name="student_id" class="form-select" required>
                    <option value="">-- Select Student --</option>
                    <?php foreach ($students as $s): ?>
                        <option value="<?=$s['id']?>"><?=htmlspecialchars($s['admission_no'].' - '.$s['full_name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select" required>
                    <option value="">-- Select Subject --</option>
                    <?php foreach ($subjects as $su): ?>
                        <option value="<?=$su['id']?>"><?=htmlspecialchars($su['name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Obtained</label>
                <input type="number" step="0.01" name="marks" class="form-control" placeholder="Marks" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Total</label>
                <input type="number" step="0.01" name="total" class="form-control" value="100" required>
            </div>
            <div class="col-md-2 align-self-end">
                <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Save</button>
            </div>
        </div>
    </form>
</div>

<div class="alert alert-info border-0 shadow-sm mb-4">
    <i class="fas fa-info-circle me-1"></i> Grades are auto-calculated based on percentage: A+ (90%+), A (80%+), B (70%+), C (60%+), D (50%+), F (Below 50%)
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

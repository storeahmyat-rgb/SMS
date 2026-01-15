<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['exam_name'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $stmt = $pdo->prepare('INSERT INTO exams (name, start_date, end_date, created_at) VALUES (:n, :s, :e, NOW())');
    $stmt->execute([':n'=>$name, ':s'=>$start, ':e'=>$end]);
    $msg = 'Exam created';
}
$exams = $pdo->query('SELECT * FROM exams ORDER BY id DESC LIMIT 50')->fetchAll();

?>
<div class="row align-items-center mb-4">
    <div class="col">
        <h1>Examination Management</h1>
        <p class="text-muted mb-0">Schedule and record student academic performance.</p>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#examModal">
            <i class="fas fa-plus me-1"></i> Create New Exam
        </button>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white"><i class="fas fa-file-invoice me-2"></i>Active & Past Examinations</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Exam Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th class="text-end pe-4">Operations</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($exams as $e): ?>
                <tr>
                    <td class="ps-4"><?=htmlspecialchars($e['id'])?></td>
                    <td class="fw-bold"><?=htmlspecialchars($e['name'])?></td>
                    <td><?=date('d M Y', strtotime($e['start_date']))?></td>
                    <td><?=date('d M Y', strtotime($e['end_date']))?></td>
                    <td class="text-end pe-4">
                        <a href="<?=BASE_URL?>admin/exam_marks.php?exam_id=<?=$e['id']?>" class="btn btn-sm btn-outline-info">
                            <i class="fas fa-pen-nib me-1"></i> Enter Marks
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="examModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header"><h5 class="modal-title">Create Exam</h5></div>
        <div class="modal-body">
          <input class="form-control mb-3" name="exam_name" placeholder="Exam name" required>
          <input type="date" class="form-control mb-3" name="start_date" required>
          <input type="date" class="form-control" name="end_date" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

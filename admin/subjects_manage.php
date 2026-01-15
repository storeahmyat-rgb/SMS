<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_name'])) {
    $stmt = $pdo->prepare('INSERT INTO subjects (name, code, created_at) VALUES (:n, :c, NOW())');
    $stmt->execute([':n'=>$_POST['subject_name'], ':c'=>$_POST['code']]);
    $msg = 'Subject created';
}
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY id')->fetchAll();

?>
<div class="row align-items-center mb-4">
    <div class="col">
        <h1>Course Subjects</h1>
        <p class="text-muted mb-0">Define and manage the academic curriculum for all classes.</p>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subjectModal">
            <i class="fas fa-plus-circle me-1"></i> Register New Subject
        </button>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<div class="card h-100">
    <div class="card-header bg-white"><i class="fas fa-book me-2"></i>Curriculum Repository</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Full Subject Name</th>
                    <th>Course Code</th>
                    <th class="text-end pe-4">System Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($subjects as $s): ?>
                <tr>
                    <td class="ps-4"><?=htmlspecialchars($s['id'])?></td>
                    <td class="fw-bold text-dark"><?=htmlspecialchars($s['name'])?></td>
                    <td><span class="badge bg-light text-dark font-monospace"><?=htmlspecialchars($s['code'] ?: 'N/A')?></span></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-warning">Modify</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="subjectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-lg">
      <form method="post">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title font-heading text-white">Configure New Subject</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Subject Full Name</label>
                <input class="form-control" name="subject_name" placeholder="e.g. Theoretical Physics" required>
            </div>
            <div class="mb-0">
                <label class="form-label fw-bold">Internal Course Code</label>
                <input class="form-control" name="code" placeholder="e.g. PHY-401">
            </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Save Subject</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

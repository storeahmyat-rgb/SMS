<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
$pdo = getPDO();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $stmt = $pdo->prepare('INSERT INTO subjects (name, code, created_at) VALUES (:n, :c, NOW())');
    $stmt->execute([':n'=>$_POST['name'], ':c'=>$_POST['code']]);
}
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY id DESC')->fetchAll();

$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<h1>Curriculum Subjects</h1>
<?php if ($msg): ?><div class="alert alert-success shadow-sm"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-circle me-1"></i> <?=htmlspecialchars($error)?></div><?php endif; ?>
<div class="row">
    <div class="col-md-4">
        <div class="card p-4 mb-4">
            <h5 class="card-title mb-3"><i class="fas fa-plus-circle me-1"></i> Add New Subject</h5>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Subject Name</label>
                    <input name="name" class="form-control" placeholder="e.g. Mathematics" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subject Code</label>
                    <input name="code" class="form-control" placeholder="e.g. MATH-101">
                </div>
                <button class="btn btn-primary w-100">Add Subject</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header bg-white"><i class="fas fa-book me-2"></i>Subject Directory</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Subject Name</th>
                            <th>Code</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($subjects)): ?>
                        <tr><td colspan="4" class="text-center p-4">No subjects defined yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($subjects as $s): ?>
                        <tr>
                            <td class="ps-4"><?=htmlspecialchars($s['id'])?></td>
                            <td class="fw-bold"><?=htmlspecialchars($s['name'])?></td>
                            <td><span class="badge bg-light text-dark"><?=htmlspecialchars($s['code'])?></span></td>
                            <td class="text-end pe-4">
                                <a href="<?=BASE_URL?>admin/subject_delete.php?id=<?=$s['id']?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Delete this subject? This action cannot be undone.')">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

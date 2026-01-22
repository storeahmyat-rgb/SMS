<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
$pdo = getPDO();
$classes = $pdo->query('SELECT * FROM classes')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $stmt = $pdo->prepare('INSERT INTO sections (class_id, name, created_at) VALUES (:c, :n, NOW())');
    $stmt->execute([':c'=>$_POST['class_id'], ':n'=>$_POST['name']]);
}
$sections = $pdo->query('SELECT sec.*, c.name AS class_name FROM sections sec LEFT JOIN classes c ON sec.class_id=c.id ORDER BY sec.id DESC')->fetchAll();

$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';
?>
<h1>Class Sections</h1>
<?php if ($msg): ?><div class="alert alert-success shadow-sm"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-circle me-1"></i> <?=htmlspecialchars($error)?></div><?php endif; ?>
<div class="row">
    <div class="col-md-4">
        <div class="card p-4 mb-4">
            <h5 class="card-title mb-3"><i class="fas fa-plus-circle me-1"></i> Add New Section</h5>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Associated Class</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Section Name</label>
                    <input name="name" class="form-control" placeholder="e.g. Section A" required>
                </div>
                <button class="btn btn-primary w-100">Add Section</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header bg-white"><i class="fas fa-list me-2"></i>Existing Sections</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Class Name</th>
                            <th>Section Name</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($sections)): ?>
                        <tr><td colspan="4" class="text-center p-4">No sections defined yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($sections as $s): ?>
                        <tr>
                            <td class="ps-4"><?=htmlspecialchars($s['id'])?></td>
                            <td class="fw-bold"><?=htmlspecialchars($s['class_name'])?></td>
                            <td><span class="badge bg-light text-dark"><?=htmlspecialchars($s['name'])?></span></td>
                            <td class="text-end pe-4">
                                <a href="<?=BASE_URL?>admin/section_delete.php?id=<?=$s['id']?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Delete this section? This action cannot be undone.')">
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

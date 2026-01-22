<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $stmt = $pdo->prepare('INSERT INTO classes (name, code, created_at) VALUES (:n, :c, NOW())');
    try {
        $stmt->execute([':n' => $_POST['name'], ':c' => $_POST['code']]);
        $msg = 'Class "' . htmlspecialchars($_POST['name']) . '" added successfully!';
    } catch (PDOException $e) {
        $error = 'Error adding class: ' . $e->getMessage();
    }
}

$classes = $pdo->query('SELECT * FROM classes ORDER BY id DESC')->fetchAll();
?>

<div class="row align-items-center mb-4">
    <div class="col">
        <h1 class="h3 mb-0">Class Management</h1>
        <p class="text-muted mb-0">Define and manage academic levels for your institution.</p>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addClassModal">
            <i class="fas fa-plus-circle me-1"></i> Add New Class
        </button>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="fas fa-exclamation-circle me-1"></i> <?=htmlspecialchars($error)?></div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3"><i class="fas fa-list me-2 text-primary"></i>Active Academic Classes</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Class Name</th>
                        <th>Class Code</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($classes)): ?>
                    <tr><td colspan="4" class="text-center p-5 text-muted">No classes defined yet. Start by adding one!</td></tr>
                <?php endif; ?>
                <?php foreach ($classes as $c): ?>
                    <tr>
                        <td class="ps-4 text-muted small"><?=$c['id']?></td>
                        <td class="fw-bold"><?=htmlspecialchars($c['name'])?></td>
                        <td><span class="badge bg-light text-primary border"><?=htmlspecialchars($c['code'] ?: 'N/A')?></span></td>
                        <td class="text-end pe-4">
                            <a href="<?=BASE_URL?>admin/class_delete.php?id=<?=$c['id']?>" 
                               class="btn btn-sm btn-outline-danger" 
                               onclick="return confirm('Attention: Deleting this class will also affect students and sections assigned to it. Proceed?')">
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

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <form method="post">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">Register New Class</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Class Name</label>
                        <input name="name" class="form-control" placeholder="e.g. Nursery, Grade 1, Matric" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Class Code (Short Name)</label>
                        <input name="code" class="form-control" placeholder="e.g. NUR, G-01, MAT">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Create Class Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

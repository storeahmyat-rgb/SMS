<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';
$pdo = getPDO();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['name'])) {
    $stmt = $pdo->prepare('INSERT INTO classes (name, code, created_at) VALUES (:n, :c, NOW())');
    $stmt->execute([':n'=>$_POST['name'], ':c'=>$_POST['code']]);
}
$classes = $pdo->query('SELECT * FROM classes ORDER BY id DESC')->fetchAll();
?>
<h1>Academic Classes</h1>
<div class="row">
    <div class="col-md-4">
        <div class="card p-4 mb-4">
            <h5 class="card-title mb-3"><i class="fas fa-plus-circle me-1"></i> Add New Class</h5>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Class Name</label>
                    <input name="name" class="form-control" placeholder="e.g. Grade 10" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Class Code</label>
                    <input name="code" class="form-control" placeholder="e.g. G10">
                </div>
                <button class="btn btn-primary w-100">Add Class</button>
            </form>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header bg-white"><i class="fas fa-list me-2"></i>Existing Classes</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Class Name</th>
                            <th>Code</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($classes)): ?>
                        <tr><td colspan="4" class="text-center p-4">No classes defined yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($classes as $c): ?>
                        <tr>
                            <td class="ps-4"><?=htmlspecialchars($c['id'])?></td>
                            <td class="fw-bold"><?=htmlspecialchars($c['name'])?></td>
                            <td><span class="badge bg-light text-dark"><?=htmlspecialchars($c['code'])?></span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
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

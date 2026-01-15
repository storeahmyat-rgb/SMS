<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section_name'])) {
    $stmt = $pdo->prepare('INSERT INTO sections (class_id, name, created_at) VALUES (:c, :n, NOW())');
    $stmt->execute([':c'=>$_POST['class_id'], ':n'=>$_POST['section_name']]);
    $msg = 'Section created';
}
$classes = $pdo->query('SELECT * FROM classes')->fetchAll();
$sections = $pdo->query('SELECT s.*, c.name AS class_name FROM sections s LEFT JOIN classes c ON s.class_id=c.id ORDER BY s.id')->fetchAll();

?>
<div class="row align-items-center mb-4">
    <div class="col">
        <h1>Class Divisions</h1>
        <p class="text-muted mb-0">Organize students into specific sections per academic level.</p>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sectionModal">
            <i class="fas fa-plus-circle me-1"></i> Add New Section
        </button>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<div class="card h-100">
    <div class="card-header bg-white"><i class="fas fa-columns me-2"></i>Active Section Mapping</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Academic Class</th>
                    <th>Section Label</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sections as $s): ?>
                <tr>
                    <td class="ps-4"><?=htmlspecialchars($s['id'])?></td>
                    <td><span class="badge bg-light text-dark"><?=htmlspecialchars($s['class_name'])?></span></td>
                    <td class="fw-bold text-primary"><?=htmlspecialchars($s['name'])?></td>
                    <td class="text-end pe-4">
                        <button class="btn btn-sm btn-outline-warning">Edit</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="sectionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-lg">
      <form method="post">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title font-heading text-white">Assign New Section</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Select Class</label>
                <select class="form-select" name="class_id" required>
                    <option value="">-- Choose Class --</option>
                    <?php foreach ($classes as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="mb-0">
                <label class="form-label fw-bold">Section Name</label>
                <input class="form-control" name="section_name" placeholder="e.g. Blue, A, Beta" required>
            </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Register Section</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

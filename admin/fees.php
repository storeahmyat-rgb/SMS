<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$classes = $pdo->query('SELECT * FROM classes')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fee_name'])) {
    $stmt = $pdo->prepare('INSERT INTO fees (name, description, amount, fee_type, class_id, created_at) VALUES (:n, :d, :a, :t, :c, NOW())');
    $stmt->execute([
        ':n'=>$_POST['fee_name'], 
        ':d'=>$_POST['description'], 
        ':a'=>$_POST['amount'], 
        ':t'=>$_POST['fee_type'],
        ':c'=>($_POST['class_id'] ?: null)
    ]);
    $msg = 'Fee category created successfully';
}
$fees = $pdo->query('SELECT f.*, c.name AS class_name FROM fees f LEFT JOIN classes c ON f.class_id = c.id ORDER BY f.id DESC')->fetchAll();

?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h1>Fee Structure</h1>
    <p class="text-muted mb-0">Define academic fees and associate them with specific classes.</p>
  </div>
  <a class="btn btn-primary" href="<?=BASE_URL?>admin/fee_pay.php">
    <i class="fas fa-hand-holding-usd me-1"></i> Collect Fee
  </a>
</div>

<div class="alert alert-info border-0 shadow-sm mb-4">
    <div class="d-flex">
        <i class="fas fa-lightbulb mt-1 me-3 fa-2x opacity-50"></i>
        <div>
            <h6 class="fw-bold mb-1">Quick Tip: Smart Billing</h6>
            <p class="mb-0 small">Fees defined here with a <strong>'Target Class'</strong> will automatically appear as recommended options when you collect fees for students in that class. Use 'Global' for fees like Transport or Exams that apply to everyone.</p>
        </div>
    </div>
</div>

<?php if (!empty($msg)): ?><div class="alert alert-success shadow-sm"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div><?php endif; ?>

<div class="card shadow-sm border-0 mb-5">
    <div class="card-header bg-white"><i class="fas fa-list me-2 text-primary"></i>Active Fee Categories</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Fee Name</th>
                    <th>Target Class</th>
                    <th>Fee Type</th>
                    <th class="text-end pe-4">Amount (Rs.)</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($fees as $f): ?>
                <tr>
                    <td class="ps-4 text-muted">#<?=htmlspecialchars($f['id'])?></td>
                    <td class="fw-bold"><?=htmlspecialchars($f['name'])?></td>
                    <td>
                        <span class="badge <?= $f['class_name'] ? 'bg-info text-white' : 'bg-light text-dark' ?>">
                            <?=htmlspecialchars($f['class_name'] ?: 'All Classes')?>
                        </span>
                    </td>
                    <td><span class="badge bg-light text-secondary"><?=htmlspecialchars($f['fee_type'])?></span></td>
                    <td class="text-end pe-4 fw-bold">Rs. <?=number_format($f['amount'], 2)?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white"><i class="fas fa-plus-circle me-2 text-success"></i>Configure New Fee Category</div>
    <div class="card-body p-4">
        <form method="post">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Fee Title</label>
                    <input class="form-control" name="fee_name" placeholder="e.g. Monthly Tuition" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Amount (PKR)</label>
                    <input class="form-control" name="amount" type="number" step="0.01" placeholder="0.00" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Fee Type</label>
                    <select name="fee_type" class="form-select">
                        <option>Monthly</option>
                        <option>Admission</option>
                        <option>Exam</option>
                        <option>Practical</option>
                        <option>Transport</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Associate with Class</label>
                    <select name="class_id" class="form-select">
                        <option value="">Global (All Classes)</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?=htmlspecialchars($c['id'])?>"><?=htmlspecialchars($c['name'])?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Select 'Global' for fees applicable to all.</small>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Short Description</label>
                    <textarea class="form-control" name="description" rows="2" placeholder="Briefly explain what this fee covers..."></textarea>
                </div>
                <div class="col-12 text-end">
                    <button class="btn btn-success px-5">Save Fee Configuration</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

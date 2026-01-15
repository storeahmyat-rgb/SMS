<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat = $_POST['category'];
    $amt = floatval($_POST['amount']);
    $date = $_POST['date'] ?: date('Y-m-d');
    $desc = $_POST['description'];
    $stmt = $pdo->prepare('INSERT INTO expenses (category, amount, expense_date, description, created_at) VALUES (:c, :a, :d, :de, NOW())');
    $stmt->execute([':c'=>$cat, ':a'=>$amt, ':d'=>$date, ':de'=>$desc]);
    $msg = 'Expense recorded';
}
$expenses = $pdo->query('SELECT * FROM expenses ORDER BY expense_date DESC LIMIT 100')->fetchAll();

?>
<h1>School Expenses</h1>
<div class="card p-4 mb-4">
    <h5 class="card-title mb-3"><i class="fas fa-plus-circle me-1"></i> Add New Expense</h5>
    <form method="post">
        <div class="row g-3">
            <div class="col-md-3">
                <input class="form-control" name="category" placeholder="Expense Category (e.g. Utility)" required>
            </div>
            <div class="col-md-2">
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" name="amount" placeholder="Amount" required>
                </div>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date" value="<?=date('Y-m-d')?>">
            </div>
            <div class="col-md-3">
                <input class="form-control" name="description" placeholder="Short description...">
            </div>
            <div class="col-md-2 text-end">
                <button class="btn btn-primary w-100"><i class="fas fa-check me-1"></i> Record</button>
            </div>
        </div>
    </form>
</div>

<?php if ($msg): ?><div class="alert alert-info"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div><?php endif; ?>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i>Recent Operational Expenses</span>
        <span class="badge bg-primary">Last 100 entries</span>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($expenses as $e): ?>
                <tr>
                    <td><?=htmlspecialchars($e['id'])?></td>
                    <td><span class="badge bg-light text-dark"><?=htmlspecialchars($e['category'])?></span></td>
                    <td class="fw-bold text-danger"><?=number_format($e['amount'], 2)?></td>
                    <td><?=date('d M Y', strtotime($e['expense_date']))?></td>
                    <td class="text-muted small"><?=htmlspecialchars($e['description'])?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

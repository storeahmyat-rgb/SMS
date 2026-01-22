<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$id = intval($_GET['id'] ?? 0);
$msg = '';
$stmt = $pdo->prepare('SELECT * FROM teachers WHERE id = :id');
$stmt->execute([':id'=>$id]);
$t = $stmt->fetch();

if (!$t) { echo 'Not found'; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upd = $pdo->prepare('UPDATE teachers SET full_name=:n, cnic=:c, qualification=:q, contact=:co, salary=:s, joining_date=:j, designation=:d, institution_type=:it, status=:st WHERE id=:id');
    $upd->execute([
        ':n'=>$_POST['full_name'], 
        ':c'=>$_POST['cnic'], 
        ':q'=>$_POST['qualification'], 
        ':co'=>$_POST['contact'], 
        ':s'=>$_POST['salary'], 
        ':j'=>$_POST['joining_date'], 
        ':d'=>$_POST['designation'] ?? 'Faculty Member',
        ':it'=>$_POST['institution_type'] ?? 'School',
        ':st'=>$_POST['status'], 
        ':id'=>$id
    ]);
    $msg = 'Updated';
    $stmt->execute([':id'=>$id]);
    $t = $stmt->fetch();
}

?>
<h1>Edit Faculty Record</h1>
<p class="text-muted">Updating profile information for: <strong><?=htmlspecialchars($t['full_name'])?></strong></p>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white"><i class="fas fa-user-edit me-2"></i>Teacher Profile Editor</div>
    <div class="card-body p-4">
        <form method="post">
            <div class="row g-4">
                <!-- Basic Info -->
                <div class="col-12"><h5 class="border-bottom pb-2">Employment Information</h5></div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Teacher ID</label>
                    <input class="form-control bg-light" value="<?=htmlspecialchars($t['teacher_id'])?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Base Salary</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" step="0.01" class="form-control" name="salary" value="<?=htmlspecialchars($t['salary'])?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Joining Date</label>
                    <input type="date" class="form-control" name="joining_date" value="<?=htmlspecialchars($t['joining_date'])?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Staff Status</label>
                    <select name="status" class="form-select">
                        <option value="Active" <?=$t['status']=='Active'?'selected':''?>>Active</option>
                        <option value="Left" <?=$t['status']=='Left'?'selected':''?>>Left</option>
                    </select>
                </div>

                <!-- Personal Info -->
                <div class="col-12 mt-5"><h5 class="border-bottom pb-2">Personal & Professional Details</h5></div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Full Name</label>
                    <input class="form-control" name="full_name" value="<?=htmlspecialchars($t['full_name'])?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Qualification</label>
                    <input class="form-control" name="qualification" value="<?=htmlspecialchars($t['qualification'])?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">CNIC / ID Card No</label>
                    <input class="form-control" name="cnic" value="<?=htmlspecialchars($t['cnic'])?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Contact Number</label>
                    <input class="form-control" name="contact" value="<?=htmlspecialchars($t['contact'])?>">
                </div>

                <div class="col-12 mt-4 pt-4 border-top text-end">
                    <a href="<?=BASE_URL?>admin/teachers.php" class="btn btn-light px-4 me-2">Back to List</a>
                    <button class="btn btn-primary px-5"><i class="fas fa-save me-1"></i> Update Faculty Record</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-info mt-3 shadow-sm"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

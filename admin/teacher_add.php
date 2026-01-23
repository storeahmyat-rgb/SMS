<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();

// Auto-generate Teacher ID
$stmt = $pdo->query("SELECT teacher_id FROM teachers WHERE teacher_id LIKE 'TCH-%' ORDER BY id DESC LIMIT 1");
$lastVal = $stmt->fetchColumn();
if ($lastVal) {
    $seq = intval(substr($lastVal, 4)) + 1;
} else {
    $seq = 1;
}
$next_teacher_id = sprintf("TCH-%03d", $seq);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        ':teacher_id'=>$_POST['teacher_id'] ?: $next_teacher_id,
        ':full_name'=>$_POST['full_name'],
        ':cnic'=>$_POST['cnic'],
        ':qualification'=>$_POST['qualification'],
        ':contact'=>$_POST['contact'],
        ':salary'=>$_POST['salary'] ?: 0,
        ':joining_date'=>$_POST['joining_date'] ?: date('Y-m-d'),
        ':designation'=>$_POST['designation'] ?: 'Faculty Member',
        ':institution_type'=>$_POST['institution_type'] ?: 'School',
        ':status'=>$_POST['status']
    ];
    $stmt = $pdo->prepare('INSERT INTO teachers (teacher_id, full_name, cnic, qualification, contact, salary, joining_date, designation, institution_type, status, created_at) VALUES (:teacher_id, :full_name, :cnic, :qualification, :contact, :salary, :joining_date, :designation, :institution_type, :status, NOW())');
    try {
        $stmt->execute($data);
        $msg = 'Teacher added successfully. ID: ' . $data[':teacher_id'];
        $next_teacher_id = sprintf("TCH-%03d", $seq + 1);
    } catch (PDOException $e) {
        $msg = 'Error: ' . $e->getMessage();
        if (strpos($e->getMessage(), 'Duplicate') !== false) {
             $msg = 'Error: Duplicate ID. Please refresh to fetch new ID.';
        }
    }
}

?>
<h1>Register New Faculty Member</h1>
<p class="text-muted">Onboard a new teacher into the faculty directory.</p>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white"><i class="fas fa-chalkboard-teacher me-2"></i>Teacher Registration Form</div>
    <div class="card-body p-4">
        <form method="post">
            <div class="row g-4">
                <!-- Basic Info -->
                <div class="col-12"><h5 class="border-bottom pb-2">Employment Information</h5></div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Teacher ID <small class="text-muted">(System Generated)</small></label>
                    <input class="form-control bg-light" name="teacher_id" value="<?=htmlspecialchars($next_teacher_id)?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Base Salary</label>
                    <div class="input-group">
                        <span class="input-group-text">Rs.</span>
                        <input type="number" step="0.01" class="form-control" name="salary" placeholder="0.00" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Joining Date</label>
                    <input type="date" class="form-control" name="joining_date" value="<?=date('Y-m-d')?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Staff Status</label>
                    <select name="status" class="form-select">
                        <option>Active</option>
                        <option>On Hold</option>
                        <option>Resigned</option>
                    </select>
                </div>

                <!-- Personal Info -->
                <div class="col-12 mt-5"><h5 class="border-bottom pb-2">Personal & Professional Details</h5></div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Full Name</label>
                    <input class="form-control" name="full_name" required placeholder="Enter full name">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Qualification</label>
                    <input class="form-control" name="qualification" required placeholder="e.g. M.Sc Mathematics, B.Ed">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">CNIC / ID Card No</label>
                    <input class="form-control" name="cnic" placeholder="00000-0000000-0">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Contact Number</label>
                    <input class="form-control" name="contact" placeholder="03XXXXXXXXX">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Designation</label>
                    <input class="form-control" name="designation" value="Faculty Member" placeholder="e.g. Senior Teacher">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Institution Type</label>
                    <select name="institution_type" class="form-select">
                        <option value="School">School</option>
                        <option value="Coaching">Coaching</option>
                    </select>
                </div>

                <div class="col-12 mt-4 pt-4 border-top text-end">
                    <a href="<?=BASE_URL?>admin/teachers.php" class="btn btn-light px-4 me-2">Cancel</a>
                    <button class="btn btn-primary px-5"><i class="fas fa-save me-1"></i> Register Faculty</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-info mt-3 shadow-sm"><i class="fas fa-info-circle me-1"></i> <?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$classes = $pdo->query('SELECT * FROM classes')->fetchAll();
$sections = $pdo->query('SELECT s.*, c.name AS class_name FROM sections s LEFT JOIN classes c ON s.class_id=c.id')->fetchAll();

// Auto-generate Admission No
$year = date('Y');
$stmt = $pdo->prepare("SELECT admission_no FROM students WHERE admission_no LIKE :p ORDER BY id DESC LIMIT 1");
$stmt->execute([':p' => "STD-$year-%"]);
$lastVal = $stmt->fetchColumn();
if ($lastVal) {
    $parts = explode('-', $lastVal);
    $seq = intval(end($parts)) + 1;
} else {
    $seq = 1;
}
$next_admission_no = sprintf("STD-%s-%03d", $year, $seq);

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photo_name = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_name = 'STD_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/../assets/uploads/students/' . $photo_name);
    }

    $data = [
        ':admission_no'=>$_POST['admission_no'] ?: $next_admission_no,
        ':photo'=>$photo_name,
        ':full_name'=>$_POST['full_name'],
        ':father_name'=>$_POST['father_name'],
        ':guardian_cnic'=>$_POST['guardian_cnic'] ?? null,
        ':b_form'=>$_POST['b_form'],
        ':class_id'=>$_POST['class_id'] ?: null,
        ':section_id'=>$_POST['section_id'] ?: null,
        ':roll_no'=>$_POST['roll_no'] ?? null,
        ':gender'=>$_POST['gender'],
        ':blood_group'=>$_POST['blood_group'] ?? null,
        ':religion'=>$_POST['religion'] ?? 'Islam',
        ':dob'=>$_POST['dob'] ?: null,
        ':contact'=>$_POST['contact'],
        ':address'=>$_POST['address'],
        ':admission_date'=>$_POST['admission_date'] ?: date('Y-m-d'),
        ':institution_type'=>$_POST['institution_type'] ?: $_SESSION['context'],
        ':status'=>$_POST['status']
    ];

    $stmt = $pdo->prepare('INSERT INTO students (admission_no, photo, full_name, father_name, guardian_cnic, b_form, class_id, section_id, roll_no, gender, blood_group, religion, dob, contact, address, admission_date, institution_type, status, created_at) VALUES (:admission_no, :photo, :full_name, :father_name, :guardian_cnic, :b_form, :class_id, :section_id, :roll_no, :gender, :blood_group, :religion, :dob, :contact, :address, :admission_date, :institution_type, :status, NOW())');
    try {
        $stmt->execute($data);
        $msg = 'Student added successfully! ID: ' . $data[':admission_no'];
        // Increment for next view
        $next_admission_no = sprintf("STD-%s-%03d", $year, $seq + 1);
    } catch (PDOException $e) {
        $msg = 'Error: ' . $e->getMessage();
    }
}

?>
<h1>Register New Student</h1>
<p class="text-muted">Fill in the details below to enroll a new student into the system.</p>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white"><i class="fas fa-id-card me-2"></i>Admission Form</div>
    <div class="card-body p-4">
        <form method="post" enctype="multipart/form-data">
            <div class="row g-4">
                <!-- Academic Info -->
                <div class="col-12"><h5 class="border-bottom pb-2">Academic Information</h5></div>
                <div class="col-md-4">
                    <?php $cur_context = $_SESSION['context'] ?? 'School'; ?>
                    <label class="form-label fw-bold text-primary">Institutional Category</label>
                    <select name="institution_type" class="form-select border-primary">
                        <option value="School" <?= $cur_context === 'School'?'selected':'' ?>>School Admission</option>
                        <option value="Coaching" <?= $cur_context === 'Coaching'?'selected':'' ?>>Coaching Enrollment</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Admission Number <small class="text-muted">(System Generated)</small></label>
                    <input class="form-control bg-light" name="admission_no" value="<?=htmlspecialchars($next_admission_no)?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Class</label>
                    <select name="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?=htmlspecialchars($c['id'])?>"><?=htmlspecialchars($c['name'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Select Section</label>
                    <select name="section_id" id="section_id" class="form-select">
                        <option value="">-- Choose Section --</option>
                        <?php foreach ($sections as $s): ?>
                            <option value="<?=htmlspecialchars($s['id'])?>" data-class="<?=htmlspecialchars($s['class_id'])?>">
                                <?=htmlspecialchars($s['name'])?> (<?=htmlspecialchars($s['class_name'] ?? '')?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Admission Date</label>
                    <input type="date" class="form-control" name="admission_date" value="<?=date('Y-m-d')?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Student Photo</label>
                    <input type="file" class="form-control" name="photo" accept="image/*">
                    <small class="text-muted">Recommended: Passport size square image.</small>
                </div>

                <!-- Fee Structure Preview -->
                <div class="col-md-12 mt-4">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h6 class="fw-bold text-muted mb-3 italic"><i class="fas fa-money-check-alt me-2"></i>Fee Structure for Selected Class</h6>
                            <div id="fee_structure_view" class="row g-2">
                                <div class="col-12 text-muted small">Please select a class to view assigned fees.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal Info -->
                <div class="col-12 mt-5"><h5 class="border-bottom pb-2">Student & Guardian Details (Local Info)</h5></div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Full Name of Student</label>
                    <input class="form-control" name="full_name" required placeholder="Full Name as per B-Form">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Father's Name</label>
                    <input class="form-control" name="father_name" required placeholder="Full Name of Father">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Father/Guardian CNIC</label>
                    <input class="form-control" name="guardian_cnic" placeholder="42XXX-XXXXXXX-X">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Student B-Form No</label>
                    <input class="form-control" name="b_form" placeholder="42XXX-XXXXXXX-X">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Emergency Contact</label>
                    <input class="form-control" name="contact" placeholder="03XXXXXXXXX">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Blood Group</label>
                    <select name="blood_group" class="form-select">
                        <option value="">Unknown</option>
                        <option value="A+">A+</option><option value="A-">A-</option>
                        <option value="B+">B+</option><option value="B-">B-</option>
                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                        <option value="O+">O+</option><option value="O-">O-</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Religion</label>
                    <input class="form-control" name="religion" value="Islam">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Roll Number</label>
                    <input class="form-control" name="roll_no">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Date of Birth</label>
                    <input type="date" class="form-control" name="dob">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Address</label>
                    <input class="form-control" name="address" placeholder="Residential Address">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Current Status</label>
                    <select name="status" class="form-select">
                        <option value="Active">Active Student</option>
                        <option value="Left">Left School</option>
                    </select>
                </div>

                <div class="col-12 mt-4 pt-4 border-top text-end">
                    <a href="<?=BASE_URL?>admin/students.php" class="btn btn-light px-4 me-2">Back to List</a>
                    <button class="btn btn-primary px-5"><i class="fas fa-save me-1"></i> Register Student Now</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Simple filter for sections based on selected class
document.querySelector('select[name="class_id"]').addEventListener('change', function() {
    const classId = this.value;
    const sectionSelect = document.getElementById('section_id');
    const options = sectionSelect.querySelectorAll('option');
    
    options.forEach(opt => {
        if (!classId || opt.value === "" || opt.getAttribute('data-class') === classId) {
            opt.style.display = "";
        } else {
            opt.style.display = "none";
        }
    });
    sectionSelect.value = "";

    // Fetch Fee Structure
    const feeView = document.getElementById('fee_structure_view');
    if (!classId) {
        feeView.innerHTML = '<div class="col-12 text-muted small">Please select a class to view assigned fees.</div>';
        return;
    }

    feeView.innerHTML = '<div class="col-12 small">Loading fees...</div>';
    fetch(`fees_ajax.php?class_id=${classId}`)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                feeView.innerHTML = '<div class="col-12 text-danger small">No fees assigned to this class yet.</div>';
            } else {
                feeView.innerHTML = data.map(f => `
                    <div class="col-md-3">
                        <div class="p-2 border rounded bg-white shadow-sm">
                            <div class="small fw-bold">${f.name}</div>
                            <div class="text-primary fw-bold">Rs. ${parseFloat(f.amount).toLocaleString()}</div>
                            <div class="text-muted" style="font-size: 0.6rem;">${f.fee_type}</div>
                        </div>
                    </div>
                `).join('');
            }
        });
});
</script>

<?php if ($msg): ?>
    <div class="alert alert-success mt-4 p-4 shadow-sm border-0 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="mb-1"><i class="fas fa-check-circle me-2"></i>Registration Successful!</h5>
            <p class="mb-0 text-dark opacity-75"><?=htmlspecialchars($msg)?></p>
        </div>
        <div>
            <?php $last_id = $pdo->lastInsertId(); ?>
            <a href="<?=BASE_URL?>admin/admission_slip.php?id=<?=$last_id?>" target="_blank" class="btn btn-outline-dark px-4 me-2">
                <i class="fas fa-print me-1"></i> Print Admission Slip (2 Copies)
            </a>
            <a href="<?=BASE_URL?>admin/fee_pay.php?student_id=<?=$last_id?>&fee_type=Admission" class="btn btn-primary px-4">
                <i class="fas fa-hand-holding-usd me-1"></i> Collect Admission Fee Now
            </a>
            <a href="<?=BASE_URL?>admin/students.php" class="btn btn-outline-secondary ms-2">View List</a>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

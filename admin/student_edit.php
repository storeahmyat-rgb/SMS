<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin', 'teacher', 'accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
$stmt->execute([':id' => $id]);
$s = $stmt->fetch();

if (!$s) { echo "Student not found."; exit; }

$classes = $pdo->query('SELECT * FROM classes')->fetchAll();
$sections = $pdo->query('SELECT s.*, c.name AS class_name FROM sections s LEFT JOIN classes c ON s.class_id=c.id')->fetchAll();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photo_name = $s['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $photo_name = 'STD_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__ . '/../assets/uploads/students/' . $photo_name);
    }

    $data = [
        ':id' => $id,
        ':photo' => $photo_name,
        ':full_name' => $_POST['full_name'],
        ':father_name' => $_POST['father_name'],
        ':guardian_cnic' => $_POST['guardian_cnic'] ?? null,
        ':b_form' => $_POST['b_form'],
        ':class_id' => $_POST['class_id'] ?: null,
        ':section_id' => $_POST['section_id'] ?: null,
        ':roll_no' => $_POST['roll_no'],
        ':gender' => $_POST['gender'],
        ':blood_group' => $_POST['blood_group'] ?? null,
        ':religion' => $_POST['religion'] ?? 'Islam',
        ':dob' => $_POST['dob'] ?: null,
        ':contact' => $_POST['contact'],
        ':address' => $_POST['address'],
        ':institution_type' => $_POST['institution_type'],
        ':status' => $_POST['status']
    ];

    $sql = "UPDATE students SET 
            photo = :photo, 
            full_name = :full_name, 
            father_name = :father_name, 
            guardian_cnic = :guardian_cnic,
            b_form = :b_form, 
            class_id = :class_id, 
            section_id = :section_id, 
            roll_no = :roll_no, 
            gender = :gender, 
            blood_group = :blood_group,
            religion = :religion,
            dob = :dob, 
            contact = :contact, 
            address = :address, 
            institution_type = :institution_type, 
            status = :status 
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute($data);
        header("Location: students.php?msg=Updated");
        exit;
    } catch (PDOException $e) {
        $msg = 'Error: ' . $e->getMessage();
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Edit Student Profile</h1>
    <a href="students.php" class="btn btn-outline-secondary">Back to List</a>
</div>

<?php if ($msg): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form method="post" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-12"><h5 class="border-bottom pb-2">Academic Information</h5></div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Institutional Category</label>
                    <select name="institution_type" class="form-select border-primary">
                        <option value="School" <?= $s['institution_type'] === 'School' ? 'selected' : '' ?>>School</option>
                        <option value="Coaching" <?= $s['institution_type'] === 'Coaching' ? 'selected' : '' ?>>Coaching</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Admission Number</label>
                    <input class="form-control bg-light" value="<?= htmlspecialchars($s['admission_no']) ?>" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Class</label>
                    <select name="class_id" class="form-select" required>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $s['class_id'] == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12 mt-5"><h5 class="border-bottom pb-2">Personal Details</h5></div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Full Name</label>
                    <input class="form-control" name="full_name" value="<?= htmlspecialchars($s['full_name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Father's Name</label>
                    <input class="form-control" name="father_name" value="<?= htmlspecialchars($s['father_name']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Guardian CNIC</label>
                    <input class="form-control" name="guardian_cnic" value="<?= htmlspecialchars($s['guardian_cnic'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Religion</label>
                    <input class="form-control" name="religion" value="<?= htmlspecialchars($s['religion'] ?? 'Islam') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Blood Group</label>
                    <select name="blood_group" class="form-select">
                        <option value="">Unknown</option>
                        <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
                            <option value="<?= $bg ?>" <?= ($s['blood_group']??'') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Section / Batch</label>
                    <select name="section_id" class="form-select">
                        <option value="">-- None --</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= $sec['id'] ?>" <?= $s['section_id'] == $sec['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sec['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Roll No</label>
                    <input class="form-control" name="roll_no" value="<?= htmlspecialchars($s['roll_no']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="Male" <?= $s['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                        <option value="Female" <?= $s['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Status</label>
                    <select name="status" class="form-select">
                        <option value="Active" <?= $s['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                        <option value="Left" <?= $s['status'] === 'Left' ? 'selected' : '' ?>>Left</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold">Photo Update</label>
                    <input type="file" class="form-control" name="photo">
                </div>

                <div class="col-12 mt-4 text-end">
                    <button class="btn btn-primary px-5 btn-lg">Update Profile</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

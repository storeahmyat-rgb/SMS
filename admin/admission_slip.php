<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin', 'accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT s.*, c.name AS class_name, sec.name AS section_name 
                       FROM students s 
                       LEFT JOIN classes c ON s.class_id = c.id 
                       LEFT JOIN sections sec ON s.section_id = sec.id 
                       WHERE s.id = :id');
$stmt->execute([':id' => $id]);
$s = $stmt->fetch();

if (!$s) { echo "Student not found."; exit; }

// Fetch applicable fees
$class_id = $s['class_id'] ?? 0;
$fstmt = $pdo->prepare('SELECT * FROM fees WHERE class_id = :cid OR class_id IS NULL');
$fstmt->execute([':cid' => $class_id]);
$s['fees'] = $fstmt->fetchAll();

$context = $s['institution_type'] ?: 'School';

function renderCopy($type, $s, $context) {
    ob_start();
    ?>
    <div class="admission-copy bg-white p-4 mb-4" style="border: 2px dashed #ccc; position: relative;">
        <div class="copy-badge py-1 px-3 bg-dark text-white position-absolute top-0 end-0 small opacity-75">
            <?= strtoupper($type) ?> COPY
        </div>
        
        <div class="row align-items-center mb-4">
            <div class="col-8">
                <h3 class="fw-bold mb-0 text-primary text-uppercase">Modern <?= $context ?> System</h3>
                <p class="small text-muted mb-0">Phase 2, Education Colony, Karachi | Tel: 0300-0000000</p>
            </div>
            <div class="col-4 text-end">
                <?php if ($s['photo']): ?>
                    <img src="<?=BASE_URL?>assets/uploads/students/<?=htmlspecialchars($s['photo'])?>" style="width: 80px; height: 80px; object-fit: cover; border: 1px solid #ddd;">
                <?php else: ?>
                    <div style="width: 80px; height: 80px; display:inline-block; border: 1px dashed #ccc; line-height: 80px; text-align: center; color: #999;">PHOTO</div>
                <?php endif; ?>
            </div>
        </div>

        <h5 class="text-center bg-light py-2 fw-bold mb-4 border text-uppercase">Admission Confirmation Slip - <?= $context ?></h5>

        <div class="row g-3">
            <div class="col-6">
                <div class="small text-muted">Student Name:</div>
                <div class="fw-bold border-bottom pb-1"><?= htmlspecialchars($s['full_name']) ?></div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Admission No:</div>
                <div class="fw-bold border-bottom pb-1 text-primary"><?= htmlspecialchars($s['admission_no']) ?></div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Father's Name:</div>
                <div class="fw-bold border-bottom pb-1"><?= htmlspecialchars($s['father_name']) ?></div>
            </div>
            <div class="col-6">
                <div class="small text-muted">Class/Program:</div>
                <div class="fw-bold border-bottom pb-1"><?= htmlspecialchars($s['class_name']) ?></div>
            </div>
        </div>

        <div class="mt-4">
            <h6 class="fw-bold small border-bottom pb-1 mb-2 text-muted">Fee Structure / Applied Dues</h6>
            <table class="table table-sm table-bordered mb-0 small">
                <thead class="bg-light">
                    <tr><th>Fee Name</th><th class="text-end">Amount (Rs.)</th></tr>
                </thead>
                <tbody>
                    <?php 
                    $fees = $s['fees'] ?? [];
                    $total = 0;
                    if(empty($fees)) {
                        echo '<tr><td colspan="2" class="text-center text-muted">No specific fees assigned.</td></tr>';
                    }
                    foreach($fees as $f): 
                        $total += $f['amount'];
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($f['name']) ?></td>
                        <td class="text-end"><?= number_format($f['amount']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-light fw-bold">
                    <tr><td>Total Monthly/Initial Commitment</td><td class="text-end">Rs. <?= number_format($total) ?></td></tr>
                </tfoot>
            </table>
        </div>

        <div class="row items-center mt-5 pt-4">
            <div class="col-4 border-top text-center pt-2">
                <small class="text-muted">Guardian Signature</small>
            </div>
            <div class="col-4 text-center">
                <div class="text-muted fw-bold small">Official Stamp</div>
            </div>
            <div class="col-4 border-top text-center pt-2">
                <small class="text-muted small">Admission Officer</small>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>

<div class="row justify-content-center">
    <div class="col-md-9 no-print mb-4">
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-info-circle me-2"></i> Admission Slip is ready. One for office and one for parents.
            </div>
            <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
                <i class="fas fa-print me-1"></i> Print This Form
            </button>
        </div>
    </div>
    
    <div class="col-md-9 admission-form-container">
        <?= renderCopy("Office", $s, $context) ?>
        <div class="text-center my-4 no-print text-muted small"><i class="fas fa-cut me-2"></i> Cut along the line</div>
        <?= renderCopy("Parent", $s, $context) ?>
    </div>
</div>

<style>
@media print {
    .sidebar, .top-navbar, .no-print, .alert { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    body { background: white !important; }
    .admission-form-container { width: 100% !important; margin: 0 !important; }
    .admission-copy { page-break-inside: avoid; border-color: transparent !important; border-bottom: 2px dashed #999 !important; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

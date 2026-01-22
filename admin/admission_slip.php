<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin', 'accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$id = intval($_GET['id'] ?? 0);
$context_session = $_SESSION['context'] ?? 'School';

if (!$id):
    $recent_students = $pdo->prepare('SELECT id, admission_no, full_name, class_id FROM students WHERE institution_type = :ctx ORDER BY id DESC LIMIT 50');
    $recent_students->execute([':ctx' => $context_session]);
    $recent_students = $recent_students->fetchAll();
    
    $classes = $pdo->query('SELECT id, name FROM classes')->fetchAll(PDO::FETCH_KEY_PAIR);
    ?>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold"><i class="fas fa-file-invoice me-2"></i>Generate Admission Slip</div>
                <div class="card-body p-4">
                    <label class="form-label fw-bold">Select Recent Student</label>
                    <div class="list-group">
                        <?php foreach ($recent_students as $rs): ?>
                            <a href="?id=<?=$rs['id']?>" class="list-group-item list-group-item-action">
                                <span class="fw-bold"><?=$rs['admission_no']?></span> - <?=htmlspecialchars($rs['full_name'])?> 
                                <span class="badge bg-light text-dark float-end"><?=htmlspecialchars($classes[$rs['class_id']] ?? 'N/A')?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <hr class="my-4">
                    <p class="text-muted small">Search students in the <strong>Student Directory</strong> to print older slips.</p>
                </div>
            </div>
        </div>
    </div>
    <?php 
    require_once __DIR__ . '/../includes/footer.php';
    exit;
endif;

$stmt = $pdo->prepare('SELECT s.*, c.name AS class_name, sec.name AS section_name 
                       FROM students s 
                       LEFT JOIN classes c ON s.class_id = c.id 
                       LEFT JOIN sections sec ON s.section_id = sec.id 
                       WHERE s.id = :id');
$stmt->execute([':id' => $id]);
$s = $stmt->fetch();

if (!$s) { echo '<div class="alert alert-danger">Student not found.</div>'; require_once __DIR__ . '/../includes/footer.php'; exit; }

// Fetch all class names for smart filtering fallback
$all_classes = $pdo->query('SELECT name FROM classes')->fetchAll(PDO::FETCH_COLUMN);

// Fetch applicable fees: Class-specific OR Global (NULL class_id)
$class_id = $s['class_id'] ?? 0;
$student_class_name = $s['class_name'] ?? '';

$fstmt = $pdo->prepare('SELECT * FROM fees WHERE (class_id = :cid OR class_id IS NULL)');
$fstmt->execute([':cid' => $class_id]);
$raw_fees = $fstmt->fetchAll();

$s['fees'] = [];
foreach ($raw_fees as $f) {
    // 1. If explicitly linked by ID, it belongs to this student.
    if (intval($f['class_id']) === intval($class_id)) {
        $s['fees'][] = $f;
        continue;
    }

    // 2. If it's a global fee (NULL), apply Smart Name Matching.
    $feeName = $f['name'];
    $normFee = strtolower(preg_replace('/[^a-zA-Z0-9]/', ' ', $feeName));
    $normStudentClass = strtolower(preg_replace('/[^a-zA-Z0-9]/', ' ', $student_class_name));

    // Find the longest matching class name in the fee title.
    $bestMatch = '';
    foreach ($all_classes as $cn) {
        $nc = strtolower(preg_replace('/[^a-zA-Z0-9]/', ' ', $cn));
        if (preg_match('/\b' . preg_quote($nc, '/') . '\b/', $normFee)) {
            if (strlen($nc) > strlen($bestMatch)) {
                $bestMatch = $nc;
            }
        }
    }

    // If it mentions our class (as the best match) OR mentions no class at all.
    if ($bestMatch === $normStudentClass || $bestMatch === '') {
        $s['fees'][] = $f;
    }
}

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
                <?php if ($s['photo'] && file_exists(__DIR__ . '/../assets/uploads/students/' . $s['photo'])): ?>
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
                <div class="fw-bold border-bottom pb-1"><?= htmlspecialchars($s['class_name'] ?? 'N/A') ?></div>
            </div>
        </div>

        <div class="mt-4">
            <h6 class="fw-bold small border-bottom pb-1 mb-2 text-muted text-uppercase">Fee Structure / Applied Dues</h6>
            <table class="table table-sm table-bordered mb-0 small fee-table">
                <thead class="bg-light">
                    <tr><th>Fee Name</th><th class="text-end">Amount (Rs.)</th></tr>
                </thead>
                <tbody class="fee-rows">
                    <?php 
                    $fees = $s['fees'] ?? [];
                    $total = 0;
                    if(empty($fees)) {
                        echo '<tr><td colspan="2" class="text-center text-muted">No specific fees assigned.</td></tr>';
                    }
                    foreach($fees as $f): 
                        $total += $f['amount'];
                    ?>
                    <tr class="fee-row" data-type="<?=htmlspecialchars($f['fee_type'])?>" data-amount="<?= $f['amount'] ?>">
                        <td><?= htmlspecialchars($f['name']) ?></td>
                        <td class="text-end"><?= number_format($f['amount']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="bg-light fw-bold">
                    <tr><td>Total Monthly/Initial Commitment</td><td class="text-end">Rs. <span class="total-amount"><?= number_format($total) ?></span></td></tr>
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
        <div class="card shadow-sm border-0 mb-3 bg-white">
            <div class="card-body p-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="fw-bold mb-2"><i class="fas fa-print me-2 text-primary"></i>Printing Options</h6>
                        <div class="d-flex gap-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input fee-toggle" type="checkbox" data-type="Admission" checked id="toggleAdm">
                                <label class="form-check-label small" for="toggleAdm">Admission Fee</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input fee-toggle" type="checkbox" data-type="Exam" checked id="toggleExm">
                                <label class="form-check-label small" for="toggleExm">Exam Fee</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input fee-toggle" type="checkbox" data-type="Practical" checked id="togglePrac">
                                <label class="form-check-label small" for="togglePrac">Practical Fee</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <button onclick="window.print()" class="btn btn-primary px-4">
                            <i class="fas fa-print me-2"></i> Print Slip
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-9 admission-form-container">
        <?= renderCopy("Office", $s, $context) ?>
        <div class="text-center my-4 no-print text-muted small"><i class="fas fa-cut me-2"></i> Cut along the line</div>
        <?= renderCopy("Parent", $s, $context) ?>
    </div>
</div>

<script>
document.querySelectorAll('.fee-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const type = this.getAttribute('data-type');
        const checked = this.checked;
        
        document.querySelectorAll(`.fee-row[data-type="${type}"]`).forEach(row => {
            row.style.display = checked ? '' : 'none';
        });
        
        updateTotals();
    });
});

function updateTotals() {
    document.querySelectorAll('.admission-copy').forEach(copy => {
        let total = 0;
        copy.querySelectorAll('.fee-row').forEach(row => {
            if (row.style.display !== 'none') {
                total += parseFloat(row.getAttribute('data-amount'));
            }
        });
        copy.querySelector('.total-amount').textContent = total.toLocaleString();
    });
}
</script>

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

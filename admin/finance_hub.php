<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin', 'accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$active_tab = $_GET['tab'] ?? 'dues'; // dues, ledgers, structure

// Shared Data
$classes = $pdo->query('SELECT * FROM classes ORDER BY name')->fetchAll();
$students_search = $pdo->query('SELECT id, admission_no, full_name FROM students WHERE status="Active"')->fetchAll();

?>
<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h1 class="h3 mb-1">Finance Intelligence Hub</h1>
        <p class="text-muted mb-0">Unified management of institutional revenue, dues, and student ledgers.</p>
    </div>
    <div class="no-print">
        <a href="<?=BASE_URL?>admin/fee_pay.php" class="btn btn-primary shadow-sm px-4">
            <i class="fas fa-hand-holding-usd me-2"></i> Collect Fees
        </a>
    </div>
</div>

<!-- Finance Tabs -->
<ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm no-print" id="financeTabs">
    <li class="nav-item">
        <a class="nav-link <?= $active_tab == 'dues' ? 'active shadow' : '' ?>" href="?tab=dues">
            <i class="fas fa-exclamation-circle me-2"></i> Current Dues
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $active_tab == 'ledgers' ? 'active shadow' : '' ?>" href="?tab=ledgers">
            <i class="fas fa-book me-2"></i> Student Ledgers
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $active_tab == 'structure' ? 'active shadow' : '' ?>" href="?tab=structure">
            <i class="fas fa-cog me-2"></i> Fee Structure
        </a>
    </li>
</ul>

<div class="tab-content border-0">
    <!-- TAB: CURRENT DUES -->
    <?php if ($active_tab == 'dues'): 
        $currentMonth = date('Y-m');
        $stmt = $pdo->prepare("
            SELECT s.id, s.admission_no, s.full_name, c.name AS class_name, sec.name AS section_name,
            (SELECT MAX(paid_on) FROM fee_payments WHERE student_id = s.id) as last_payment
            FROM students s 
            LEFT JOIN classes c ON s.class_id=c.id 
            LEFT JOIN sections sec ON s.section_id=sec.id
            WHERE s.status='Active' 
            AND s.id NOT IN (
                SELECT student_id FROM fee_payments 
                WHERE DATE_FORMAT(paid_on, '%Y-%m') = :m
            )
            ORDER BY s.admission_no
        ");
        $stmt->execute([':m' => $currentMonth]);
        $pending = $stmt->fetchAll();
    ?>
        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold"><i class="fas fa-user-clock me-2 text-warning"></i>Unpaid Dues for <?=date('F Y')?></h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="small text-uppercase">
                            <th class="ps-4">Admission No</th>
                            <th>Student Name</th>
                            <th>Class / Section</th>
                            <th>Last Payment Date</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $p): ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary"><?=htmlspecialchars($p['admission_no'])?></td>
                            <td><?=htmlspecialchars($p['full_name'])?></td>
                            <td>
                                <span class="badge bg-light text-dark border"><?=htmlspecialchars($p['class_name'])?></span>
                                <span class="badge bg-light text-dark border"><?=htmlspecialchars($p['section_name'] ?: 'A')?></span>
                            </td>
                            <td><?= $p['last_payment'] ? date('d M Y', strtotime($p['last_payment'])) : '<span class="text-danger small italic">Never Paid</span>' ?></td>
                            <td class="text-end pe-4">
                                <a href="<?=BASE_URL?>admin/fee_pay.php?student_id=<?=$p['id']?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-hand-holding-usd me-1"></i> Collect
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; if(empty($pending)) echo '<tr><td colspan="5" class="text-center py-5">All students have cleared their dues.</td></tr>'; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- TAB: STUDENT LEDGERS -->
    <?php elseif ($active_tab == 'ledgers'): 
        $selected_student = intval($_GET['student_id'] ?? 0);
        $payments = []; $student = null; $total_paid = 0;
        if ($selected_student) {
            $stmt = $pdo->prepare('SELECT s.*, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id=c.id WHERE s.id = :id');
            $stmt->execute([':id'=>$selected_student]);
            $student = $stmt->fetch();
            $stmt = $pdo->prepare('SELECT fp.*, f.name AS fee_name FROM fee_payments fp LEFT JOIN fees f ON fp.fee_id=f.id WHERE fp.student_id = :sid ORDER BY fp.paid_on DESC');
            $stmt->execute([':sid'=>$selected_student]);
            $payments = $stmt->fetchAll();
            $total_paid = array_reduce($payments, function($carry, $p) { return $carry + floatval($p['amount']); }, 0);
        }
    ?>
        <div class="card shadow-sm border-0 mb-4 bg-light p-3 no-print">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Select student to view ledger</label>
                    <select id="student_ledger_select" class="form-select select2">
                        <option value="">-- Start typing student name --</option>
                        <?php foreach ($students_search as $s): ?>
                            <option value="<?=$s['id']?>" <?=$s['id']==$selected_student?'selected':''?>><?=htmlspecialchars($s['admission_no'].' - '.$s['full_name'])?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <?php if ($student): ?>
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card shadow-sm border-0 h-100 border-start border-5 border-primary">
                        <div class="card-body d-flex align-items-center gap-4">
                            <div class="avatar bg-primary-subtle text-primary rounded-circle p-3 h2 mb-0"><i class="fas fa-user-graduate"></i></div>
                            <div>
                                <h4 class="mb-1 fw-bold"><?=htmlspecialchars($student['full_name'])?></h4>
                                <div class="text-muted small">Admission: <?=htmlspecialchars($student['admission_no'])?> | Class: <?=htmlspecialchars($student['class_name'])?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 h-100 bg-success text-white">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <div class="h6 mb-0 opacity-75">Life-time Contribution</div>
                            <div class="h2 fw-bold mb-0">Rs. <?=number_format($total_paid, 0)?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Payment History</h6>
                    <button onclick="window.print()" class="btn btn-sm btn-outline-secondary no-print"><i class="fas fa-print"></i></button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr class="small text-uppercase">
                                <th class="ps-4">#</th>
                                <th>Fee Category</th>
                                <th>Amount</th>
                                <th>Paid On</th>
                                <th>Method</th>
                                <th class="pe-4">Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $p): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?=htmlspecialchars($p['id'])?></td>
                                <td><span class="badge bg-light text-dark border"><?=htmlspecialchars($p['fee_name'] ?: 'Custom Allocation')?></span></td>
                                <td class="fw-bold">Rs. <?=number_format($p['amount'], 2)?></td>
                                <td><?=date('d M Y', strtotime($p['paid_on']))?></td>
                                <td><span class="badge bg-primary-subtle text-primary border border-primary"><?=htmlspecialchars($p['payment_method'])?></span></td>
                                <td class="small text-muted pe-4"><?=htmlspecialchars($p['note'])?></td>
                            </tr>
                            <?php endforeach; if(empty($payments)) echo '<tr><td colspan="6" class="text-center py-5">No payments found.</td></tr>'; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center p-5 text-muted bg-white rounded shadow-sm border-dashed">
                <i class="fas fa-file-invoice-dollar fa-3x mb-3 opacity-25"></i>
                <h5>Select a student to load their financial history.</h5>
            </div>
        <?php endif; ?>

    <!-- TAB: FEE STRUCTURE -->
    <?php elseif ($active_tab == 'structure'): 
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fee_name'])) {
            $stmt = $pdo->prepare('INSERT INTO fees (name, description, amount, fee_type, class_id, created_at) VALUES (:n, :d, :a, :t, :c, NOW())');
            $stmt->execute([
                ':n'=>$_POST['fee_name'], ':d'=>$_POST['description'], ':a'=>$_POST['amount'], 
                ':t'=>$_POST['fee_type'], ':c'=>($_POST['class_id'] ?: null)
            ]);
            echo "<script>window.location='?tab=structure&msg=success';</script>";
        }
        $fees = $pdo->query('SELECT f.*, c.name AS class_name FROM fees f LEFT JOIN classes c ON f.class_id = c.id ORDER BY f.id DESC')->fetchAll();
    ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Active Fee Categories</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr class="small text-uppercase">
                                    <th class="ps-4">Title</th>
                                    <th>Target Class</th>
                                    <th>Type</th>
                                    <th class="text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fees as $f): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?=htmlspecialchars($f['name'])?></td>
                                    <td><span class="badge bg-light text-dark border"><?=htmlspecialchars($f['class_name'] ?: 'Global')?></span></td>
                                    <td><span class="badge bg-info-subtle text-info border border-info"><?=htmlspecialchars($f['fee_type'])?></span></td>
                                    <td class="text-end pe-4 fw-bold text-success">Rs. <?=number_format($f['amount'], 2)?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 bg-light p-1">
                    <div class="card-header bg-transparent border-0"><h6 class="mb-0 fw-bold">Configure New fee</h6></div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Fee Title</label>
                                <input class="form-control" name="fee_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Amount (PKR)</label>
                                <input class="form-control" name="amount" type="number" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Fee Type</label>
                                <select name="fee_type" class="form-select">
                                    <option>Monthly</option><option>Admission</option><option>Exam</option>
                                    <option>Practical</option><option>Transport</option><option>Other</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Target Class</label>
                                <select name="class_id" class="form-select">
                                    <option value="">Global (All Classes)</option>
                                    <?php foreach ($classes as $c): ?>
                                        <option value="<?=$c['id']?>"><?=$c['name']?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-dark w-100 shadow-sm mt-2">Save Category</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single { height: 38px; border: 1px solid #dee2e6; padding-top: 5px; }
.border-dashed { border: 2px dashed #dee2e6 !important; }
@media print { .no-print { display: none !important; } .main-content { margin: 0; padding: 0; } }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2').select2({ placeholder: "Search..." });
    $('#student_ledger_select').on('change', function() {
        if(this.value) window.location = '?tab=ledgers&student_id=' + this.value;
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

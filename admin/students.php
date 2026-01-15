<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$context = $_SESSION['context'] ?? 'School';

$stmt = $pdo->prepare('SELECT s.*, c.name AS class_name, sec.name AS section_name 
                       FROM students s 
                       LEFT JOIN classes c ON s.class_id = c.id 
                       LEFT JOIN sections sec ON s.section_id = sec.id 
                       WHERE s.institution_type = :ctx 
                       ORDER BY s.id DESC');
$stmt->execute([':ctx' => $context]);
$students = $stmt->fetchAll();

?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><?= $context ?> Directory</h1>
        <p class="text-muted small">Managing all <?= strtolower($context) ?> enrollments.</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i> Print Directory
        </button>
        <a href="<?=BASE_URL?>admin/student_add.php" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Student
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users me-2"></i>Recent Admissions</span>
        <span class="badge bg-primary">Showing last 200</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Admission No</th>
                    <th>Full Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <td class="ps-4 fw-bold text-primary"><?=htmlspecialchars($s['admission_no'])?></td>
                    <td><?=htmlspecialchars($s['full_name'])?></td>
                    <td><span class="badge bg-light text-dark"><?=htmlspecialchars($s['class_name'])?></span></td>
                    <td><span class="badge bg-light text-dark"><?=htmlspecialchars($s['section_name'] ?: 'N/A')?></span></td>
                    <td>
                        <span class="badge <?=($s['status']=='Active'?'bg-success':'bg-secondary')?>">
                            <?=htmlspecialchars($s['status'])?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group">
                            <a href="<?=BASE_URL?>admin/fee_pay.php?student_id=<?=$s['id']?>" class="btn btn-sm btn-primary" title="Collect Fees">
                                <i class="fas fa-cash-register"></i>
                            </a>
                            <a href="<?=BASE_URL?>admin/student_edit.php?id=<?=$s['id']?>" class="btn btn-sm btn-outline-primary" title="Edit Profile">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?=BASE_URL?>admin/student_id_card.php?id=<?=$s['id']?>" class="btn btn-sm btn-outline-info" title="Print ID Card" target="_blank">
                                <i class="fas fa-id-card"></i>
                            </a>
                            <a href="<?=BASE_URL?>admin/ledger.php?student_id=<?=$s['id']?>" class="btn btn-sm btn-outline-success" title="View Fee Ledger">
                                <i class="fas fa-book"></i>
                            </a>
                            <a href="<?=BASE_URL?>admin/student_status.php?id=<?=$s['id']?>&status=<?=($s['status']=='Active'?'Left':'Active')?>" 
                               class="btn btn-sm <?=($s['status']=='Active'?'btn-outline-secondary':'btn-outline-success')?>" 
                               title="Toggle Status (Active/Left)"
                               onclick="return confirm('Change status to <?=($s['status']=='Active'?'Left':'Active')?>?')">
                                <i class="fas <?=($s['status']=='Active'?'fa-user-slash':'fa-user-check')?>"></i>
                            </a>
                            <a href="<?=BASE_URL?>admin/student_delete.php?id=<?=$s['id']?>" class="btn btn-sm btn-outline-danger" 
                               title="Permanent Delete"
                               onclick="return confirm('ARE YOU SURE? This will remove the student and all their history!')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

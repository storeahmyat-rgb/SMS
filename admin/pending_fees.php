<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();

// Find students with unpaid dues
// Find students and their payments
$stmt = $pdo->query('SELECT s.id, s.admission_no, s.full_name, s.class_id, c.name AS class_name, COALESCE(SUM(fp.amount), 0) AS total_paid FROM students s LEFT JOIN classes c ON s.class_id=c.id LEFT JOIN fee_payments fp ON s.id=fp.student_id WHERE s.status="Active" GROUP BY s.id ORDER BY s.admission_no');
$pending = $stmt->fetchAll();

?>
<h1>Monthly Dues Overview</h1>
<p class="text-muted">Tracking students who have not yet cleared their fees for <strong><?=date('F Y')?></strong>.</p>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Unpaid Dues (<?=date('F Y')?>)</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Admission No</th>
                    <th>Student Name</th>
                    <th>Class / Section</th>
                    <th>Last Payment Date</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            // Better query: Students who don't have a payment in the current month
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

            if (empty($pending)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">All students have cleared their dues for this month!</td></tr>
            <?php endif; ?>

            <?php foreach ($pending as $p): ?>
                <tr>
                    <td class="ps-4 fw-bold text-primary"><?=htmlspecialchars($p['admission_no'])?></td>
                    <td><?=htmlspecialchars($p['full_name'])?></td>
                    <td>
                        <span class="badge bg-light text-dark"><?=htmlspecialchars($p['class_name'])?></span>
                        <span class="badge bg-light text-dark"><?=htmlspecialchars($p['section_name'] ?: 'N/A')?></span>
                    </td>
                    <td><?= $p['last_payment'] ? date('d M Y', strtotime($p['last_payment'])) : '<span class="text-danger small italic">Never Paid</span>' ?></td>
                    <td class="text-end pe-4">
                        <a href="<?=BASE_URL?>admin/fee_pay.php?student_id=<?=$p['id']?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-hand-holding-usd me-1"></i> Collect
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

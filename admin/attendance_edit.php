<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$stmt = $pdo->query('SELECT ae.*, sa.student_id, sa.attendance_date, u.username AS editor FROM attendance_edits ae JOIN student_attendance sa ON ae.attendance_id=sa.id JOIN users u ON ae.edited_by = u.id ORDER BY ae.edited_at DESC LIMIT 200');
$edits = $stmt->fetchAll();

?>
<h1>Attendance Audit Trail</h1>
<p class="text-muted">A history of manual adjustments made to student attendance records.</p>

<div class="card">
    <div class="card-header bg-white"><i class="fas fa-history me-2"></i>Detailed Edit Log</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>Attendance Ref</th>
                    <th>Status Shift</th>
                    <th>Edited By</th>
                    <th>Timetamp</th>
                    <th class="pe-4">Reason / Notes</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($edits as $e): ?>
                <tr>
                    <td class="ps-4"><?=htmlspecialchars($e['id'])?></td>
                    <td>
                        <small class="text-muted text-uppercase d-block">Student ID: <?=htmlspecialchars($e['student_id'])?></small>
                        <span class="fw-bold">Date: <?=date('d M Y', strtotime($e['attendance_date']))?></span>
                    </td>
                    <td>
                        <span class="badge bg-secondary"><?=htmlspecialchars($e['old_status'])?></span>
                        <i class="fas fa-arrow-right mx-2 text-muted"></i>
                        <span class="badge bg-primary"><?=htmlspecialchars($e['new_status'])?></span>
                    </td>
                    <td class="fw-bold"><?=htmlspecialchars($e['editor'])?></td>
                    <td><?=date('d M Y, H:i', strtotime($e['edited_at']))?></td>
                    <td class="pe-4"><em><?=htmlspecialchars($e['reason'] ?: 'No reason provided')?></em></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

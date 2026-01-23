<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT sa.*, t.full_name, t.teacher_id as t_code, t.designation, t.salary as base_salary FROM salaries sa JOIN teachers t ON sa.teacher_id = t.id WHERE sa.id = :id');
$stmt->execute([':id'=>$id]);
$s = $stmt->fetch();

if (!$s) { echo 'Salary record not found'; exit; }

?>
<div class="row justify-content-center mt-5">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border: 2px solid #eee !important;">
            <div class="card-body p-0">
                <div class="bg-dark text-white p-4 text-center border-bottom">
                    <h2 class="fw-bold mb-1 text-uppercase">Modern School System</h2>
                    <p class="mb-0 opacity-75">FACULTY SALARY DISBURSEMENT SLIP</p>
                </div>
                <div class="p-4">
                    <div class="row mb-5">
                        <div class="col-6">
                            <h6 class="text-uppercase small text-muted font-weight-bold mb-3">Employee Particulars</h6>
                            <div class="fs-5 fw-bold text-primary"><?=htmlspecialchars($s['full_name'])?></div>
                            <div class="text-muted fw-bold"><?=htmlspecialchars($s['designation'] ?: 'Faculty Member')?></div>
                            <div class="small mt-1">Employee ID: <span class="fw-bold"><?=htmlspecialchars($s['t_code'])?></span></div>
                        </div>
                        <div class="col-6 text-end">
                            <h6 class="text-uppercase small text-muted font-weight-bold mb-3">Voucher Details</h6>
                            <div class="fs-5 fw-bold"><?=date('F Y', strtotime($s['month_year'].'-01'))?></div>
                            <div class="text-muted small">Voucher #: <span class="fw-bold">SLP-<?=htmlspecialchars($s['id'])?></span></div>
                            <div class="text-muted small">Paid On: <span class="fw-bold"><?=date('d M Y', strtotime($s['paid_on']))?></span></div>
                        </div>
                    </div>

                    <table class="table table-bordered mb-4">
                        <thead class="bg-light"><tr class="small text-uppercase"><th>Earnings Description</th><th class="text-end">Amount (PKR)</th></tr></thead>
                        <tbody>
                            <tr>
                                <td class="py-3">Base Contractual Salary<br><small class="text-muted">Standard monthly committed amount</small></td>
                                <td class="text-end py-3 fw-bold"><?=number_format($s['base_salary'], 2)?></td>
                            </tr>
                            <tr>
                                <td class="py-3">Attendance Proration Adjustment<br><small class="text-muted">Based on worked days (Present/Leave/Late)</small></td>
                                <td class="text-end py-3 text-<?=($s['amount'] < $s['base_salary'] ? 'danger' : 'success')?>">
                                    <?=number_format($s['amount'] - $s['base_salary'], 2)?>
                                </td>
                            </tr>
                            <?php if($s['bonus_deduction'] != 0): ?>
                            <tr>
                                <td class="py-3">Manual Adjustments (Bonus/Deduction)<br><small class="text-muted"><?=htmlspecialchars($s['payment_notes'] ?: 'No specific notes')?></small></td>
                                <td class="text-end py-3 <?=($s['bonus_deduction'] > 0 ? 'text-success' : 'text-danger')?>">
                                    <?=number_format($s['bonus_deduction'], 2)?>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr class="fw-bold fs-4 bg-light">
                                <td class="py-3">Net Disbursement (<?=$s['payment_method']?>)</td>
                                <td class="text-end py-3 text-dark">Rs. <?=number_format($s['total_payout'], 2)?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row mt-5 pt-4">
                        <div class="col-6 text-center">
                            <div class="border-top pt-2 mx-5 small text-muted">Accountant / Principal Signature</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="border-top pt-2 mx-5 small text-muted">Faculty Member Signature</div>
                        </div>
                    </div>

                    <div class="mt-5 text-center text-muted small border-top pt-3 no-print">
                        <p>This is an electronically generated document. For inquiries, please contact the accounts office.</p>
                        <button onclick="window.print()" class="btn btn-dark px-5 mt-2 shadow-sm"><i class="fas fa-print me-2"></i> Print Official Slip</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .top-navbar, .no-print { display: none !important; }
    .main-content { margin-left: 0 !important; margin-top: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    body { background: white !important; font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    .table th { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

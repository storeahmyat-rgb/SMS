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
        <div class="card border shadow-none" style="border: 2px solid #333 !important;">
            <div class="card-body p-0">
                <div class="bg-light p-4 text-center border-bottom">
                    <h3 class="fw-bold mb-1 text-uppercase">Modern School System</h3>
                    <p class="mb-0 text-muted">Monthly Staff Salary Pay-Slip</p>
                </div>
                <div class="p-4">
                    <div class="row mb-4">
                        <div class="col-6">
                            <h6 class="text-uppercase small text-muted font-weight-bold">Employee Particulars</h6>
                            <div class="fs-5 fw-bold"><?=htmlspecialchars($s['full_name'])?></div>
                            <div class="text-muted"><?=htmlspecialchars($s['designation'] ?: 'Faculty Member')?></div>
                            <div class="small">Employee ID: <?=htmlspecialchars($s['t_code'])?></div>
                        </div>
                        <div class="col-6 text-end">
                            <h6 class="text-uppercase small text-muted font-weight-bold">Pay Period</h6>
                            <div class="fs-5 fw-bold"><?=date('F Y', strtotime($s['month_year'].'-01'))?></div>
                            <div class="text-muted">Voucher #SLP-<?=htmlspecialchars($s['id'])?></div>
                        </div>
                    </div>

                    <table class="table table-bordered">
                        <thead><tr class="bg-light"><th>Description</th><th class="text-end">Amount (Rs.)</th></tr></thead>
                        <tbody>
                            <tr><td>Base Monthly Salary</td><td class="text-end"><?=number_format($s['base_salary'], 2)?></td></tr>
                            <tr style="height: 100px;">
                                <td class="ps-3 pt-3">
                                    <div class="fw-bold">Attendance Proration</div>
                                    <small class="text-muted">Calculated based on actual working days (Present/Leave/Late).</small>
                                </td>
                                <td class="text-end pt-3 text-danger">Audit Applied</td>
                            </tr>
                            <tr class="fw-bold fs-5">
                                <td class="bg-light">Net Payable Amount</td>
                                <td class="bg-light text-end text-primary">Rs. <?=number_format($s['amount'], 2)?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row mt-5 pt-3">
                        <div class="col-6 text-center">
                            <div class="border-top pt-2">Accountant Signature</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="border-top pt-2">Employee Signature</div>
                        </div>
                    </div>

                    <div class="mt-5 text-center text-muted small border-top pt-3 no-print">
                        <p>Note: This is a computer-generated document and does not require a physical stamp for internal school records.</p>
                        <button onclick="window.print()" class="btn btn-sm btn-primary px-4 mt-2"><i class="fas fa-print me-1"></i> Print Pay Slip</button>
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
    .card { border: none !important; }
    body { background: white !important; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
// attempt to use TCPDF if available
$useTCPDF = false;
if (file_exists(__DIR__ . '/../vendor/tcpdf/tcpdf.php')) {
    $useTCPDF = true;
    require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';
}

$pdo = getPDO();
$id = intval($_GET['id'] ?? 0);
$tid = $_GET['tid'] ?? '';

if ($tid) {
    // Fetch multiple payments for a transaction
    $stmt = $pdo->prepare('SELECT fp.*, s.full_name, s.admission_no, f.name AS fee_name 
                           FROM fee_payments fp 
                           LEFT JOIN students s ON fp.student_id=s.id 
                           LEFT JOIN fees f ON fp.fee_id=f.id 
                           WHERE fp.transaction_id = :tid');
    $stmt->execute([':tid'=>$tid]);
    $payments = $stmt->fetchAll();
    if (!$payments) { echo 'Transaction not found'; exit; }
    $p = $payments[0]; // Header info from first record
} else {
    // Legacy single payment view
    $stmt = $pdo->prepare('SELECT fp.*, s.full_name, s.admission_no, f.name AS fee_name 
                           FROM fee_payments fp 
                           LEFT JOIN students s ON fp.student_id=s.id 
                           LEFT JOIN fees f ON fp.fee_id=f.id 
                           WHERE fp.id = :id');
    $stmt->execute([':id'=>$id]);
    $p = $stmt->fetch();
    if (!$p) { echo 'Payment not found'; exit; }
    $payments = [$p];
}

$total_amount = array_reduce($payments, function($carry, $item) {
    return $carry + $item['amount'];
}, 0);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-9">
        <!-- Professional Print Form -->
        <div class="card shadow-none border" style="border: 2px solid #eef2f7 !important;">
            <div class="card-body p-0">
                <!-- Header with School Branding -->
                <div class="p-4 text-center border-bottom bg-light">
                    <h2 class="fw-bold text-dark text-uppercase mb-1">Modern School System</h2>
                    <p class="mb-0 text-muted">Education Colony, Phase 2, Karachi, Pakistan</p>
                    <p class="small text-muted mb-0">Contact: +92 300 0000000 | Email: info@school.pk</p>
                </div>
                
                <div class="p-4">
                    <div class="row mb-5">
                        <div class="col-6">
                            <h5 class="text-primary text-uppercase small fw-bold mb-3">Student Particulars</h5>
                            <table class="table table-sm no-hover mb-0">
                                <tr><td class="ps-0 py-1 text-muted">Admission No:</td><td class="fw-bold py-1"><?=htmlspecialchars($p['admission_no'])?></td></tr>
                                <tr><td class="ps-0 py-1 text-muted">Student Name:</td><td class="fw-bold py-1"><?=htmlspecialchars($p['full_name'])?></td></tr>
                                <tr><td class="ps-0 py-1 text-muted">Fee Type:</td><td class="py-1"><?=htmlspecialchars($p['fee_name'] ?: 'Miscellaneous')?></td></tr>
                            </table>
                        </div>
                        <div class="col-6 text-end">
                            <h5 class="text-primary text-uppercase small fw-bold mb-3">Voucher Details</h5>
                            <table class="table table-sm no-hover mb-0">
                                <tr><td class="py-1 text-muted">Voucher/TXN ID:</td><td class="fw-bold py-1">#<?=htmlspecialchars($p['transaction_id'] ?: $p['id'])?></td></tr>
                                <tr><td class="py-1 text-muted">Payment Date:</td><td class="py-1"><?=date('d-M-Y', strtotime($p['paid_on']))?></td></tr>
                                <tr><td class="py-1 text-muted">Payment Mode:</td><td class="py-1"><?=htmlspecialchars($p['payment_method'])?></td></tr>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 py-3">Description of Transaction</th>
                                    <th class="text-end pe-3 py-3" style="width: 200px;">Amount Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $item): ?>
                                <tr>
                                    <td class="ps-3 py-2">
                                        <div class="fw-bold text-dark"><?=htmlspecialchars($item['fee_name'] ?: 'General Fee Deposit')?></div>
                                        <div class="small text-muted"><?=htmlspecialchars($item['note'] ?: 'Payment received for academic dues.')?></div>
                                    </td>
                                    <td class="text-end pe-3 py-2 fw-bold">
                                        Rs. <?=number_format($item['amount'], 2)?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <td class="text-end fw-bold py-3">Grand Total (Net Collection):</td>
                                    <td class="text-end pe-3 fw-bold text-primary fs-4 py-3">Rs. <?=number_format($total_amount, 2)?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Pakistani Style Stamp/Signature Area -->
                    <div class="row mt-5 pt-5">
                        <div class="col-4 border-top text-center pt-2 mt-4">
                            <small class="text-muted">Signature of Parent/Guardian</small>
                        </div>
                        <div class="col-4"></div>
                        <div class="col-4 border-top text-center pt-2 mt-4 font-heading">
                            <small class="text-dark fw-bold">Authorized Account Officer</small>
                            <div class="small text-muted">Modern School System</div>
                        </div>
                    </div>
                </div>

                <!-- Footer Note -->
                <div class="bg-light p-3 text-center border-top">
                    <p class="small text-muted mb-0">Note: Fees once paid are non-refundable. Please keep this receipt safe for your records.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-primary px-5 btn-lg shadow-sm">
                <i class="fas fa-print me-2"></i> Print Official Receipt
            </button>
            <a href="<?=BASE_URL?>admin/fee_pay.php" class="btn btn-light ms-2 px-4">Collect More</a>
        </div>
    </div>
</div>
        
        <?php if (!$useTCPDF): ?>
        <div class="mt-3 alert alert-light text-center no-print">
            <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Tip: To save as PDF, click Print and select "Save as PDF" as your printer.</small>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
@media print {
    .sidebar, .top-navbar, .no-print {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<?php
require_once __DIR__ . '/../includes/footer.php';
exit;
?>

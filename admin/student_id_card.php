<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT s.*, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.id = :id');
$stmt->execute([':id'=>$id]);
$s = $stmt->fetch();

if (!$s) { echo 'Student not found'; exit; }

?>
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <!-- Professional Print Form -->
        <div class="card shadow-none border" style="width: 350px; height: 550px; margin: auto; border: 2px solid #0d6efd !important;">
            <div class="card-body p-0 d-flex flex-column h-100">
                <!-- Header -->
                <div class="bg-primary text-white p-3 text-center">
                    <h5 class="fw-bold mb-0 text-uppercase">Modern School System</h5>
                    <small>Identity Card</small>
                </div>
                
                <!-- ID Card Content -->
                <div class="p-4 flex-grow-1 text-center">
                <div class="mb-4 d-inline-block p-1 border rounded bg-light overflow-hidden" style="width: 120px; height: 120px;">
                    <?php if ($s['photo']): ?>
                        <img src="<?=BASE_URL?>assets/uploads/students/<?=htmlspecialchars($s['photo'])?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user-graduate fa-5x text-secondary mt-3"></i>
                    <?php endif; ?>
                </div>
                    
                    <h4 class="fw-bold mb-1 text-uppercase"><?=htmlspecialchars($s['full_name'])?></h4>
                    <div class="badge bg-light text-primary border border-primary mb-4"><?=htmlspecialchars($s['admission_no'])?></div>
                    
                    <table class="table table-sm no-hover text-start mx-auto" style="max-width: 250px;">
                        <tr><td class="text-muted border-0">Class:</td><td class="fw-bold border-0"><?=htmlspecialchars($s['class_name'])?></td></tr>
                        <tr><td class="text-muted border-0">Father:</td><td class="fw-bold border-0"><?=htmlspecialchars($s['father_name'])?></td></tr>
                        <tr><td class="text-muted border-0">Contact:</td><td class="fw-bold border-0"><?=htmlspecialchars($s['contact'])?></td></tr>
                        <tr><td class="text-muted border-0">Blood Grp:</td><td class="fw-bold border-0">B+</td></tr>
                    </table>
                </div>

                <!-- Footer -->
                <div class="bg-light p-3 border-top text-center mt-auto">
                    <div class="small text-muted mb-1 text-uppercase">Principal Signature</div>
                    <div class="fw-bold border-bottom border-dark mx-auto w-50" style="height: 20px;"></div>
                    <div class="small text-muted mt-3">Valid for Academic Session 2024-25</div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 no-print">
            <button onclick="window.print()" class="btn btn-lg btn-success px-5"><i class="fas fa-print me-2"></i> Print Official ID Card</button>
            <p class="mt-3 text-muted"><i class="fas fa-info-circle me-1"></i> Make sure to enable "Background Graphics" in print settings for best results.</p>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .top-navbar, .no-print { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .card { border: 2px solid #0d6efd !important; break-inside: avoid; }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

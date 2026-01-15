<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (!is_uploaded_file($file)) { $msg = 'Invalid file'; }
    else {
        try {
            $pdo->beginTransaction();
            $handle = fopen($file, 'r');
            $header = fgetcsv($handle);
            $count = 0;
            while (($row = fgetcsv($handle)) !== false) {
                // Expected format: device_user_id, mapped_type (student/teacher), mapped_id, timestamp
                if (count($row) >= 4) {
                    $stmt = $pdo->prepare('INSERT INTO biometric_logs (device_user_id, mapped_type, mapped_id, timestamp, raw_data) VALUES (:du, :mt, :mid, :ts, :rd)');
                    $stmt->execute([':du'=>$row[0], ':mt'=>$row[1], ':mid'=>$row[2], ':ts'=>$row[3], ':rd'=>implode('|', $row)]);
                    $count++;
                }
            }
            fclose($handle);
            $pdo->commit();
            $msg = "Imported $count biometric records";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $msg = 'Error: '.$e->getMessage();
        }
    }
}

?>
<h1>Biometric Data Synchronization</h1>
<p class="text-muted">Import attendance records from external biometric devices via CSV.</p>

<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 mb-4 h-100">
            <div class="card-header bg-white"><i class="fas fa-file-upload me-2 text-primary"></i>Upload Logs</div>
            <div class="card-body p-4">
                <?php if ($msg): ?>
                    <div class="alert alert-info border-0 shadow-sm mb-4">
                        <i class="fas fa-info-circle me-1"></i> <?=htmlspecialchars($msg)?>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label fw-bold font-sm">SELECT CSV FILE</label>
                        <input type="file" name="csv_file" class="form-control form-control-lg" accept=".csv" required>
                        <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Only .csv files are supported.</div>
                    </div>
                    <button class="btn btn-primary btn-lg w-100"><i class="fas fa-sync me-1"></i> Start Import Process</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm border-0 mb-4 h-100 bg-light">
            <div class="card-header bg-white"><i class="fas fa-info-circle me-2 text-info"></i>CSV Required Format</div>
            <div class="card-body">
                <p>The file must contain exactly 4 columns in the following order without any leading/trailing spaces:</p>
                <div class="bg-dark text-white p-3 rounded mb-3">
                    <code>device_user_id,mapped_type,mapped_id,timestamp</code>
                </div>
                <h6>Field Descriptions:</h6>
                <ul class="list-group list-group-flush bg-transparent">
                    <li class="list-group-item bg-transparent font-sm px-0"><strong>device_user_id:</strong> The unique ID assigned in the biometric machine.</li>
                    <li class="list-group-item bg-transparent font-sm px-0"><strong>mapped_type:</strong> Either 'student' or 'teacher'.</li>
                    <li class="list-group-item bg-transparent font-sm px-0"><strong>mapped_id:</strong> The database ID of the corresponding record.</li>
                    <li class="list-group-item bg-transparent font-sm px-0"><strong>timestamp:</strong> The exact date-time (YYYY-MM-DD HH:MM:SS).</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

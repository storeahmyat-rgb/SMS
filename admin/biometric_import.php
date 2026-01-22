<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$msg = '';
$stats = ['student' => 0, 'teacher' => 0, 'logs' => 0];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    if (!is_uploaded_file($file)) {
        $msg = 'Invalid file uploaded.';
    } else {
        try {
            $pdo->beginTransaction();
            $handle = fopen($file, 'r');
            $header = fgetcsv($handle); // Skip header row
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) >= 4) {
                    $duid = trim($row[0]);
                    $type = strtolower(trim($row[1])); // student or teacher
                    $mid  = intval($row[2]);
                    $ts   = trim($row[3]); // YYYY-MM-DD HH:MM:SS
                    
                    if (!$mid) continue;

                    $date = date('Y-m-d', strtotime($ts));
                    $time = date('H:i:s', strtotime($ts));

                    // 1. Insert into raw biometric_logs
                    $stmt = $pdo->prepare('INSERT INTO biometric_logs (device_user_id, mapped_type, mapped_id, timestamp, raw_data) VALUES (:du, :mt, :mid, :ts, :rd)');
                    $stmt->execute([':du'=>$duid, ':mt'=>$type, ':mid'=>$mid, ':ts'=>$ts, ':rd'=>implode('|', $row)]);
                    $stats['logs']++;

                    // 2. Process Attendance Update
                    if ($type === 'student') {
                        $table = 'student_attendance';
                        $col_id = 'student_id';
                        $stats['student']++;
                    } elseif ($type === 'teacher') {
                        $table = 'teacher_attendance';
                        $col_id = 'teacher_id';
                        $stats['teacher']++;
                    } else {
                        continue;
                    }

                    // Check if record exists for this date
                    $check = $pdo->prepare("SELECT id, in_time, out_time FROM $table WHERE $col_id = :mid AND attendance_date = :date");
                    $check->execute([':mid' => $mid, ':date' => $date]);
                    $existing = $check->fetch();

                    if ($existing) {
                        $new_in = $existing['in_time'];
                        $new_out = $existing['out_time'];

                        // If new time is earlier than existing in_time, update In-Time
                        if (!$new_in || $time < $new_in) {
                            $new_in = $time;
                        }
                        // If new time is later than existing out_time (or in_time), update Out-Time
                        if (!$new_out || $time > $new_out) {
                            if ($time > ($new_in ?: '00:00:00')) {
                                $new_out = $time;
                            }
                        }

                        $upd = $pdo->prepare("UPDATE $table SET in_time = :in, out_time = :out, status = 'Present' WHERE id = :id");
                        $upd->execute([':in' => $new_in, ':out' => $new_out, ':id' => $existing['id']]);
                    } else {
                        // Create new attendance record
                        $ins = $pdo->prepare("INSERT INTO $table ($col_id, attendance_date, in_time, status) VALUES (:mid, :date, :in, 'Present')");
                        $ins->execute([':mid' => $mid, ':date' => $date, ':in' => $time]);
                    }
                }
            }
            fclose($handle);
            $pdo->commit();
            $msg = "Success: Processed {$stats['logs']} logs. (Students: {$stats['student']}, Teachers: {$stats['teacher']})";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $msg = 'Error processing file: ' . $e->getMessage();
        }
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Biometric Sync (Students & Teachers)</h1>
    <a href="attendance_report.php" class="btn btn-outline-primary"><i class="fas fa-list me-1"></i> View Reports</a>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white fw-bold"><i class="fas fa-upload me-2 text-primary"></i>Upload Attendance CSV</div>
            <div class="card-body p-4">
                <?php if ($msg): ?>
                    <div class="alert <?= strpos($msg, 'Error') === false ? 'alert-success' : 'alert-danger' ?> border-0 shadow-sm mb-4">
                        <i class="fas <?= strpos($msg, 'Error') === false ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> me-2"></i> 
                        <?=htmlspecialchars($msg)?>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="mb-4 text-center p-4 border-dashed rounded bg-light">
                        <i class="fas fa-file-csv fa-3x text-muted mb-3"></i>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        <div class="form-text mt-2 small text-muted">Select the CSV file exported from your machine.</div>
                    </div>
                    <button class="btn btn-primary btn-lg w-100 shadow-sm">
                        <i class="fas fa-sync-alt me-2"></i> Sync All Attendance
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 bg-gradient-primary text-white">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-magic me-2"></i>Smart Sync Logic</h6>
                <p class="small mb-0 opacity-75">Our system automatically identifies whether a log belongs to a **Student** or **Teacher**. It also intelligently handles multiple swipes by recording the earliest as **In-Time** and the latest as **Out-Time** for the day.</p>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold"><i class="fas fa-info-circle me-2 text-info"></i>CSV Required Format</div>
            <div class="card-body">
                <p class="small text-muted mb-4">The machine CSV must follow this exact column structure to map records correctly to Students and Faculty members.</p>
                
                <div class="table-responsive">
                    <table class="table table-sm table-bordered bg-dark text-white font-monospace mb-4" style="font-size: 0.75rem;">
                        <thead class="bg-secondary text-white border-0">
                            <tr><th>device_user_id</th><th>mapped_type</th><th>mapped_id</th><th>timestamp</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>101</td><td>student</td><td>15</td><td>2024-01-22 08:30:00</td></tr>
                            <tr><td>502</td><td>teacher</td><td>3</td><td>2024-01-22 08:15:00</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light">
                            <span class="d-block fw-bold small">mapped_type</span>
                            <code class="small text-primary">student</code> or <code class="small text-primary">teacher</code>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light">
                            <span class="d-block fw-bold small">mapped_id</span>
                            <span class="small text-muted">ID of Student/Teacher in portal.</span>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning border-0 mt-4 small mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> Make sure the date format is <code>YYYY-MM-DD HH:MM:SS</code>.
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-dashed { border: 2px dashed #dee2e6 !important; }
.bg-gradient-primary { background: linear-gradient(45deg, #4e73df 0%, #224abe 100%); }
.font-monospace { font-family: 'Courier New', Courier, monospace; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

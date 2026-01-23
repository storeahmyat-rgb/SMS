<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

$exam_id = intval($_GET['exam_id'] ?? 0);
$student_id = intval($_GET['student_id'] ?? 0);

if (!$exam_id || !$student_id) {
    http_response_code(400);
    echo 'Missing exam or student ID';
    exit;
}

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT * FROM exams WHERE id = :id');
$stmt->execute([':id'=>$exam_id]);
$exam = $stmt->fetch();

$stmt = $pdo->prepare('SELECT * FROM students WHERE id = :id');
$stmt->execute([':id'=>$student_id]);
$student = $stmt->fetch();

$stmt = $pdo->prepare('SELECT r.*, su.name AS subject_name FROM results r LEFT JOIN subjects su ON r.subject_id=su.id WHERE exam_id = :e AND student_id = :s');
$stmt->execute([':e'=>$exam_id, ':s'=>$student_id]);
$results = $stmt->fetchAll();

// Check if TCPDF is available
$useTCPDF = file_exists(__DIR__ . '/../vendor/tcpdf/tcpdf.php');
if ($useTCPDF) {
    require_once __DIR__ . '/../vendor/tcpdf/tcpdf.php';
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);
    $html = '<h2>Result Card</h2>';
    $html .= '<p><strong>Exam:</strong> '.htmlspecialchars($exam['name']).'</p>';
    $html .= '<p><strong>Student:</strong> '.htmlspecialchars($student['admission_no'].' - '.$student['full_name']).'</p>';
    $html .= '<table border="1"><tr><th>Subject</th><th>Marks</th><th>Total</th><th>Grade</th></tr>';
    foreach ($results as $r) {
        $html .= '<tr><td>'.htmlspecialchars($r['subject_name']).'</td><td>'.htmlspecialchars($r['marks_obtained']).'</td><td>'.htmlspecialchars($r['total_marks']).'</td><td>'.htmlspecialchars($r['grade']).'</td></tr>';
    }
    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('result_card_'.$student_id.'.pdf', 'I');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card shadow-sm result-card-print">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-primary"><i class="fas fa-file-invoice me-2"></i>Academic Result Card</h4>
                <button onclick="window.print()" class="btn btn-sm btn-outline-secondary no-print">
                    <i class="fas fa-print me-1"></i> Print Result
                </button>
            </div>
            <div class="card-body p-5">
                <div class="row mb-4 text-center">
                    <div class="col-12">
                        <h2 class="mb-1"><?= SCHOOL_NAME ?></h2>
                        <p class="text-muted">Academic Excellence Report</p>
                        <hr>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted text-uppercase small">Student Details</h6>
                        <div class="fs-5 fw-bold"><?=htmlspecialchars($student['full_name'])?></div>
                        <div><strong>Admission No:</strong> <?=htmlspecialchars($student['admission_no'])?></div>
                        <div><strong>Roll No:</strong> <?=htmlspecialchars($student['roll_no'] ?? 'N/A')?></div>
                    </div>
                    <div class="col-sm-6 text-sm-end">
                        <h6 class="text-muted text-uppercase small">Examination</h6>
                        <div class="fs-5 fw-bold text-primary"><?=htmlspecialchars($exam['name'])?></div>
                        <div><strong>Term:</strong> <?=htmlspecialchars($exam['term'] ?? 'Final')?></div>
                        <div><strong>Date:</strong> <?=date('d M Y')?></div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="bg-light">
                            <tr>
                                <th>Subject</th>
                                <th class="text-center">Obtained Marks</th>
                                <th class="text-center">Total Marks</th>
                                <th class="text-center">Percentage</th>
                                <th class="text-center">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grand_total = 0; $grand_obtained = 0;
                            foreach ($results as $r): 
                                $perc = ($r['total_marks'] > 0) ? ($r['marks_obtained'] / $r['total_marks'] * 100) : 0;
                                $grand_total += $r['total_marks'];
                                $grand_obtained += $r['marks_obtained'];
                            ?>
                                <tr>
                                    <td><?=htmlspecialchars($r['subject_name'])?></td>
                                    <td class="text-center"><?=htmlspecialchars($r['marks_obtained'])?></td>
                                    <td class="text-center"><?=htmlspecialchars($r['total_marks'])?></td>
                                    <td class="text-center"><?=number_format($perc, 1)?>%</td>
                                    <td class="text-center fw-bold text-primary"><?=htmlspecialchars($r['grade'])?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td>Grand Total</td>
                                <td class="text-center"><?=$grand_obtained?></td>
                                <td class="text-center"><?=$grand_total?></td>
                                <td class="text-center"><?= ($grand_total > 0 ? number_format(($grand_obtained/$grand_total)*100, 1) : 0) ?>%</td>
                                <td class="text-center">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row mt-5">
                    <div class="col-4 text-center border-top pt-2 mt-4 mx-auto">
                        <small class="text-muted">Headmaster Signature</small>
                    </div>
                    <div class="col-4 text-center border-top pt-2 mt-4 mx-auto">
                        <small class="text-muted">Class Teacher Signature</small>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!$useTCPDF): ?>
        <div class="mt-3 alert alert-light text-center no-print">
            <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Tip: Select "Save as PDF" in the print dialog to export this card.</small>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher']);
require_once __DIR__ . '/../includes/db.php';

$pdo = getPDO();
$action = $_GET['action'] ?? $_POST['action'] ?? '';
if ($action === 'sections') {
    $class_id = intval($_GET['class_id'] ?? 0);
    $teacher_id = intval($_GET['teacher_id'] ?? 0);
    
    $query = 'SELECT * FROM sections WHERE class_id = :c';
    $params = [':c'=>$class_id];
    
    if ($_SESSION['role'] === 'teacher' && $teacher_id > 0) {
        $query .= ' AND class_teacher_id = :t';
        $params[':t'] = $teacher_id;
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($action === 'load') {
    $class_id = intval($_GET['class_id'] ?? 0);
    $section_id = intval($_GET['section_id'] ?? 0);
    $date = $_GET['date'] ?? date('Y-m-d');
    
    // Security check for teachers
    if ($_SESSION['role'] === 'teacher') {
        $stT = $pdo->prepare('SELECT t.id FROM teachers t JOIN users u ON t.full_name = u.full_name WHERE u.username = :n LIMIT 1');
        $stT->execute([':n' => $_SESSION['username']]);
        $tid = $stT->fetchColumn() ?: 0;
        
        $stV = $pdo->prepare('SELECT count(*) FROM sections WHERE id = :s AND class_teacher_id = :t');
        $stV->execute([':s'=>$section_id, ':t'=>$tid]);
        if ($stV->fetchColumn() == 0) {
            echo '<div class="alert alert-danger">Access Denied: You are not assigned to this section.</div>';
            exit;
        }
    }

    $sql = 'SELECT * FROM students WHERE class_id = :c AND status = "Active"';
    $params = [':c' => $class_id];
    if ($section_id > 0) {
        $sql .= ' AND section_id = :s';
        $params[':s'] = $section_id;
    }
    $sql .= ' ORDER BY roll_no, full_name';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $students = $stmt->fetchAll();

    // load existing attendance
    $attStmt = $pdo->prepare('SELECT * FROM student_attendance WHERE student_id = :sid AND attendance_date = :d LIMIT 1');

    ob_start();
    echo '<style>
        .att-btn-group .btn { width: 50px; height: 50px; border-radius: 12px !important; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; margin: 0 4px; border: 2px solid; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); background: white; }
        .att-btn-group .btn:active { transform: scale(0.9); }
        .att-btn-group .btn-p { color: #198754; border-color: #198754; }
        .att-btn-group .btn-p.active { background: #198754; color: white; box-shadow: 0 4px 10px rgba(25,135,84,0.3); }
        .att-btn-group .btn-a { color: #dc3545; border-color: #dc3545; }
        .att-btn-group .btn-a.active { background: #dc3545; color: white; box-shadow: 0 4px 10px rgba(220,53,69,0.3); }
        .att-btn-group .btn-l { color: #ffc107; border-color: #ffc107; }
        .att-btn-group .btn-l.active { background: #ffc107; color: white; box-shadow: 0 4px 10px rgba(255,193,7,0.3); }
        .att-btn-group .btn-t { color: #0dcaf0; border-color: #0dcaf0; }
        .att-btn-group .btn-t.active { background: #0dcaf0; color: white; box-shadow: 0 4px 10px rgba(13,202,240,0.3); }
        
        @media (max-width: 768px) {
            .att-table thead { display: none; }
            .att-table tr { display: block; margin-bottom: 20px; border: none !important; border-radius: 15px; padding: 20px; background: white; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
            .att-table td { display: block; border: none !important; padding: 0 !important; width: 100%; text-align: center; }
            .att-table td[data-label="ID"] { position: absolute; top: -10px; left: 10px; background: var(--accent-color); color: white; padding: 2px 10px !important; border-radius: 20px; font-size: 0.7rem; font-weight: bold; }
            .att-table td[data-label="Student"] { margin-bottom: 15px; border-bottom: 1px solid #f0f0f0 !important; padding-bottom: 10px !important; }
            .att-table td::before { content: none; }
            .sticky-save { position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255,255,255,0.9); backdrop-filter: blur(10px); padding: 15px; box-shadow: 0 -10px 30px rgba(0,0,0,0.08); z-index: 1000; }
            #attendanceArea { padding-bottom: 90px; }
            .attendance-card-wrapper { position: relative; }
        }
    </style>';

    echo '<form onsubmit="event.preventDefault(); saveAttendance(this);">';
    echo '<input type="hidden" name="class_id" value="'.htmlspecialchars($class_id).'">';
    echo '<input type="hidden" name="section_id" value="'.htmlspecialchars($section_id).'">';
    echo '<input type="hidden" name="date" value="'.htmlspecialchars($date).'">';
    
    echo '<div class="table-responsive" style="overflow: visible;">';
    echo '<table class="table att-table align-middle"><thead><tr class="text-uppercase small"><th width="50">#</th><th>Student Particulars</th><th class="text-center">Attendance Status</th></tr></thead><tbody>';
    
    foreach ($students as $s) {
        $attStmt->execute([':sid'=>$s['id'], ':d'=>$date]);
        $att = $attStmt->fetch();
        $status = $att['status'] ?? 'Present';
        
        echo '<tr class="attendance-card-wrapper">';
        echo '<td data-label="ID">'.htmlspecialchars($s['id']).'</td>';
        echo '<td data-label="Student">
                <div class="h5 fw-bold text-dark mb-1">'.htmlspecialchars($s['full_name']).'</div>
                <div class="small text-uppercase tracking-wider text-muted fw-bold">Roll: '.htmlspecialchars($s['roll_no']).'</div>
              </td>';
        echo '<td data-label="Mark Status" class="text-md-center">
                <div class="att-btn-group d-flex justify-content-center mt-3 mt-md-0">
                    <input type="hidden" name="status['.htmlspecialchars($s['id']).']" value="'.$status.'" id="input_'.$s['id'].'">
                    <button type="button" class="btn btn-p '.($status=='Present'?'active':'').'" onclick="setStatus('.$s['id'].', \'Present\', this)">P</button>
                    <button type="button" class="btn btn-a '.($status=='Absent'?'active':'').'" onclick="setStatus('.$s['id'].', \'Absent\', this)">A</button>
                    <button type="button" class="btn btn-l '.($status=='Leave'?'active':'').'" onclick="setStatus('.$s['id'].', \'Leave\', this)">L</button>
                    <button type="button" class="btn btn-t '.($status=='Late'?'active':'').'" onclick="setStatus('.$s['id'].', \'Late\', this)">T</button>
                </div>
              </td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '</div>'; // table-responsive
    
    echo '<div class="sticky-save text-center">';
    echo '<button class="btn btn-success btn-lg px-5 shadow"><i class="fas fa-save me-2"></i> Save All Records</button>';
    echo '</div>';
    echo '</form>';
    
    $html = ob_get_clean();
    echo $html;
    exit;
}

if ($action === 'save') {
    $class_id = intval($_POST['class_id'] ?? 0);
    $section_id = intval($_POST['section_id'] ?? 0);
    $date = $_POST['date'] ?? date('Y-m-d');
    $statuses = $_POST['status'] ?? [];
    $userId = $_SESSION['user_id'];
    
    // Security check for teachers
    if ($_SESSION['role'] === 'teacher') {
        $stT = $pdo->prepare('SELECT t.id FROM teachers t JOIN users u ON t.full_name = u.full_name WHERE u.username = :n LIMIT 1');
        $stT->execute([':n' => $_SESSION['username']]);
        $tid = $stT->fetchColumn() ?: 0;
        
        $stV = $pdo->prepare('SELECT count(*) FROM sections WHERE id = :s AND class_teacher_id = :t');
        $stV->execute([':s'=>$section_id, ':t'=>$tid]);
        if ($stV->fetchColumn() == 0) {
            echo json_encode(['success'=>false, 'error'=>'Access Denied']);
            exit;
        }
    }

    try {
        $pdo->beginTransaction();
        $ins = $pdo->prepare('INSERT INTO student_attendance (student_id, class_id, section_id, attendance_date, status, recorded_by, recorded_at) VALUES (:sid, :c, :s, :d, :st, :rb, NOW())');
        $up = $pdo->prepare('UPDATE student_attendance SET status = :st, edited_by = :eb, edited_at = NOW(), edit_reason = :reason WHERE id = :id');
        $sel = $pdo->prepare('SELECT id, status FROM student_attendance WHERE student_id = :sid AND attendance_date = :d LIMIT 1');
        $auditIns = $pdo->prepare('INSERT INTO attendance_edits (attendance_id, old_status, new_status, edited_by, edited_at, reason) VALUES (:aid, :old, :new, :by, NOW(), :reason)');
        foreach ($statuses as $sid => $st) {
            $sid = intval($sid);
            $sel->execute([':sid'=>$sid, ':d'=>$date]);
            $row = $sel->fetch();
            if ($row) {
                if ($row['status'] !== $st) {
                    $up->execute([':st'=>$st, ':eb'=>$userId, ':reason'=>'Updated via Web', ':id'=>$row['id']]);
                    // Only insert audit if table exists (handled by migration but being safe)
                    try {
                        $auditIns->execute([':aid'=>$row['id'], ':old'=>$row['status'], ':new'=>$st, ':by'=>$userId, ':reason'=>'Web Update']);
                    } catch (Exception $e) { /* Log error or ignore if audit table not ready */ }
                }
            } else {
                $ins->execute([
                    ':sid'=>$sid, 
                    ':c'=>$class_id, 
                    ':s'=>($section_id > 0 ? $section_id : null), 
                    ':d'=>$date, 
                    ':st'=>$st, 
                    ':rb'=>$userId
                ]);
            }
        }
        $pdo->commit();
        echo json_encode(['success'=>true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success'=>false, 'error'=>'Database Error: ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(400);
echo json_encode(['error'=>'Invalid action']);

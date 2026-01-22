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
    echo '<form onsubmit="event.preventDefault(); saveAttendance(this);">';
    echo '<input type="hidden" name="class_id" value="'.htmlspecialchars($class_id).'">';
    echo '<input type="hidden" name="section_id" value="'.htmlspecialchars($section_id).'">';
    echo '<input type="hidden" name="date" value="'.htmlspecialchars($date).'">';
    echo '<table class="table"><thead><tr><th>#</th><th>Name</th><th>Roll</th><th>Status</th></tr></thead><tbody>';
    foreach ($students as $s) {
        $attStmt->execute([':sid'=>$s['id'], ':d'=>$date]);
        $att = $attStmt->fetch();
        $status = $att['status'] ?? 'Present';
        echo '<tr>';
        echo '<td>'.htmlspecialchars($s['id']).'</td>';
        echo '<td>'.htmlspecialchars($s['full_name']).'</td>';
        echo '<td>'.htmlspecialchars($s['roll_no']).'</td>';
        echo '<td><select name="status['.htmlspecialchars($s['id']).']" class="form-select"><option '.($status=='Present'?'selected':'').'>Present</option><option '.($status=='Absent'?'selected':'').'>Absent</option><option '.($status=='Leave'?'selected':'').'>Leave</option><option '.($status=='Late'?'selected':'').'>Late</option></select></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<button class="btn btn-success">Save Attendance</button>';
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

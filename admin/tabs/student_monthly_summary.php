<?php
// Calculate statistics for the whole month for all students in class/section
$query = "SELECT s.id, s.full_name, s.roll_no,
          SUM(CASE WHEN sa.status = 'Present' THEN 1 ELSE 0 END) as present_count,
          SUM(CASE WHEN sa.status = 'Absent' THEN 1 ELSE 0 END) as absent_count,
          SUM(CASE WHEN sa.status = 'Leave' THEN 1 ELSE 0 END) as leave_count,
          SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) as late_count
          FROM students s
          LEFT JOIN student_attendance sa ON s.id = sa.student_id AND DATE_FORMAT(sa.attendance_date, '%Y-%m') = :month
          WHERE s.status = 'Active'";
$params = [':month' => $month];
if ($class_id) { $query .= " AND s.class_id = :cid"; $params[':cid'] = $class_id; }
if ($section_id) { $query .= " AND s.section_id = :sid"; $params[':sid'] = $section_id; }
$query .= " GROUP BY s.id ORDER BY s.roll_no";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$summaries = $stmt->fetchAll();
?>
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="ps-4">Roll</th>
                    <th>Student Name</th>
                    <th class="text-center font-monospace">P</th>
                    <th class="text-center font-monospace">A</th>
                    <th class="text-center font-monospace">L</th>
                    <th class="text-center font-monospace">Late</th>
                    <th class="text-end pe-4">Attendance %</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($summaries as $sm): 
                    $total = $sm['present_count'] + $sm['absent_count'] + $sm['leave_count'] + $sm['late_count'];
                    $perc = $total > 0 ? round(($sm['present_count'] + $sm['late_count']) / $total * 100, 1) : 0;
                ?>
                <tr>
                    <td class="ps-4"><?=$sm['roll_no']?></td>
                    <td class="fw-bold"><?=$sm['full_name']?></td>
                    <td class="text-center text-success fw-bold"><?=$sm['present_count']?></td>
                    <td class="text-center text-danger fw-bold"><?=$sm['absent_count']?></td>
                    <td class="text-center text-warning fw-bold"><?=$sm['leave_count']?></td>
                    <td class="text-center text-primary fw-bold"><?=$sm['late_count']?></td>
                    <td class="text-end pe-4">
                        <div class="progress" style="height: 10px; width: 100px; display: inline-flex;">
                            <div class="progress-bar bg-<?= $perc > 75 ? 'success' : ($perc > 50 ? 'warning' : 'danger') ?>" style="width: <?=$perc?>%"></div>
                        </div>
                        <span class="ms-2 small fw-bold"><?=$perc?>%</span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

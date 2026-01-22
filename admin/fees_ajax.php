<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$pdo = getPDO();
$class_id = intval($_GET['class_id'] ?? 0);
$institution_type = $_GET['institution_type'] ?? '';
$skip_global = intval($_GET['skip_global'] ?? 0);

if ($class_id > 0) {
    // Get all class names for smart filtering
    $all_classes = $pdo->query('SELECT name FROM classes')->fetchAll(PDO::FETCH_COLUMN);
    $stmtC = $pdo->prepare('SELECT name FROM classes WHERE id = :id');
    $stmtC->execute([':id' => $class_id]);
    $student_class_name = $stmtC->fetchColumn();

    // Fetch class-specific OR global fees
    $stmtValue = 'SELECT * FROM fees WHERE (class_id = :cid OR class_id IS NULL)';
    $params = [':cid' => $class_id];
    $stmt = $pdo->prepare($stmtValue);
    $stmt->execute($params);
    $raw_fees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filtered_fees = [];
    foreach ($raw_fees as $f) {
        // If already linked by ID, it's definitely applicable
        if (intval($f['class_id']) === $class_id) {
            $filtered_fees[] = $f;
            continue;
        }

        // Otherwise (it's Global/NULL), apply smart name-matching
        $feeName = $f['name'];
        $normFee = strtolower(preg_replace('/[^a-zA-Z0-9]/', ' ', $feeName));
        $normStudentClass = strtolower(preg_replace('/[^a-zA-Z0-9]/', ' ', $student_class_name));

        // Find the BEST (longest) class name match in the fee title
        $bestMatch = '';
        foreach ($all_classes as $cn) {
            $nc = strtolower(preg_replace('/[^a-zA-Z0-9]/', ' ', $cn));
            if (preg_match('/\b' . preg_quote($nc, '/') . '\b/', $normFee)) {
                if (strlen($nc) > strlen($bestMatch)) {
                    $bestMatch = $nc;
                }
            }
        }

        if ($bestMatch === $normStudentClass) {
            $filtered_fees[] = $f;
        } else if ($bestMatch === '') {
            // No class name mentioned at all, treat as truly global
            $filtered_fees[] = $f;
        }
    }
    
    echo json_encode($filtered_fees);
} else {
    echo json_encode([]);
}

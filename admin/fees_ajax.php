<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$pdo = getPDO();
$class_id = intval($_GET['class_id'] ?? 0);

if ($class_id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM fees WHERE class_id = :cid OR class_id IS NULL');
    $stmt->execute([':cid' => $class_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo json_encode([]);
}

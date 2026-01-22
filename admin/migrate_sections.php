<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();

try {
    $pdo->exec("ALTER TABLE sections ADD COLUMN class_teacher_id INT NULL AFTER name");
    $pdo->exec("ALTER TABLE sections ADD FOREIGN KEY (class_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL");
    echo "Migration successful: Column class_teacher_id added to sections table.";
} catch (PDOException $e) {
    echo "Migration Error: " . $e->getMessage();
}
?>

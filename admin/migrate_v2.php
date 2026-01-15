<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

$pdo = getPDO();

try {
    // 1. Create Directories
    $uploadDir = __DIR__ . '/../assets/uploads/students';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
        echo "Directory created: assets/uploads/students<br>";
    }

    // 2. Schema Changes
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS photo VARCHAR(255) NULL AFTER admission_no");
    $pdo->exec("ALTER TABLE fee_payments ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(100) NULL AFTER student_id");
    $pdo->exec("ALTER TABLE fee_payments ADD INDEX (transaction_id)");

    echo "Migration successful: Photo and Transaction Grouping added.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

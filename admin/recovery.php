<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();

try {
    echo "Starting Recovery...<br>";

    // 1. Ensure students table has all columns
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS photo VARCHAR(255) AFTER admission_no");
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS institution_type ENUM('School', 'Coaching') DEFAULT 'School' AFTER admission_date");
    
    // 2. Ensure fee_payments table has transaction_id
    $pdo->exec("ALTER TABLE fee_payments ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(100) AFTER student_id");
    $pdo->exec("ALTER TABLE fee_payments ADD INDEX IF NOT EXISTS (transaction_id)");

    // 3. Ensure classes table has institution_type
    $pdo->exec("ALTER TABLE classes ADD COLUMN IF NOT EXISTS institution_type ENUM('School', 'Coaching') DEFAULT 'School' AFTER code");

    // 4. Ensure fees table has institution_type
    $pdo->exec("ALTER TABLE fees ADD COLUMN IF NOT EXISTS institution_type ENUM('School', 'Coaching', 'Both') DEFAULT 'Both' AFTER fee_type");

    // 5. Create directories
    $upload_dir = __DIR__ . '/../assets/uploads/students/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
        echo "Created directory: $upload_dir <br>";
    }

    echo "Recovery Successful! All columns and directories are ready.";
} catch (Exception $e) {
    echo "Recovery Error: " . $e->getMessage();
}

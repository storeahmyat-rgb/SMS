<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

$pdo = getPDO();

try {
    // 1. Update Students Table
    $pdo->exec("ALTER TABLE students ADD COLUMN IF NOT EXISTS institution_type ENUM('School', 'Coaching') DEFAULT 'School' AFTER admission_date");
    $pdo->exec("UPDATE students SET institution_type = 'School' WHERE institution_type IS NULL");

    // 2. Update Classes Table
    $pdo->exec("ALTER TABLE classes ADD COLUMN IF NOT EXISTS institution_type ENUM('School', 'Coaching') DEFAULT 'School' AFTER code");
    $pdo->exec("UPDATE classes SET institution_type = 'School' WHERE institution_type IS NULL");

    // 3. Update Fees Table
    $pdo->exec("ALTER TABLE fees ADD COLUMN IF NOT EXISTS institution_type ENUM('School', 'Coaching', 'Both') DEFAULT 'Both' AFTER fee_type");
    $pdo->exec("UPDATE fees SET institution_type = 'Both' WHERE institution_type IS NULL");

    echo "Migration successful: Institution discrimination added to Students, Classes, and Fees.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

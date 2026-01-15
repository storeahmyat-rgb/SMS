<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();

function addColumn($pdo, $table, $column, $definition) {
    try {
        $pdo->query("SELECT $column FROM $table LIMIT 1");
        echo "Column $column already exists in $table.<br>";
    } catch (Exception $e) {
        $pdo->exec("ALTER TABLE $table ADD $column $definition");
        echo "Added column $column to $table.<br>";
    }
}

try {
    echo "<h2>Starting Super Recovery...</h2>";

    // 1. Students Table Fixes
    addColumn($pdo, 'students', 'photo', "VARCHAR(255) AFTER admission_no");
    addColumn($pdo, 'students', 'institution_type', "ENUM('School', 'Coaching') DEFAULT 'School' AFTER admission_date");
    addColumn($pdo, 'students', 'guardian_cnic', "VARCHAR(20) AFTER father_name");
    addColumn($pdo, 'students', 'blood_group', "VARCHAR(10) AFTER gender");
    addColumn($pdo, 'students', 'religion', "VARCHAR(50) DEFAULT 'Islam' AFTER blood_group");
    
    // 2. Fees Table Fixes
    addColumn($pdo, 'fees', 'class_id', "INT NULL AFTER id");
    addColumn($pdo, 'fees', 'section_id', "INT NULL AFTER class_id");
    addColumn($pdo, 'fees', 'institution_type', "ENUM('School', 'Coaching', 'Both') DEFAULT 'Both' AFTER fee_type");
    
    // 3. Teachers Table Fixes
    addColumn($pdo, 'teachers', 'designation', "VARCHAR(100) DEFAULT 'Faculty Member' AFTER full_name");
    addColumn($pdo, 'teachers', 'address', "TEXT AFTER contact");
    addColumn($pdo, 'teachers', 'institution_type', "ENUM('School', 'Coaching') DEFAULT 'School' AFTER status");

    // 4. Classes Table Fixes
    addColumn($pdo, 'classes', 'institution_type', "ENUM('School', 'Coaching') DEFAULT 'School' AFTER code");

    // 5. Payments Table Fixes
    addColumn($pdo, 'fee_payments', 'transaction_id', "VARCHAR(100) AFTER student_id");

    // 5. Directory Fixes (Absolute paths)
    $dirs = [
        __DIR__ . '/../assets/uploads/',
        __DIR__ . '/../assets/uploads/students/'
    ];

    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            if (mkdir($dir, 0777, true)) {
                echo "Created directory: " . htmlspecialchars($dir) . "<br>";
            } else {
                echo "FAILED to create directory: " . htmlspecialchars($dir) . ". Please create it manually.<br>";
            }
        } else {
            echo "Directory already exists: " . htmlspecialchars($dir) . "<br>";
        }
    }

    echo "<h3 style='color:green;'>All fixes applied! Now try Admission or Fee Collection.</h3>";
} catch (Exception $e) {
    echo "<h3 style='color:red;'>Recovery Failed: " . $e->getMessage() . "</h3>";
}

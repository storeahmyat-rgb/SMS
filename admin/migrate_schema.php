<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();

try {
    // Check if institution_type exists in students
    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'institution_type'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE students ADD COLUMN institution_type ENUM('School', 'Coaching') DEFAULT 'School' AFTER address");
        echo "Added 'institution_type' to students table.<br>";
    }

    // Check if institution_type exists in teachers
    $stmt = $pdo->query("SHOW COLUMNS FROM teachers LIKE 'institution_type'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE teachers ADD COLUMN institution_type ENUM('School', 'Coaching') DEFAULT 'School' AFTER joining_date");
        echo "Added 'institution_type' to teachers table.<br>";
    }

    // Check if designation exists in teachers
    $stmt = $pdo->query("SHOW COLUMNS FROM teachers LIKE 'designation'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE teachers ADD COLUMN designation VARCHAR(100) DEFAULT 'Faculty Member' AFTER institution_type");
        echo "Added 'designation' to teachers table.<br>";
    }

    echo "Schema migration complete!";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}

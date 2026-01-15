<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = getPDO();
    // Create attendance_edits table
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance_edits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        attendance_id INT NOT NULL,
        old_status ENUM('Present','Absent','Leave','Late'),
        new_status ENUM('Present','Absent','Leave','Late'),
        edited_by INT,
        edited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        reason VARCHAR(255),
        FOREIGN KEY (attendance_id) REFERENCES student_attendance(id) ON DELETE CASCADE
    )");

    echo "Database patch applied: 'attendance_edits' table ensured.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
<br><a href="attendance.php">Back to Attendance</a>

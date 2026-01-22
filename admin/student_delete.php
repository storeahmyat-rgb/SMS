<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo = getPDO();
        
        // Transaction to ensure data integrity
        $pdo->beginTransaction();
        
        // Delete related attendance
        $stmt = $pdo->prepare('DELETE FROM student_attendance WHERE student_id = ?');
        $stmt->execute([$id]);
        
        // Delete related fees
        $stmt = $pdo->prepare('DELETE FROM fee_payments WHERE student_id = ?');
        $stmt->execute([$id]);
        
        // Finally delete student
        $stmt = $pdo->prepare('DELETE FROM students WHERE id = ?');
        $stmt->execute([$id]);
        
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
    }
}

header('Location: ' . BASE_URL . 'admin/students.php');
exit;

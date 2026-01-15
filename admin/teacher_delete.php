<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo = getPDO();
        $pdo->beginTransaction();
        
        // Delete related attendance
        $stmt = $pdo->prepare('DELETE FROM teacher_attendance WHERE teacher_id = ?');
        $stmt->execute([$id]);
        
        // Delete related salaries
        $stmt = $pdo->prepare('DELETE FROM salaries WHERE teacher_id = ?');
        $stmt->execute([$id]);
        
        // Delete teacher
        $stmt = $pdo->prepare('DELETE FROM teachers WHERE id = ?');
        $stmt->execute([$id]);
        
        $pdo->commit();
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
    }
}

header('Location: ' . BASE_URL . 'admin/teachers.php');
exit;

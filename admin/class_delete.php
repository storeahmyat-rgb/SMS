<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo = getPDO();
        
        // Check if class has sections
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM sections WHERE class_id = ?');
        $stmt->execute([$id]);
        $sectionCount = $stmt->fetchColumn();
        
        // Check if class has students
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE class_id = ?');
        $stmt->execute([$id]);
        $studentCount = $stmt->fetchColumn();
        
        if ($sectionCount > 0 || $studentCount > 0) {
            $msg = "Cannot delete this class. It has $sectionCount section(s) and $studentCount student(s).";
            header('Location: ' . BASE_URL . 'admin/classes.php?error=' . urlencode($msg));
            exit;
        }
        
        // Safe to delete
        $stmt = $pdo->prepare('DELETE FROM classes WHERE id = ?');
        $stmt->execute([$id]);
        
        header('Location: ' . BASE_URL . 'admin/classes.php?msg=Class deleted successfully');
        exit;
        
    } catch (PDOException $e) {
        header('Location: ' . BASE_URL . 'admin/classes.php?error=' . urlencode('Error: ' . $e->getMessage()));
        exit;
    }
}

header('Location: ' . BASE_URL . 'admin/classes.php');
exit;

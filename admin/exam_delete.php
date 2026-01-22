<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo = getPDO();
        
        // Check if exam has marks entered
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM exam_marks WHERE exam_id = ?');
        $stmt->execute([$id]);
        $marksCount = $stmt->fetchColumn();
        
        if ($marksCount > 0) {
            $msg = "Cannot delete this exam. It has $marksCount mark entries.";
            header('Location: ' . BASE_URL . 'admin/exams.php?error=' . urlencode($msg));
            exit;
        }
        
        // Safe to delete
        $stmt = $pdo->prepare('DELETE FROM exams WHERE id = ?');
        $stmt->execute([$id]);
        
        header('Location: ' . BASE_URL . 'admin/exams.php?msg=Exam deleted successfully');
        exit;
        
    } catch (PDOException $e) {
        header('Location: ' . BASE_URL . 'admin/exams.php?error=' . urlencode('Error: ' . $e->getMessage()));
        exit;
    }
}

header('Location: ' . BASE_URL . 'admin/exams.php');
exit;

<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo = getPDO();
        
        // Check if section has students
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE section_id = ?');
        $stmt->execute([$id]);
        $studentCount = $stmt->fetchColumn();
        
        if ($studentCount > 0) {
            $msg = "Cannot delete this section. It has $studentCount student(s) enrolled.";
            header('Location: ' . BASE_URL . 'admin/sections.php?error=' . urlencode($msg));
            exit;
        }
        
        // Safe to delete
        $stmt = $pdo->prepare('DELETE FROM sections WHERE id = ?');
        $stmt->execute([$id]);
        
        header('Location: ' . BASE_URL . 'admin/sections.php?msg=Section deleted successfully');
        exit;
        
    } catch (PDOException $e) {
        header('Location: ' . BASE_URL . 'admin/sections.php?error=' . urlencode('Error: ' . $e->getMessage()));
        exit;
    }
}

header('Location: ' . BASE_URL . 'admin/sections.php');
exit;

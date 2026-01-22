<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $pdo = getPDO();
        
        // Check if subject is used in timetable
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM timetable WHERE subject_id = ?');
        $stmt->execute([$id]);
        $timetableCount = $stmt->fetchColumn();
        
        if ($timetableCount > 0) {
            $msg = "Cannot delete this subject. It is used in $timetableCount timetable entries.";
            header('Location: ' . BASE_URL . 'admin/subjects.php?error=' . urlencode($msg));
            exit;
        }
        
        // Safe to delete
        $stmt = $pdo->prepare('DELETE FROM subjects WHERE id = ?');
        $stmt->execute([$id]);
        
        header('Location: ' . BASE_URL . 'admin/subjects.php?msg=Subject deleted successfully');
        exit;
        
    } catch (PDOException $e) {
        header('Location: ' . BASE_URL . 'admin/subjects.php?error=' . urlencode('Error: ' . $e->getMessage()));
        exit;
    }
}

header('Location: ' . BASE_URL . 'admin/subjects.php');
exit;

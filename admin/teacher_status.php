<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

$id = intval($_GET['id'] ?? 0);
$status = $_GET['status'] ?? 'Active';

if ($id > 0) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE teachers SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    } catch (PDOException $e) {
        // Handle error silently
    }
}

header('Location: ' . BASE_URL . 'admin/teachers.php');
exit;

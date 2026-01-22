<?php
require_once __DIR__ . '/../includes/db.php';
try {
    $pdo = getPDO();
    echo "Connection successful.<br>";
    $stmt = $pdo->query("SELECT COUNT(*) FROM students");
    echo "Student count: " . $stmt->fetchColumn() . "<br>";
    
    // Test write
    $pdo->exec("CREATE TABLE IF NOT EXISTS crud_test (id INT AUTO_INCREMENT PRIMARY KEY, val VARCHAR(10))");
    $pdo->exec("INSERT INTO crud_test (val) VALUES ('test')");
    echo "Write test successful.<br>";
    $pdo->exec("DROP TABLE crud_test");
    echo "Cleanup successful.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

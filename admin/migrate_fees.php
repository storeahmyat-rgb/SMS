<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';

try {
    $pdo = getPDO();
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM fees LIKE 'class_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE fees ADD COLUMN class_id INT NULL AFTER description");
        $pdo->exec("ALTER TABLE fees ADD CONSTRAINT fk_fee_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL");
        echo "Database updated successfully. 'class_id' added to 'fees' table.";
    } else {
        echo "Column 'class_id' already exists.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
<br><a href="fees.php">Back to Fees</a>

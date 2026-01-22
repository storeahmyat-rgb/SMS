<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getPDO();
try {
    $pdo->exec("ALTER TABLE salaries 
        ADD COLUMN payment_method ENUM('Cash', 'Bank Transfer', 'Cheque') DEFAULT 'Cash' AFTER paid_status,
        ADD COLUMN total_payout DECIMAL(10,2) NULL AFTER amount,
        ADD COLUMN bonus_deduction DECIMAL(10,2) DEFAULT 0.00 AFTER total_payout,
        ADD COLUMN payment_notes TEXT NULL AFTER paid_on");
    
    // Update existing paid salaries to have total_payout = amount
    $pdo->exec("UPDATE salaries SET total_payout = amount WHERE total_payout IS NULL");
    
    echo "Salaries table enhanced successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

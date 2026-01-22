<?php
require_once __DIR__ . '/includes/db.php';
$pdo = getPDO();
$tables = ['salaries', 'expenses'];
foreach($tables as $t) {
    echo "\n--- $t ---\n";
    try {
        $stmt = $pdo->query("DESCRIBE $t");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    } catch (Exception $e) {
        echo "Error describing $t: " . $e->getMessage() . "\n";
    }
}
?>

<?php
// Run this once after configuring includes/config.php to create DB schema and default admin.
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';

$sqlFile = __DIR__ . '/sql/schema.sql';
if (!file_exists($sqlFile)) {
    echo "Missing schema file: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
$pdo = null;
try {
    // Connect to MySQL server without selecting a database first
    $rootDsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
    $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Create database if not exists
    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    
    // Now connect with getPDO() which selects the database
    $pdo = getPDO();
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)), function($s) { return !empty($s); });
    
    $pdo->beginTransaction();
    
    // Execute each statement separately
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            $pdo->exec($statement . ';');
        }
    }

    // Create default super admin
    $username = 'admin';
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, role, full_name, created_at) VALUES (:u, :h, :r, :n, NOW())');
    $stmt->execute([':u'=>$username, ':h'=>$hash, ':r'=>'super_admin', ':n'=>'Default Admin']);

    $pdo->commit();

    echo "<h2 style='color:green;'>✅ Setup Successful!</h2>";
    echo "<p><strong>Schema created and default admin user created!</strong></p>";
    echo "<p><strong>Login Credentials:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Username:</strong> $username</li>";
    echo "<li><strong>Password:</strong> $password</li>";
    echo "</ul>";
    echo "<p style='color:red;'><strong>⚠️ Please change the password after first login!</strong></p>";
    echo "<p><a href='index.php' style='padding:10px; background:blue; color:white; text-decoration:none;'>Go to Login Page</a></p>";
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    echo "<h2 style='color:red;'>❌ Setup failed</h2>";
    echo "<p style='color:red;'><strong>Error: " . htmlspecialchars($e->getMessage()) . "</strong></p>";
    echo "<p>Make sure:</p>";
    echo "<ul>";
    echo "<li>MySQL is running</li>";
    echo "<li>config.php has correct database credentials (username: root, password: empty)</li>";
    echo "<li>Database doesn't already exist (or delete it first)</li>";
    echo "</ul>";
    exit(1);
}

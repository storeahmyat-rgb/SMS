<?php
require_once __DIR__ . '/../includes/db.php';
$pdo = getPDO();

try {
    // Create a test teacher in teachers table if not exists
    $checkTeacher = $pdo->query("SELECT id, full_name FROM teachers WHERE full_name = 'Test Teacher' LIMIT 1")->fetch();
    
    if (!$checkTeacher) {
        $pdo->exec("INSERT INTO teachers (teacher_id, full_name, cnic, qualification, contact, salary, joining_date, status) 
                    VALUES ('TCH-TEST', 'Test Teacher', '12345-1234567-1', 'M.Sc Education', '03001234567', 50000, CURDATE(), 'Active')");
        $teacher_id = $pdo->lastInsertId();
        echo "✓ Teacher record created (ID: $teacher_id)<br>";
    } else {
        $teacher_id = $checkTeacher['id'];
        echo "✓ Teacher already exists (ID: $teacher_id)<br>";
    }
    
    // Create user account with username: teacher, password: teacher123
    $checkUser = $pdo->query("SELECT id FROM users WHERE username = 'teacher' LIMIT 1")->fetch();
    
    if (!$checkUser) {
        $password_hash = password_hash('teacher123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password_hash, role, full_name, email) 
                       VALUES (:u, :p, 'teacher', 'Test Teacher', 'teacher@school.edu')")
            ->execute([':u' => 'teacher', ':p' => $password_hash]);
        echo "✓ User account created<br>";
    } else {
        echo "✓ User account already exists<br>";
    }
    
    // Assign teacher to first available section
    $section = $pdo->query("SELECT * FROM sections ORDER BY id LIMIT 1")->fetch();
    if ($section) {
        $pdo->prepare("UPDATE sections SET class_teacher_id = :t WHERE id = :s")
            ->execute([':t' => $teacher_id, ':s' => $section['id']]);
        echo "✓ Teacher assigned to Section ID: {$section['id']}<br>";
    }
    
    echo "<hr>";
    echo "<h3>✅ Setup Complete!</h3>";
    echo "<p><strong>Login Credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>teacher</code></li>";
    echo "<li>Password: <code>teacher123</code></li>";
    echo "</ul>";
    echo "<p>Teacher has been assigned to section: <strong>{$section['name']}</strong></p>";
    echo "<p><a href='../index.php'>Go to Login Page</a></p>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

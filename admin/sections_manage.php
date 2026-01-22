<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section_name'])) {
    $stmt = $pdo->prepare('INSERT INTO sections (class_id, name, class_teacher_id, created_at) VALUES (:c, :n, :t, NOW())');
    $stmt->execute([
        ':c' => $_POST['class_id'], 
        ':n' => $_POST['section_name'],
        ':t' => $_POST['class_teacher_id'] ?: null
    ]);
    $msg = 'Section created';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_teacher'])) {
    $teacher_id = $_POST['class_teacher_id'] ?: null;
    
    // Update section assignment
    $stmt = $pdo->prepare('UPDATE sections SET class_teacher_id = :t WHERE id = :id');
    $stmt->execute([
        ':t' => $teacher_id,
        ':id' => $_POST['section_id']
    ]);
    
    // Auto-create user account for teacher if assigned and doesn't exist
    if ($teacher_id) {
        $teacherData = $pdo->prepare('SELECT full_name FROM teachers WHERE id = :id');
        $teacherData->execute([':id' => $teacher_id]);
        $teacher = $teacherData->fetch();
        
        if ($teacher) {
            // Generate username from teacher name (lowercase, no spaces)
            $username = strtolower(str_replace(' ', '', $teacher['full_name']));
            
            // Check if user account exists
            $checkUser = $pdo->prepare('SELECT id FROM users WHERE username = :u');
            $checkUser->execute([':u' => $username]);
            
            if (!$checkUser->fetch()) {
                // Create user account with default password 'teacher123'
                $password_hash = password_hash('teacher123', PASSWORD_DEFAULT);
                $pdo->prepare('INSERT INTO users (username, password_hash, role, full_name) VALUES (:u, :p, "teacher", :n)')
                    ->execute([':u' => $username, ':p' => $password_hash, ':n' => $teacher['full_name']]);
                $msg = 'Teacher assigned & login account created!';
            } else {
                $msg = 'Teacher assignment updated';
            }
        }
    } else {
        $msg = 'Teacher unassigned from section';
    }
}

$classes = $pdo->query('SELECT * FROM classes')->fetchAll();
$teachers = $pdo->query('SELECT id, full_name FROM teachers WHERE status="Active"')->fetchAll();
$sections = $pdo->query('SELECT s.*, c.name AS class_name, t.full_name AS teacher_name, t.id as teacher_id FROM sections s 
                          LEFT JOIN classes c ON s.class_id=c.id 
                          LEFT JOIN teachers t ON s.class_teacher_id=t.id 
                          ORDER BY c.name, s.name')->fetchAll();

// Get teacher usernames for display
$teacher_usernames = [];
foreach ($sections as $sec) {
    if ($sec['teacher_id']) {
        $username = strtolower(str_replace(' ', '', $sec['teacher_name']));
        $teacher_usernames[$sec['teacher_id']] = $username;
    }
}

?>
<div class="row align-items-center mb-4">
    <div class="col">
        <h1>Class Divisions</h1>
        <p class="text-muted mb-0">Organize students into specific sections per academic level.</p>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sectionModal">
            <i class="fas fa-plus-circle me-1"></i> Add New Section
        </button>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-info border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-1"></i> <?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<div class="card h-100">
    <div class="card-header bg-white"><i class="fas fa-columns me-2"></i>Active Section Mapping</div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Academic Class</th>
                    <th>Section Label</th>
                    <th>Class Teacher</th>
                    <th>Login Credentials</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sections as $s): ?>
                <tr>
                    <td class="ps-4"><?=htmlspecialchars($s['id'])?></td>
                    <td><?=htmlspecialchars($s['class_name'])?></td>
                    <td class="fw-bold text-primary"><?=htmlspecialchars($s['name'])?></td>
                    <td>
                        <form method="post" class="d-flex gap-2">
                            <input type="hidden" name="section_id" value="<?=$s['id']?>">
                            <select name="class_teacher_id" class="form-select form-select-sm" style="max-width: 200px;">
                                <option value="">-- Unassigned --</option>
                                <?php foreach($teachers as $t): ?>
                                    <option value="<?=$t['id']?>" <?=$s['class_teacher_id']==$t['id']?'selected':''?>><?=htmlspecialchars($t['full_name'])?></option>
                                <?php endforeach; ?>
                            </select>
                            <button name="update_teacher" class="btn btn-sm btn-outline-primary" title="Update Assignment">
                                <i class="fas fa-save"></i>
                            </button>
                        </form>
                    </td>
                    <td>
                        <?php if($s['teacher_id'] && isset($teacher_usernames[$s['teacher_id']])): ?>
                            <div class="small">
                                <div class="badge bg-success mb-1">Active Account</div>
                                <div><strong>User:</strong> <code><?=$teacher_usernames[$s['teacher_id']]?></code></div>
                                <div class="text-muted">Pass: <code>teacher123</code></div>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">No teacher assigned</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end pe-4">
                        <small class="text-muted">ID: <?=$s['id']?></small>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="sectionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-lg">
      <form method="post">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title font-heading text-white">Assign New Section</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Select Class</label>
                <select class="form-select" name="class_id" required>
                    <option value="">-- Choose Class --</option>
                    <?php foreach ($classes as $c): ?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Section Name</label>
                <input class="form-control" name="section_name" placeholder="e.g. Blue, A, Beta" required>
            </div>
            <div class="mb-0">
                <label class="form-label fw-bold">Assign Class Teacher (Optional)</label>
                <select class="form-select" name="class_teacher_id">
                    <option value="">-- No Teacher Assigned --</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?=$t['id']?>"><?=htmlspecialchars($t['full_name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-primary px-4"><i class="fas fa-save me-1"></i> Register Section</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

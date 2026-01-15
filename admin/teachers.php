<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$stmt = $pdo->query('SELECT * FROM teachers ORDER BY id DESC LIMIT 200');
$teachers = $stmt->fetchAll();

?>
<div class="row align-items-center mb-4">
    <div class="col">
        <h1>Teacher Directory</h1>
        <p class="text-muted mb-0">Management of faculty members and their employment status.</p>
    </div>
    <div class="col-auto">
        <a class="btn btn-primary" href="<?=BASE_URL?>admin/teacher_add.php">
            <i class="fas fa-chalkboard-teacher me-1"></i> Add New Teacher
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users-cog me-2"></i>Active Faculty List</span>
        <span class="badge bg-primary">Showing last 200</span>
    </div>
    <div class="card-body p-0">
        <table class="table mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Teacher ID</th>
                    <th>Full Name</th>
                    <th>Designation</th>
                    <th>CNIC</th>
                    <th>Qualification</th>
                    <th>Salary</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($teachers as $t): ?>
                <tr>
                    <td class="ps-4 fw-bold text-primary"><?=htmlspecialchars($t['teacher_id'])?></td>
                    <td><?=htmlspecialchars($t['full_name'])?></td>
                    <td class="small text-muted italic"><?=htmlspecialchars($t['designation'] ?? 'Faculty Member')?></td>
                    <td><?=htmlspecialchars($t['cnic'])?></td>
                    <td><?=htmlspecialchars($t['qualification'])?></td>
                    <td class="fw-bold">Rs. <?=number_format($t['salary'], 0)?></td>
                    <td>
                        <span class="badge <?=($t['status']=='Active'?'bg-success':'bg-danger')?>">
                            <?=htmlspecialchars($t['status'])?>
                        </span>
                    </td>
                    <td class="text-end pe-4">
                        <div class="btn-group">
                            <a href="<?=BASE_URL?>admin/teacher_edit.php?id=<?=$t['id']?>" class="btn btn-sm btn-outline-primary" title="Edit Profile">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?=BASE_URL?>admin/teacher_status.php?id=<?=$t['id']?>&status=<?=($t['status']=='Active'?'Left':'Active')?>" 
                               class="btn btn-sm <?=($t['status']=='Active'?'btn-outline-secondary':'btn-outline-success')?>" 
                               title="Toggle Status (Active/Left)"
                               onclick="return confirm('Change faculty status to <?=($t['status']=='Active'?'Left':'Active')?>?')">
                                <i class="fas <?=($t['status']=='Active'?'fa-user-slash':'fa-user-check')?>"></i>
                            </a>
                            <a href="<?=BASE_URL?>admin/teacher_delete.php?id=<?=$t['id']?>" class="btn btn-sm btn-outline-danger" 
                               title="Permanent Delete"
                               onclick="return confirm('ARE YOU SURE? This will remove the teacher and their attendance records!')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

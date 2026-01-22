<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-dark text-white">
                <tr class="small text-uppercase">
                    <th class="ps-4">Teacher ID</th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th class="text-center">Status</th>
                    <th>Log-In</th>
                    <th>Log-Out</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($attendance as $a): ?>
                <tr>
                    <td class="ps-4 text-muted"><?=$a['teacher_id']?></td>
                    <td class="fw-bold"><?=$a['full_name']?></td>
                    <td><?=$a['designation']?></td>
                    <td class="text-center">
                        <?php if(!$a['status']): ?>
                            <span class="badge bg-light text-dark border">Pending</span>
                        <?php else: ?>
                            <span class="badge bg-<?=$a['status']=='Present'?'success':($a['status']=='Absent'?'danger':'warning')?>"><?=$a['status']?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-success"><?= $a['in_time'] ? date('h:i A', strtotime($a['in_time'])) : '--:--' ?></td>
                    <td class="text-danger"><?= $a['out_time'] ? date('h:i A', strtotime($a['out_time'])) : '--:--' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

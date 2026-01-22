<?php
// Summary Stats
$stats = ['Present'=>0, 'Absent'=>0, 'Leave'=>0, 'Late'=>0, 'Pending'=>0];
foreach($attendance as $a) { $stats[$a['status'] ?: 'Pending']++; }
?>
<div class="row mb-4">
    <?php foreach(['Present'=>'success', 'Absent'=>'danger', 'Leave'=>'warning', 'Late'=>'primary', 'Pending'=>'secondary'] as $label=>$color): ?>
    <div class="col">
        <div class="stats-card-premium bg-white border-start border-4 border-<?=$color?>">
            <span class="label text-muted small d-block"><?=$label?></span>
            <span class="value h4 mb-0 text-<?=$color?> fw-bold"><?=$stats[$label]?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-uppercase">
                    <th class="ps-4">Roll</th>
                    <th>Student</th>
                    <th>Class/Section</th>
                    <th class="text-center">Status</th>
                    <th>In/Out Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($attendance as $a): ?>
                <tr>
                    <td class="ps-4 text-muted"><?=$a['roll_no'] ?: '-'?></td>
                    <td>
                        <div class="fw-bold"><?=$a['full_name']?></div>
                        <div class="small text-muted"><?=$a['admission_no']?></div>
                    </td>
                    <td><?=$a['class_name']?> (<?=$a['section_name'] ?: 'A'?>)</td>
                    <td class="text-center">
                        <?php if(!$a['status']): ?>
                            <span class="badge bg-light text-dark border">Pending</span>
                        <?php else: ?>
                            <span class="badge bg-<?=$a['status']=='Present'?'success':($a['status']=='Absent'?'danger':'warning')?>"><?=$a['status']?></span>
                        <?php endif; ?>
                    </td>
                    <td class="small">
                        <?php if($a['in_time']): ?>
                            <span class="text-success"><i class="fas fa-sign-in-alt me-1"></i><?=date('h:i A', strtotime($a['in_time']))?></span>
                            <?php if($a['out_time']): ?>
                                <span class="text-danger ms-2"><i class="fas fa-sign-out-alt me-1"></i><?=date('h:i A', strtotime($a['out_time']))?></span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted italic">Manual Entry</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

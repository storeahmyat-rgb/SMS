<?php if($member_meta): ?>
    <div class="card shadow-sm border-0 mb-4 bg-gradient-light border-start border-5 border-primary">
        <div class="card-body d-flex align-items-center gap-4">
            <div class="avatar bg-primary text-white rounded-circle"><i class="fas fa-id-badge fa-lg"></i></div>
            <div>
                <h4 class="mb-1 text-primary fw-bold"><?=$member_meta['full_name']?></h4>
                <div class="text-muted">
                    <?php if($active_tab == 'student'): ?>
                        Class: <?=$member_meta['class_name']?> | Admission: <?=$member_meta['admission_no']?>
                    <?php else: ?>
                        Designation: <?=$member_meta['designation']?> | ID: <?=$member_meta['teacher_id']?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th class="no-print text-end">Record Info</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ledger_data as $l): ?>
                    <tr>
                        <td class="fw-bold"><?=date('d M Y, D', strtotime($l['attendance_date']))?></td>
                        <td>
                            <span class="badge bg-<?=$l['status']=='Present'?'success':($l['status']=='Absent'?'danger':'warning')?>"><?=$l['status']?></span>
                        </td>
                        <td class="text-success"><?= $l['in_time'] ? date('h:i A', strtotime($l['in_time'])) : '--:--' ?></td>
                        <td class="text-danger"><?= $l['out_time'] ? date('h:i A', strtotime($l['out_time'])) : '--:--' ?></td>
                        <td class="small text-muted text-end no-print italic">
                            Recorded at <?=date('d/m/y h:i A', strtotime($l['recorded_at']))?>
                        </td>
                    </tr>
                    <?php endforeach; if(empty($ledger_data)) echo '<tr><td colspan="5" class="text-center p-5 text-muted">No attendance logs found for this period.</td></tr>'; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="text-center p-5 text-muted bg-white rounded shadow-sm border-dashed">
        <i class="fas fa-search-plus fa-3x mb-3 opacity-25"></i>
        <h5>Select a member to view their detailed history.</h5>
    </div>
<?php endif; ?>

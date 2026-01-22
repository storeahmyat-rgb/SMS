<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-bordered table-grid mb-0">
            <thead class="bg-dark text-white">
                <tr>
                    <th rowspan="2" class="align-middle">Student</th>
                    <?php 
                    $curr = new DateTime($weekRange['start']);
                    for($i=0; $i<7; $i++): ?>
                        <th><?=$curr->format('D')?><br><small><?=$curr->format('d/m')?></small></th>
                    <?php $curr->modify('+1 day'); endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $s): ?>
                <tr>
                    <td class="text-start ps-3">
                        <div class="fw-bold"><?=$s['full_name']?></div>
                        <div class="small text-muted"><?=$s['class_name']?></div>
                    </td>
                    <?php 
                    $curr = new DateTime($weekRange['start']);
                    for($i=0; $i<7; $i++): 
                        $d = $curr->format('Y-m-d');
                        $st = $att_data[$s['id']][$d] ?? null;
                    ?>
                        <td class="align-middle">
                            <?php if($st): ?>
                                <span class="badge bg-<?=$st=='Present'?'success':($st=='Absent'?'danger':'warning')?>"><?=$st[0]?></span>
                            <?php else: ?>
                                <span class="text-muted opacity-25">-</span>
                            <?php endif; ?>
                        </td>
                    <?php $curr->modify('+1 day'); endfor; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 small text-muted">
    <span class="badge bg-success">P</span> Present | <span class="badge bg-danger">A</span> Absent | <span class="badge bg-warning">L</span> Leave
</div>

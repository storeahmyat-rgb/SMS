<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-bordered table-grid mb-0">
            <thead class="bg-primary text-white">
                <tr>
                    <th rowspan="2" class="align-middle">Faculty Member</th>
                    <?php 
                    $curr = new DateTime($weekRange['start']);
                    for($i=0; $i<7; $i++): ?>
                        <th><?=$curr->format('D')?><br><small><?=$curr->format('d/m')?></small></th>
                    <?php $curr->modify('+1 day'); endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($faculty as $f): ?>
                <tr>
                    <td class="text-start ps-3">
                        <div class="fw-bold"><?=$f['full_name']?></div>
                        <div class="small text-muted"><?=$f['teacher_id']?></div>
                    </td>
                    <?php 
                    $curr = new DateTime($weekRange['start']);
                    for($i=0; $i<7; $i++): 
                        $d = $curr->format('Y-m-d');
                        $st = $att_data[$f['id']][$d] ?? null;
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

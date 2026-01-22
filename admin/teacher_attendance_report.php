<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$teachers = $pdo->query('SELECT id, full_name, designation FROM teachers WHERE status="Active"')->fetchAll();

$selected_teacher = intval($_GET['teacher_id'] ?? 0);
$view_type = $_GET['view_type'] ?? 'monthly';
$selected_date = $_GET['date'] ?? date('Y-m-d');
$selected_month = $_GET['month'] ?? date('Y-m');
$selected_year = $_GET['year'] ?? date('Y');

$date_condition = "";
$params = [':tid' => $selected_teacher];

if ($selected_teacher) {
    if ($view_type === 'daily') {
        $date_condition = "AND attendance_date = :d";
        $params[':d'] = $selected_date;
        $title_suffix = "for " . date('d M Y', strtotime($selected_date));
    } elseif ($view_type === 'weekly') {
        $week_start = date('Y-m-d', strtotime('monday this week', strtotime($selected_date)));
        $week_end = date('Y-m-d', strtotime('sunday this week', strtotime($selected_date)));
        $date_condition = "AND attendance_date BETWEEN :s AND :e";
        $params[':s'] = $week_start;
        $params[':e'] = $week_end;
        $title_suffix = "Week (" . date('d M', strtotime($week_start)) . " - " . date('d M Y', strtotime($week_end)) . ")";
    } elseif ($view_type === 'yearly') {
        $date_condition = "AND DATE_FORMAT(attendance_date, '%Y') = :y";
        $params[':y'] = $selected_year;
        $title_suffix = "for Year " . $selected_year;
    } else { // monthly (default)
        $date_condition = "AND DATE_FORMAT(attendance_date, '%Y-%m') = :m";
        $params[':m'] = $selected_month;
        $title_suffix = "for " . date('F Y', strtotime($selected_month . '-01'));
    }

    $teacher = $pdo->prepare('SELECT * FROM teachers WHERE id = :id');
    $teacher->execute([':id'=>$selected_teacher]);
    $teacher = $teacher->fetch();

    $stmt = $pdo->prepare("SELECT * FROM teacher_attendance WHERE teacher_id = :tid $date_condition ORDER BY attendance_date DESC");
    $stmt->execute($params);
    $attendance = $stmt->fetchAll();

    $summary = ['Present'=>0, 'Absent'=>0, 'Leave'=>0, 'Late'=>0];
    foreach ($attendance as $a) {
        $summary[$a['status']]++;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Teacher Attendance Report</h1>
    <?php if ($selected_teacher): ?>
    <button onclick="window.print()" class="btn btn-outline-primary no-print">
        <i class="fas fa-print me-1"></i> Print Report
    </button>
    <?php endif; ?>
</div>

<div class="card p-4 mb-4 no-print">
    <form method="GET" class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Search Teacher</label>
            <select name="teacher_id" id="teacher_select" class="form-select" required>
                <option value="">-- Select Teacher --</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?=$t['id']?>" <?=$t['id']==$selected_teacher?'selected':''?>>
                        <?=htmlspecialchars($t['full_name'] . ' (' . $t['designation'] . ')')?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Report Type</label>
            <select name="view_type" id="view_type" class="form-select">
                <option value="daily" <?=$view_type=='daily'?'selected':''?>>Daily</option>
                <option value="weekly" <?=$view_type=='weekly'?'selected':''?>>Weekly</option>
                <option value="monthly" <?=$view_type=='monthly'?'selected':''?>>Monthly</option>
                <option value="yearly" <?=$view_type=='yearly'?'selected':''?>>Yearly</option>
            </select>
        </div>
        
        <div class="col-md-3 filter-input" id="daily_filter" style="display: <?=$view_type=='daily'||$view_type=='weekly'?'block':'none'?>;">
            <label class="form-label">Select Date</label>
            <input type="date" name="date" class="form-control" value="<?=htmlspecialchars($selected_date)?>">
        </div>
        <div class="col-md-3 filter-input" id="monthly_filter" style="display: <?=$view_type=='monthly'?'block':'none'?>;">
            <label class="form-label">Select Month</label>
            <input type="month" name="month" class="form-control" value="<?=htmlspecialchars($selected_month)?>">
        </div>
        <div class="col-md-3 filter-input" id="yearly_filter" style="display: <?=$view_type=='yearly'?'block':'none'?>;">
            <label class="form-label">Select Year</label>
            <select name="year" class="form-select">
                <?php for($y=date('Y'); $y>=2020; $y--): ?>
                    <option value="<?=$y?>" <?=$y==$selected_year?'selected':''?>><?=$y?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Load Report</button>
        </div>
    </form>
</div>

<?php if ($selected_teacher): ?>
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-light p-3 rounded-circle">
                <i class="fas fa-chalkboard-teacher fa-2x text-primary"></i>
            </div>
            <div>
                <h4 class="mb-0"><?=htmlspecialchars($teacher['full_name'])?></h4>
                <p class="text-muted mb-0">Designation: <?=htmlspecialchars($teacher['designation'] ?? 'Faculty Member')?> | ID: <?=htmlspecialchars($teacher['teacher_id'] ?: $teacher['id'])?></p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card bg-gradient-success">
            <div class="d-flex justify-content-between">
                <div>
                   <h3><?=$summary['Present']?></h3>
                   <p class="mb-0">Days Present</p>
                </div>
                <i class="fas fa-check-circle fa-2x opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-danger">
            <div class="d-flex justify-content-between">
                <div>
                   <h3><?=$summary['Absent']?></h3>
                   <p class="mb-0">Days Absent</p>
                </div>
                <i class="fas fa-times-circle fa-2x opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-warning">
            <div class="d-flex justify-content-between">
                <div>
                   <h3><?=$summary['Leave']?></h3>
                   <p class="mb-0">On Leave</p>
                </div>
                <i class="fas fa-calendar-minus fa-2x opacity-25"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card bg-gradient-primary">
            <div class="d-flex justify-content-between">
                <div>
                   <h3><?=$summary['Late']?></h3>
                   <p class="mb-0">Late Arrival</p>
                </div>
                <i class="fas fa-clock fa-2x opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span><i class="fas fa-calendar-alt me-2 text-primary"></i>Detailed Records (<?=$title_suffix?>)</span>
        <span class="badge bg-light text-dark">Total Records: <?=count($attendance)?></span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($attendance)): ?>
                <tr><td colspan="5" class="text-center p-5 text-muted">No attendance records found for this period.</td></tr>
            <?php endif; ?>
            <?php foreach ($attendance as $a): ?>
                <tr>
                    <td class="ps-4"><?=date('d M Y, D', strtotime($a['attendance_date']))?></td>
                    <td class="fw-bold"><?=htmlspecialchars($a['in_time'] ?: '-')?></td>
                    <td class="fw-bold"><?=htmlspecialchars($a['out_time'] ?: '-')?></td>
                    <td class="small text-muted italic">
                        <?php 
                        if ($a['in_time'] && $a['out_time']) {
                            $start = new DateTime($a['in_time']);
                            $end = new DateTime($a['out_time']);
                            $diff = $start->diff($end);
                            echo $diff->format('%h hrs %i min');
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td>
                        <span class="badge <?php 
                            echo ($a['status']=='Present' ? 'bg-success' : 
                                 ($a['status']=='Absent' ? 'bg-danger' : 
                                 ($a['status']=='Late' ? 'bg-primary' : 'bg-warning'))); 
                        ?>">
                            <?=htmlspecialchars($a['status'])?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<style>
@media print {
    .sidebar, .top-navbar, .no-print { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
    body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .table thead th { background-color: #f8f9fa !important; }
}
.bg-gradient-success { background: linear-gradient(45deg, #1cc88a 0%, #13855c 100%); color: white; border-radius: 12px; }
.bg-gradient-danger { background: linear-gradient(45deg, #e74a3b 0%, #be2617 100%); color: white; border-radius: 12px; }
.bg-gradient-warning { background: linear-gradient(45deg, #f6c23e 0%, #dda20a 100%); color: white; border-radius: 12px; }
.bg-gradient-primary { background: linear-gradient(45deg, #4e73df 0%, #224abe 100%); color: white; border-radius: 12px; }
.stats-card { padding: 25px; transition: transform 0.2s; }
.stats-card:hover { transform: translateY(-5px); }
.stats-card h3 { font-size: 2rem; font-weight: 800; margin-bottom: 0; }
.stats-card p { opacity: 0.9; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; }
.table th { border: 0; font-size: 0.8rem; text-transform: uppercase; color: #6e707e; }
.table td { vertical-align: middle; padding: 15px 10px; }
</style>

<script>
document.getElementById('view_type').addEventListener('change', function() {
    document.querySelectorAll('.filter-input').forEach(el => el.style.display = 'none');
    if (this.value === 'daily' || this.value === 'weekly') {
        document.getElementById('daily_filter').style.display = 'block';
    } else if (this.value === 'monthly') {
        document.getElementById('monthly_filter').style.display = 'block';
    } else if (this.value === 'yearly') {
        document.getElementById('yearly_filter').style.display = 'block';
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

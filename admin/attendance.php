<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','teacher']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();

$is_teacher = ($_SESSION['role'] === 'teacher');
$current_teacher_id = 0;

if ($is_teacher) {
    // FIX: Match teacher using username from users table instead of full_name directly
    $stT = $pdo->prepare('SELECT t.id FROM teachers t JOIN users u ON t.full_name = u.full_name WHERE u.username = :n LIMIT 1');
    $stT->execute([':n' => $_SESSION['username']]);
    $current_teacher_id = $stT->fetchColumn() ?: 0;
    
    // Only classes where this teacher has at least one assigned section
    $classes = $pdo->prepare('SELECT DISTINCT c.* FROM classes c 
                               JOIN sections s ON c.id = s.class_id 
                               WHERE s.class_teacher_id = :t');
    $classes->execute([':t' => $current_teacher_id]);
    $classes = $classes->fetchAll();
} else {
    $classes = $pdo->query('SELECT * FROM classes')->fetchAll();
}
?>
<h3 class="fw-bold text-primary mb-4"><i class="fas fa-calendar-check me-2"></i>Daily Attendance Marking</h3>
<div class="card shadow-sm border-0 p-3 p-md-4">
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <label class="form-label small fw-bold">Select Class</label>
            <select id="class_id" class="form-select border-primary-subtle shadow-sm">
                <option value="">-- Class --</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small fw-bold">Section</label>
            <select id="section_id" class="form-select border-primary-subtle shadow-sm"><option value="">-- All --</option></select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Date</label>
            <input type="date" id="att_date" class="form-control border-primary-subtle shadow-sm" value="<?=date('Y-m-d')?>">
        </div>
        <div class="col-md-3 align-self-end">
            <button id="loadBtn" class="btn btn-primary w-100 py-2 shadow-sm"><i class="fas fa-users me-2"></i> Load Student List</button>
        </div>
    </div>
</div>
<div id="attendanceArea" class="mt-4"></div>

<script>
document.getElementById('class_id').addEventListener('change', async function(){
  const classId = this.value;
  if(!classId) return;
  const isTeacher = <?= json_encode($is_teacher) ?>;
  const teacherId = <?= json_encode($current_teacher_id) ?>;
  
  let url = '<?=BASE_URL?>admin/attendance_ajax.php?action=sections&class_id='+classId;
  if(isTeacher) url += '&teacher_id='+teacherId;
  
  const res = await fetch(url);
  const data = await res.json();
  const sel = document.getElementById('section_id'); 
  sel.innerHTML = isTeacher ? '' : '<option value="">-- All --</option>';
  data.forEach(s=>{ const o = document.createElement('option'); o.value=s.id; o.textContent=s.name; sel.appendChild(o); });
});

document.getElementById('loadBtn').addEventListener('click', async function(){
  const classId = document.getElementById('class_id').value;
  const sectionId = document.getElementById('section_id').value;
  const date = document.getElementById('att_date').value;
  if (!classId || !date) return alert('Please select class and date');
  
  document.getElementById('attendanceArea').innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary"></div></div>';
  
  const res = await fetch('<?=BASE_URL?>admin/attendance_ajax.php?action=load&class_id='+classId+'&section_id='+sectionId+'&date='+date);
  const html = await res.text();
  document.getElementById('attendanceArea').innerHTML = html;
});

async function saveAttendance(form){
  const formData = new FormData(form);
  const btn = form.querySelector('button');
  const originalText = btn.innerHTML;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
  btn.disabled = true;

  try {
      const res = await fetch('<?=BASE_URL?>admin/attendance_ajax.php?action=save', {method:'POST', body: formData});
      const data = await res.json();
      if (data.success) {
          alert('Attendance saved successfully!');
      } else {
          alert('Error: '+(data.error||'unknown'));
      }
  } catch(e) {
      alert('Network error. Please try again.');
  } finally {
      btn.innerHTML = originalText;
      btn.disabled = false;
  }
}

// Global function to handle mobile button clicks
function setStatus(sid, status, btn) {
    const input = document.getElementById("input_" + sid);
    if (input) {
        input.value = status;
        const container = btn.parentElement;
        container.querySelectorAll(".btn").forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
    } else {
        console.error("Input not found for student ID: " + sid);
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

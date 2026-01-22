<?php 
function is_active($path) { 
    return strpos($_SERVER['REQUEST_URI'], $path) !== false ? 'active' : ''; 
} 

// Ensure context is set
if (!isset($_SESSION['context'])) {
    $_SESSION['context'] = 'School';
}

// Handle context switch
if (isset($_GET['switch_context'])) {
    $_SESSION['context'] = ($_GET['switch_context'] === 'Coaching') ? 'Coaching' : 'School';
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

$context = $_SESSION['context'];
$is_coaching = ($context === 'Coaching');
?>
<div class="sidebar">
    <div class="brand d-flex align-items-center justify-content-center flex-column pb-0">
        <div class="mb-2">
            <i class="fas <?= $is_coaching ? 'fa-book-reader' : 'fa-university' ?> fa-2x"></i>
        </div>
        <div class="fw-bold h5 mb-0 text-uppercase"><?= $context ?> Portal</div>
        <div class="small fw-normal opacity-75 mt-1" style="font-size: 0.6rem;">Pakistani Standard v3.0</div>
    </div>

    <!-- Context Switcher Toggle -->
    <div class="px-3 mb-4 mt-3">
        <div class="btn-group w-100 shadow-sm" style="border-radius: 8px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
            <a href="?switch_context=School" class="btn btn-sm <?= !$is_coaching ? 'btn-light text-primary' : 'btn-dark text-white opacity-50' ?> border-0 py-2">
                <i class="fas fa-school me-1"></i> School
            </a>
            <a href="?switch_context=Coaching" class="btn btn-sm <?= $is_coaching ? 'btn-light text-primary' : 'btn-dark text-white opacity-50' ?> border-0 py-2">
                <i class="fas fa-laptop-code me-1"></i> Coaching
            </a>
        </div>
    </div>
    
    <div class="px-3 mb-2">
        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Institutional Navigation</small>
    </div>

    <?php if ($_SESSION['role'] === 'super_admin'): ?>
        <a href="<?=BASE_URL?>admin/dashboard_enhanced.php" class="<?=is_active('dashboard')?>">
            <i class="fas fa-chart-line"></i> Analytics
        </a>
        
        <div class="px-3 mb-2 mt-4">
            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Academics & HR</small>
        </div>
        <a href="<?=BASE_URL?>admin/students.php" class="<?=is_active('students')?>">
            <i class="fas fa-user-graduate"></i> <?= $is_coaching ? 'Coaching Students' : 'Student Directory' ?>
        </a>
        <a href="<?=BASE_URL?>admin/student_add.php" class="<?=is_active('student_add')?>">
            <i class="fas fa-plus-circle"></i> Add New Student
        </a>
        <a href="<?=BASE_URL?>admin/student_id_card.php" class="<?=is_active('student_id_card')?>">
            <i class="fas fa-id-card"></i> Student ID Cards
        </a>
        <a href="<?=BASE_URL?>admin/admission_slip.php" class="<?=is_active('admission_slip')?>">
            <i class="fas fa-file-alt"></i> Admission Slips
        </a>
        
        <?php if (!$is_coaching): ?>
        <a href="<?=BASE_URL?>admin/teachers.php" class="<?=is_active('teachers')?>">
            <i class="fas fa-chalkboard-teacher"></i> Faculty Members
        </a>
        <a href="<?=BASE_URL?>admin/teacher_add.php" class="<?=is_active('teacher_add')?>">
            <i class="fas fa-plus-circle"></i> Add New Teacher
        </a>
        <?php endif; ?>

        <a href="<?=BASE_URL?>admin/attendance.php" class="<?=is_active('attendance.php')?>">
            <i class="fas fa-calendar-check"></i> Mark Attendance
        </a>
        <a href="<?=BASE_URL?>admin/attendance_center.php" class="<?=is_active('attendance_center')?>">
            <i class="fas fa-chart-pie"></i> Attendance Center
        </a>
        <a href="<?=BASE_URL?>admin/biometric_import.php" class="<?=is_active('biometric')?> text-info">
            <i class="fas fa-fingerprint"></i> Biometric Sync
        </a>

        <div class="px-3 mb-2 mt-4">
            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Finance & Accounts</small>
        </div>
        <a href="<?=BASE_URL?>admin/fee_pay.php" class="<?=is_active('fee_pay')?>">
            <i class="fas fa-money-bill-wave"></i> Fee Collection
        </a>
        <a href="<?=BASE_URL?>admin/finance_hub.php" class="<?=is_active('finance_hub')?>">
            <i class="fas fa-university"></i> Finance Hub
        </a>
        
        <?php if (!$is_coaching): ?>
        <a href="<?=BASE_URL?>admin/salaries.php" class="<?=is_active('salaries')?>">
            <i class="fas fa-wallet"></i> Faculty Payroll
        </a>
        <a href="<?=BASE_URL?>admin/expenses.php" class="<?=is_active('expenses')?> border-bottom">
            <i class="fas fa-file-invoice"></i> Official Expenses
        </a>
        <a href="<?=BASE_URL?>admin/profitloss.php" class="<?=is_active('profitloss')?>">
            <i class="fas fa-balance-scale"></i> Analytics & P/L
        </a>
        <?php endif; ?>

        <div class="px-3 mb-2 mt-4">
            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Academic Setup</small>
        </div>
        <?php /*
        <a href="<?=BASE_URL?>admin/exams.php" class="<?=is_active('exams.php')?>">
            <i class="fas fa-file-alt"></i> Manage Exams
        </a>
        <a href="<?=BASE_URL?>admin/exam_marks.php" class="<?=is_active('exam_marks')?>">
            <i class="fas fa-award"></i> Mark Entry
        </a>
        <a href="<?=BASE_URL?>admin/result_card.php" class="<?=is_active('result_card')?>">
            <i class="fas fa-certificate"></i> Result Cards
        </a>
        <a href="<?=BASE_URL?>admin/timetable.php" class="<?=is_active('timetable.php')?>">
            <i class="fas fa-clock"></i> <?= $is_coaching ? 'Batch Schedule' : 'School Timetable' ?>
        </a>
        */ ?>
        <a href="<?=BASE_URL?>admin/sections_manage.php" class="<?=is_active('sections_manage.php')?>">
            <i class="fas fa-layer-group"></i> Class Divisions
        </a>
        <a href="<?=BASE_URL?>admin/sections.php" class="<?=is_active('sections.php')?>">
            <i class="fas <?= $is_coaching ? 'fa-layer-group' : 'fa-door-open' ?>"></i> <?= $is_coaching ? 'Coaching Batches' : 'Class Sections' ?>
        </a>
        <a href="<?=BASE_URL?>admin/classes.php" class="<?=is_active('classes.php')?>">
            <i class="fas fa-school"></i> Class Management
        </a>


        
    <?php elseif ($_SESSION['role'] === 'teacher'): ?>
        <a href="<?=BASE_URL?>teacher/dashboard.php" class="<?=is_active('dashboard')?>">
            <i class="fas fa-home"></i> Home
        </a>
        
        <div class="px-3 mb-2 mt-4">
            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Attendance Management</small>
        </div>
        <a href="<?=BASE_URL?>admin/attendance.php" class="<?=is_active('attendance.php')?>">
            <i class="fas fa-calendar-check"></i> Mark Attendance
        </a>
        <a href="<?=BASE_URL?>admin/attendance_center.php" class="<?=is_active('attendance_center')?>">
            <i class="fas fa-clipboard-list"></i> Attendance Center
        </a>
        
    <?php elseif ($_SESSION['role'] === 'accountant'): ?>
        <a href="<?=BASE_URL?>accountant/dashboard.php" class="<?=is_active('dashboard')?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?=BASE_URL?>admin/fee_pay.php" class="<?=is_active('fee_pay')?>">
            <i class="fas fa-cash-register"></i> Collect Fees
        </a>
        <a href="<?=BASE_URL?>admin/finance_hub.php" class="<?=is_active('finance_hub')?>">
            <i class="fas fa-university"></i> Finance Hub
        </a>
        <a href="<?=BASE_URL?>admin/salaries.php" class="<?=is_active('salaries')?>">
            <i class="fas fa-wallet"></i> Pay Salaries
        </a>
        <a href="<?=BASE_URL?>admin/expenses.php" class="<?=is_active('expenses')?>">
            <i class="fas fa-file-invoice-dollar"></i> Manage Expenses
        </a>
        <a href="<?=BASE_URL?>admin/profitloss.php" class="<?=is_active('profitloss')?>">
            <i class="fas fa-chart-line"></i> Analytics & P/L
        </a>
    <?php endif; ?>
    
    <div class="mt-auto pb-4">
        <hr class="sidebar-divider mx-3 opacity-25">
        <a href="<?=BASE_URL?>admin/logout.php" class="text-danger">
            <i class="fas fa-sign-out-alt"></i> Secure Logout
        </a>
    </div>
</div>

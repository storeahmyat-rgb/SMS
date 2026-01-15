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
        
        <?php if (!$is_coaching): ?>
        <a href="<?=BASE_URL?>admin/teachers.php" class="<?=is_active('teachers')?>">
            <i class="fas fa-chalkboard-teacher"></i> Faculty Members
        </a>
        <?php endif; ?>

        <a href="<?=BASE_URL?>admin/attendance.php" class="<?=is_active('attendance.php')?>">
            <i class="fas fa-calendar-check"></i> Attendance
        </a>
        
        <?php if (!$is_coaching): ?>
        <a href="<?=BASE_URL?>admin/attendance_daily.php" class="<?=is_active('attendance_daily')?>">
            <i class="fas fa-tasks"></i> Daily Summary
        </a>
        <?php endif; ?>

        <a href="<?=BASE_URL?>admin/attendance_report.php" class="<?=is_active('attendance_report')?>">
            <i class="fas fa-clipboard-list"></i> Logs & Reports
        </a>

        <div class="px-3 mb-2 mt-4">
            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Finance & Accounts</small>
        </div>
        <a href="<?=BASE_URL?>admin/fee_pay.php" class="<?=is_active('fee_pay')?>">
            <i class="fas fa-money-bill-wave"></i> Fee Collection
        </a>
        <a href="<?=BASE_URL?>admin/pending_fees.php" class="<?=is_active('pending_fees')?>">
            <i class="fas fa-clock"></i> Monthly Dues
        </a>
        <a href="<?=BASE_URL?>admin/ledger.php" class="<?=is_active('ledger')?>">
            <i class="fas fa-book"></i> Fee Ledgers
        </a>
        
        <?php if (!$is_coaching): ?>
        <a href="<?=BASE_URL?>admin/salaries.php" class="<?=is_active('salaries.php')?>">
            <i class="fas fa-wallet"></i> Staff Salaries
        </a>
        <a href="<?=BASE_URL?>admin/expenses.php" class="<?=is_active('expenses.php')?>">
            <i class="fas fa-file-invoice"></i> Official Expenses
        </a>
        <a href="<?=BASE_URL?>admin/profitloss.php" class="<?=is_active('profitloss.php')?>">
            <i class="fas fa-balance-scale"></i> Profit & Loss
        </a>
        <?php endif; ?>

        <div class="px-3 mb-2 mt-4">
            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 1px;">Exams & Setup</small>
        </div>
        <a href="<?=BASE_URL?>admin/exams.php" class="<?=is_active('exams.php')?>">
            <i class="fas fa-file-alt"></i> Manage Exams
        </a>
        <a href="<?=BASE_URL?>admin/exam_marks.php" class="<?=is_active('exam_marks')?>">
            <i class="fas fa-award"></i> Mark Entry
        </a>
        <a href="<?=BASE_URL?>admin/timetable.php" class="<?=is_active('timetable.php')?>">
            <i class="fas fa-clock"></i> <?= $is_coaching ? 'Batch Schedule' : 'School Timetable' ?>
        </a>
        <a href="<?=BASE_URL?>admin/sections.php" class="<?=is_active('sections.php')?>">
            <i class="fas <?= $is_coaching ? 'fa-layer-group' : 'fa-door-open' ?>"></i> <?= $is_coaching ? 'Coaching Batches' : 'Class Sections' ?>
        </a>
        <a href="<?=BASE_URL?>admin/classes_manage.php" class="<?=is_active('classes_manage')?>">
            <i class="fas fa-cogs"></i> System Setup
        </a>
        <a href="<?=BASE_URL?>admin/biometric_import.php" class="<?=is_active('biometric')?> text-info">
            <i class="fas fa-fingerprint"></i> Biometric Sync
        </a>
    
    <?php elseif ($_SESSION['role'] === 'teacher'): ?>
        <a href="<?=BASE_URL?>teacher/dashboard.php" class="<?=is_active('dashboard')?>">
            <i class="fas fa-home"></i> Home
        </a>
        <a href="<?=BASE_URL?>admin/students.php" class="<?=is_active('students')?>">
            <i class="fas fa-user-graduate"></i> My Students
        </a>
        <a href="<?=BASE_URL?>admin/attendance.php" class="<?=is_active('attendance')?>">
            <i class="fas fa-calendar-check"></i> Mark Attendance
        </a>
        <a href="<?=BASE_URL?>admin/exam_marks.php" class="<?=is_active('exam_marks')?>">
            <i class="fas fa-award"></i> Post Result
        </a>
        <a href="<?=BASE_URL?>admin/teacher_attendance.php" class="<?=is_active('teacher_attendance')?>">
            <i class="fas fa-user-clock"></i> My Attendance
        </a>
        
    <?php elseif ($_SESSION['role'] === 'accountant'): ?>
        <a href="<?=BASE_URL?>accountant/dashboard.php" class="<?=is_active('dashboard')?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="<?=BASE_URL?>admin/fee_pay.php" class="<?=is_active('fee_pay')?>">
            <i class="fas fa-cash-register"></i> Collect Fees
        </a>
        <a href="<?=BASE_URL?>admin/pending_fees.php" class="<?=is_active('pending')?>">
            <i class="fas fa-user-clock"></i> Pending Dues
        </a>
        <a href="<?=BASE_URL?>admin/ledger.php" class="<?=is_active('ledger')?>">
            <i class="fas fa-book"></i> Student Ledger
        </a>
        <a href="<?=BASE_URL?>admin/salary_manage.php" class="<?=is_active('salary')?>">
            <i class="fas fa-wallet"></i> Pay Salaries
        </a>
        <a href="<?=BASE_URL?>admin/expenses.php" class="<?=is_active('expenses')?>">
            <i class="fas fa-file-invoice-dollar"></i> Manage Expenses
        </a>
        <a href="<?=BASE_URL?>admin/income_report.php" class="<?=is_active('report')?>">
            <i class="fas fa-chart-line"></i> Income Report
        </a>
    <?php endif; ?>
    
    <div class="mt-auto pb-4">
        <hr class="sidebar-divider mx-3 opacity-25">
        <a href="<?=BASE_URL?>admin/logout.php" class="text-danger">
            <i class="fas fa-sign-out-alt"></i> Secure Logout
        </a>
    </div>
</div>

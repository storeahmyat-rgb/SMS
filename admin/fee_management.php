<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$tab = $_GET['tab'] ?? 'collect';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1><i class="fas fa-money-bill-wave me-2"></i>Fee Management</h1>
        <p class="text-muted mb-0">Comprehensive fee collection, tracking, and reporting system</p>
    </div>
</div>

<!-- Tab Navigation -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'collect' ? 'active' : '' ?>" href="?tab=collect">
            <i class="fas fa-hand-holding-usd me-1"></i> Fee Collection
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'dues' ? 'active' : '' ?>" href="?tab=dues">
            <i class="fas fa-clock me-1"></i> Monthly Dues
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'ledger' ? 'active' : '' ?>" href="?tab=ledger">
            <i class="fas fa-book me-1"></i> Fee Ledgers
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'structure' ? 'active' : '' ?>" href="?tab=structure">
            <i class="fas fa-receipt me-1"></i> Fee Structure
        </a>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <?php if ($tab === 'collect'): ?>
        <!-- Fee Collection Content -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i> 
            <strong>Quick Access:</strong> Use this tab to collect fees from students. Search by admission number or name.
        </div>
        <iframe src="<?=BASE_URL?>admin/fee_pay.php?embedded=1" 
                style="width:100%; height:800px; border:none;" 
                title="Fee Collection"></iframe>
    
    <?php elseif ($tab === 'dues'): ?>
        <!-- Monthly Dues Content -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i> 
            <strong>Pending Dues:</strong> View all students with outstanding monthly fees.
        </div>
        <iframe src="<?=BASE_URL?>admin/pending_fees.php?embedded=1" 
                style="width:100%; height:800px; border:none;" 
                title="Monthly Dues"></iframe>
    
    <?php elseif ($tab === 'ledger'): ?>
        <!-- Fee Ledgers Content -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i> 
            <strong>Student Ledgers:</strong> View complete payment history for any student.
        </div>
        <iframe src="<?=BASE_URL?>admin/ledger.php?embedded=1" 
                style="width:100%; height:800px; border:none;" 
                title="Fee Ledgers"></iframe>
    
    <?php elseif ($tab === 'structure'): ?>
        <!-- Fee Structure Content -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-1"></i> 
            <strong>Fee Structure:</strong> Configure fee categories and amounts for different classes.
        </div>
        <iframe src="<?=BASE_URL?>admin/fees.php?embedded=1" 
                style="width:100%; height:800px; border:none;" 
                title="Fee Structure"></iframe>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

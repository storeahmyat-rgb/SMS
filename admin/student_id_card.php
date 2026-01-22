<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$id = intval($_GET['id'] ?? 0);
$class_id = intval($_GET['class_id'] ?? 0);
$context = $_SESSION['context'] ?? 'School';

$students = [];
if ($id > 0) {
    // FIX: Added 's' alias to fix SQL Error reported by user
    $stmt = $pdo->prepare('SELECT s.*, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.id = :id');
    $stmt->execute([':id'=>$id]);
    $students = $stmt->fetchAll();
} elseif ($class_id > 0) {
    $stmt = $pdo->prepare('SELECT s.*, c.name AS class_name FROM students s LEFT JOIN classes c ON s.class_id = c.id WHERE s.class_id = :cid AND s.institution_type = :ctx AND s.status = "Active"');
    $stmt->execute([':cid'=>$class_id, ':ctx'=>$context]);
    $students = $stmt->fetchAll();
}

$classes = $pdo->prepare('SELECT * FROM classes WHERE id IN (SELECT class_id FROM students WHERE institution_type = :ctx)');
$classes->execute([':ctx'=>$context]);
$classes = $classes->fetchAll();

if (!$id && !$class_id): ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-bold"><i class="fas fa-print me-2 text-primary"></i>Bulk Print ID Cards</div>
            <div class="card-body p-4">
                <form method="GET" class="row g-3">
                    <div class="col-md-9">
                        <label class="form-label">Select Class to Print All Cards</label>
                        <select name="class_id" class="form-select border-primary" required>
                            <option value="">-- Choose Class --</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100 shadow-sm">Load Students</button>
                    </div>
                </form>
                <hr class="my-4">
                <p class="text-muted small"><i class="fas fa-info-circle me-1"></i> To print a single card, go to the <strong>Student Directory</strong> and click the ID card icon next to a student.</p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($students)): ?>
<div class="no-print mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0">ID Card Generation</h1>
        <p class="text-muted mb-0"><?=count($students)?> cards ready for printing.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-lg btn-success shadow-sm px-5">
            <i class="fas fa-print me-2"></i> Print Official Cards
        </button>
        <a href="student_id_card.php" class="btn btn-lg btn-outline-secondary shadow-sm px-4">Reset</a>
    </div>
</div>

<div class="id-card-print-area">
    <?php foreach ($students as $s): ?>
    <div class="id_card_wrap">
        <!-- FRONT SIDE -->
        <div class="id-card front">
            <div class="card-header-premium">
                <div class="header-overlay"></div>
                <div class="school-logo">
                    <i class="fas <?= $context === 'Coaching' ? 'fa-book-reader' : 'fa-university' ?>"></i>
                </div>
                <div class="school-info">
                    <h5 class="m-0 fw-bold">MODERN <?= strtoupper($context) ?></h5>
                    <small>PREMIUM QUALITY EDUCATION</small>
                </div>
                <div class="id-badge">STUDENT</div>
            </div>
            
            <div class="card-body-premium">
                <div class="photo-section">
                    <div class="photo-frame">
                        <?php if ($s['photo']): ?>
                            <img src="<?=BASE_URL?>assets/uploads/students/<?=htmlspecialchars($s['photo'])?>" alt="Student">
                        <?php else: ?>
                            <div class="photo-placeholder"><i class="fas fa-user-graduate"></i></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h4 class="student-name text-uppercase"><?=htmlspecialchars($s['full_name'])?></h4>
                <div class="admission-chip"><?=htmlspecialchars($s['admission_no'])?></div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <span class="label">FATHER NAME</span>
                        <span class="value"><?=htmlspecialchars($s['father_name'])?></span>
                    </div>
                    <div class="info-group">
                        <div class="info-item">
                            <span class="label">CLASS</span>
                            <span class="value"><?=htmlspecialchars($s['class_name'] ?? 'N/A')?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">BLOOD</span>
                            <span class="value"><?=htmlspecialchars($s['blood_group'] ?: 'N/A')?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="label">EMERGENCY CONTACT</span>
                        <span class="value"><?=htmlspecialchars($s['contact'])?></span>
                    </div>
                </div>
            </div>

            <div class="card-footer-premium">
                <div class="footer-left">
                    <small>Issued: <?= date('M Y') ?></small>
                </div>
                <div class="footer-center">
                    <div class="qr-code-placeholder"><i class="fas fa-qrcode"></i></div>
                </div>
                <div class="footer-right">
                    <div class="sig-line"></div>
                    <small>PRINCIPAL</small>
                </div>
            </div>
            <div class="bottom-stripe"></div>
        </div>

        <!-- BACK SIDE -->
        <div class="id-card back">
            <div class="back-header">
                <h6 class="m-0 fw-bold text-primary">TERMS & CONDITIONS</h6>
            </div>
            <div class="back-content">
                <ul class="tc-list">
                    <li>This card is the property of Modern <?= $context ?> System and is non-transferable.</li>
                    <li>The cardholder must present this card at the school gate and for all official examinations.</li>
                    <li>If found, please return to the school administration office immediately.</li>
                    <li>In case of loss, a replacement card will be issued upon payment of the prescribed fee.</li>
                    <li>Misuse of this card is a disciplinary offense and may result in strict action.</li>
                </ul>
                
                <div class="validation-box">
                    <p class="m-0 small fw-bold">VALIDATION PERIOD</p>
                    <h6 class="m-0 text-primary">Session <?= date('Y') ?> - <?= date('Y', strtotime('+1 year')) ?></h6>
                </div>

                <div class="contact-section">
                    <p class="m-0 small"><strong>Address:</strong> Phase 2, Education Colony, Karachi</p>
                    <p class="m-0 small"><strong>Phone:</strong> 0300-0000000 | <strong>Email:</strong> info@modern.edu</p>
                </div>
                
                <div class="back-footer">
                    <div class="website-link">www.modernsystem.pk</div>
                    <div class="version-tag">ST-ID v3.0</div>
                </div>
            </div>
            <div class="bottom-stripe stripe-orange"></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
/* PREMIUM ID CARD CSS */
.id_card_wrap {
    display: flex;
    gap: 30px;
    margin-bottom: 50px;
    justify-content: center;
    page-break-inside: avoid;
}

.id-card {
    width: 340px;
    height: 520px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
    position: relative;
    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    border: 1px solid #eee;
    display: flex;
    flex-direction: column;
}

/* Header Styling */
.card-header-premium {
    height: 120px;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
    position: relative;
    padding: 20px;
    color: white;
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url('https://www.transparenttextures.com/patterns/cubes.png');
    opacity: 0.1;
}

.school-logo {
    width: 50px; height: 50px;
    background: white;
    color: #1e3a8a;
    border-radius: 10px;
    display: flex;
    align-items: center; justify-content: center;
    font-size: 1.8rem;
    z-index: 1;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.school-info { z-index: 1; flex-grow: 1; }
.school-info h5 { line-height: 1.1; letter-spacing: 0.5px; }
.school-info small { opacity: 0.8; font-size: 0.65rem; letter-spacing: 1px; }

.id-badge {
    position: absolute;
    top: 0; right: 20px;
    background: #f97316;
    padding: 5px 10px;
    font-size: 0.65rem;
    font-weight: bold;
    border-radius: 0 0 5px 5px;
    z-index: 1;
}

/* Body Styling */
.card-body-premium {
    padding: 20px;
    flex-grow: 1;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.photo-section { margin-bottom: 15px; }
.photo-frame {
    width: 130px; height: 130px;
    border: 4px solid #fff;
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    border-radius: 15px;
    overflow: hidden;
    background: #f8fafc;
}
.photo-frame img { width: 100%; height: 100%; object-fit: cover; }
.photo-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 4rem; color: #cbd5e1;
}

.student-name {
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 5px;
    font-size: 1.25rem;
    letter-spacing: -0.5px;
}

.admission-chip {
    background: #e2e8f0;
    padding: 4px 15px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: 20px;
    display: inline-block;
}

.info-grid {
    width: 100%;
    text-align: left;
}

.info-item { margin-bottom: 10px; }
.info-group { display: flex; gap: 10px; margin-bottom: 10px; }
.info-group .info-item { flex: 1; }

.label {
    display: block;
    font-size: 0.55rem;
    color: #94a3b8;
    font-weight: bold;
    text-transform: uppercase;
    margin-bottom: 1px;
}

.value {
    display: block;
    font-size: 0.85rem;
    color: #334155;
    font-weight: 600;
    line-height: 1.2;
}

/* Footer Styling */
.card-footer-premium {
    padding: 15px 20px;
    border-top: 1px dashed #e2e8f0;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
}

.footer-left small { font-size: 0.6rem; color: #94a3b8; font-weight: bold; }

.footer-center .qr-code-placeholder {
    font-size: 2.2rem;
    color: #1e293b;
    opacity: 0.8;
}

.footer-right { text-align: center; width: 100px; }
.sig-line { border-bottom: 1px solid #1e293b; height: 35px; margin-bottom: 5px; }
.footer-right small { font-size: 0.55rem; font-weight: 800; color: #1e293b; text-transform: uppercase; }

.bottom-stripe { height: 8px; background: #1e3a8a; }
.stripe-orange { background: #f97316; }

/* BACK SIDE CSS */
.back { padding: 30px 20px; background-color: #f8fafc; }
.back-header { text-align: center; margin-bottom: 20px; position: relative; }
.back-header::after {
    content: ''; display: block; width: 40px; height: 3px; 
    background: #3b82f6; margin: 8px auto; border-radius: 2px;
}

.tc-list {
    padding-left: 15px;
    margin-bottom: 30px;
    text-align: left;
}
.tc-list li {
    font-size: 0.68rem;
    color: #475569;
    margin-bottom: 8px;
    line-height: 1.4;
    list-style-type: decimal;
}

.validation-box {
    background: white;
    padding: 15px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    margin-bottom: 25px;
    text-align: center;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}

.contact-section { text-align: center; margin-bottom: 30px; }
.contact-section p { color: #64748b; line-height: 1.5; font-size: 0.72rem; }

.back-footer {
    border-top: 1px solid #e2e8f0;
    padding-top: 15px;
    display: flex;
    justify-content: space-between;
    margin-top: auto;
}
.website-link { font-size: 0.7rem; font-weight: bold; color: #1e3a8a; }
.version-tag { font-size: 0.6rem; color: #94a3b8; }

@media print {
    .no-print, .sidebar, .top-navbar { display: none !important; }
    .main-content { margin: 0 !important; padding: 0 !important; }
    body { background: white !important; font-size: 12pt; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .id_card_wrap { 
        display: flex !important; 
        gap: 1cm !important; 
        margin-bottom: 1cm !important; 
        justify-content: center !important;
        background: transparent !important;
    }
    .id-card { 
        box-shadow: none !important; 
        border: 1px solid #000 !important;
        margin: 0 !important;
    }
    .id_card_wrap { page-break-after: always; }
}
</style>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

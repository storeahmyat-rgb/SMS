<?php
require_once __DIR__ . '/../includes/auth.php';
requireRole(['super_admin','accountant']);
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = getPDO();
$context = $_SESSION['context'] ?? 'School';

// Filter students and fees by active context
$stmt1 = $pdo->prepare('SELECT id, admission_no, full_name, class_id FROM students WHERE status="Active" AND institution_type = :ctx');
$stmt1->execute([':ctx' => $context]);
$students = $stmt1->fetchAll();

$stmt2 = $pdo->prepare('SELECT * FROM fees WHERE institution_type IN (:ctx, "Both")');
$stmt2->execute([':ctx' => $context]);
$fees = $stmt2->fetchAll();

$classes_list = $pdo->query('SELECT id, name FROM classes')->fetchAll();
$classes_json = json_encode($classes_list);
?>
<?php
$msg = '';
$payment_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
    $fee_ids = $_POST['fee_ids'] ?? [];
    $amounts = $_POST['amounts'] ?? [];
    $method = $_POST['method'] ?? 'Cash';
    $note = $_POST['note'] ?? '';
    $transaction_id = 'TXN-' . strtoupper(uniqid());

    if ($student_id > 0 && !empty($fee_ids)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO fee_payments (student_id, transaction_id, fee_id, amount, paid_on, paid_by, payment_method, note) VALUES (:sid, :tid, :fid, :amt, NOW(), :pb, :m, :n)');
            
            foreach ($fee_ids as $index => $fid) {
                $fid_val = intval($fid) ?: null;
                $amt_val = floatval($amounts[$index]);
                if ($amt_val > 0) {
                    $stmt->execute([':sid'=>$student_id, ':tid'=>$transaction_id, ':fid'=>$fid_val, ':amt'=>$amt_val, ':pb'=>$_SESSION['user_id'], ':m'=>$method, ':n'=>$note]);
                }
            }
            $pdo->commit();
            $payment_txn = $transaction_id;
            $msg = 'Success! Multiple payments recorded.';
        } catch (PDOException $e) {
            $pdo->rollBack();
            $msg = 'Error recording payments: ' . $e->getMessage();
        }
    } else {
        $msg = 'Selection incomplete. Please add at least one fee.';
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-11">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0 text-gray-800">Fee Collection Portal (Multi-Fee)</h1>
            <a href="<?=BASE_URL?>admin/pending_fees.php" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-history me-1"></i> View Dues
            </a>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-success shadow-sm d-flex align-items-center mb-4">
                <i class="fas fa-check-circle fs-4 me-3"></i>
                <div>
                    <?=htmlspecialchars($msg)?>
                    <?php if (isset($payment_txn)): ?>
                        <a href="<?=BASE_URL?>admin/fee_receipt.php?tid=<?=$payment_txn?>" target="_blank" class="ms-2 fw-bold text-decoration-none btn btn-sm btn-success">
                            <i class="fas fa-print me-1"></i>Print Combined Receipt
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 text-primary fw-bold"><i class="fas fa-plus-circle me-2"></i>1. Add Fees to List</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Student</label>
                            <select id="student_id_select" class="form-select select2" required>
                                <option value="">-- Choose Student --</option>
                                <?php foreach ($students as $s): ?>
                                    <option value="<?=$s['id']?>" data-class="<?=$s['class_id']?>">
                                        <?=htmlspecialchars($s['admission_no'].' - '.$s['full_name'])?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Fee</label>
                            <select id="fee_id_select" class="form-select">
                                <option value="0" data-amount="0">Custom / Others</option>
                                <?php foreach ($fees as $f): ?>
                                    <option value="<?=$f['id']?>" 
                                            data-class="<?=$f['class_id'] ?? ''?>" 
                                            data-amount="<?=$f['amount']?>"
                                            class="fee-opt">
                                        <?=htmlspecialchars($f['name'])?> (Rs. <?=number_format($f['amount'])?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Amount (Rs.)</label>
                            <input id="amt_input" class="form-control fw-bold text-primary" placeholder="0.00">
                        </div>

                        <button type="button" id="addFeeBtn" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-1"></i> Add to Receipt
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm border-0 h-100">
                    <form method="post">
                        <input type="hidden" name="student_id" id="form_student_id">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-success fw-bold"><i class="fas fa-shopping-cart me-2"></i>2. Selected Fees</h6>
                            <span class="badge bg-light text-dark border" id="student_indicator">No Student Selected</span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0" id="feeTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Fee Type</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-center" style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="cartItems">
                                    <!-- Dynamic Rows -->
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold fs-5 bg-light">
                                        <td class="ps-3">Total Payable</td>
                                        <td class="text-end text-primary" id="grandTotal">Rs. 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="card-footer bg-white p-4 border-top-0">
                            <div class="row g-3 mb-4">
                                <div class="col-6 text-start">
                                    <label class="form-label small fw-bold">Method</label>
                                    <select name="method" class="form-select form-select-sm">
                                        <option>Cash</option>
                                        <option>Bank</option>
                                    </select>
                                </div>
                                <div class="col-6 text-start">
                                    <label class="form-label small fw-bold">Payment Month</label>
                                    <input name="note" class="form-control form-control-sm" value="<?=date('F Y')?>">
                                </div>
                            </div>
                            <button class="btn btn-success btn-lg w-100 shadow-sm" id="submitBtn" disabled>
                                <i class="fas fa-check-double me-1"></i> Confirm & Finalize Receipt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const cartItems = document.getElementById('cartItems');
const grandTotalEl = document.getElementById('grandTotal');
const studentSelect = document.getElementById('student_id_select');
const formStudentId = document.getElementById('form_student_id');
const feeSelect = document.getElementById('fee_id_select');
const amtInput = document.getElementById('amt_input');
const submitBtn = document.getElementById('submitBtn');
const indicator = document.getElementById('student_indicator');

let cartData = [];

const allClasses = <?= $classes_json ?>;

function isFeeApplicable(feeName, studentClassId) {
    const studentClass = allClasses.find(c => c.id == studentClassId);
    if (!studentClass) return true;

    const normFee = feeName.toLowerCase().replace(/[^a-z0-9]/g, ' ');
    const normStudentClass = studentClass.name.toLowerCase().replace(/[^a-z0-9]/g, ' ');

    let bestMatchMatch = '';
    allClasses.forEach(c => {
        const nc = c.name.toLowerCase().replace(/[^a-z0-9]/g, ' ');
        const regex = new RegExp('\\b' + nc.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&') + '\\b', 'i');
        if (regex.test(normFee)) {
            if (nc.length > bestMatchMatch.length) {
                bestMatchMatch = nc;
            }
        }
    });

    if (bestMatchMatch === normStudentClass) return true;
    if (bestMatchMatch === '') return true; // Truly global
    return false;
}

function updateCart() {
    cartItems.innerHTML = '';
    let total = 0;
    cartData.forEach((item, index) => {
        total += item.amount;
        cartItems.innerHTML += `
            <tr>
                <td class="ps-3">
                    ${item.name}
                    <input type="hidden" name="fee_ids[]" value="${item.id}">
                    <input type="hidden" name="amounts[]" value="${item.amount}">
                </td>
                <td class="text-end fw-bold">Rs. ${item.amount.toLocaleString()}</td>
                <td class="text-center">
                    <button type="button" onclick="removeItem(${index})" class="btn btn-sm p-0 text-danger"><i class="fas fa-times-circle"></i></button>
                </td>
            </tr>
        `;
    });
    grandTotalEl.textContent = 'Rs. ' + total.toLocaleString();
    submitBtn.disabled = cartData.length === 0;
}

studentSelect.addEventListener('change', function() {
    const classId = this.options[this.selectedIndex].getAttribute('data-class');
    formStudentId.value = this.value;
    indicator.textContent = this.options[this.selectedIndex].text;
    
    // Clear list if student changes
    if(cartData.length > 0) {
       if(!confirm('Changing student will clear the current list. Continue?')) {
           this.value = formStudentId.value; // Revert
           return;
       }
       cartData = [];
       updateCart();
    }

    const feeOpts = feeSelect.querySelectorAll('.fee-opt');
    feeOpts.forEach(opt => {
        const feeClass = opt.getAttribute('data-class');
        const feeName = opt.textContent;
        
        if (feeClass && feeClass == classId) {
            opt.style.display = "";
        } else if (!feeClass || feeClass == "" || feeClass == "0") {
            opt.style.display = isFeeApplicable(feeName, classId) ? "" : "none";
        } else {
            opt.style.display = "none";
        }
    });
});

feeSelect.addEventListener('change', function() {
    amtInput.value = this.options[this.selectedIndex].getAttribute('data-amount') || '';
});

// Initial filter for the first load
(function() {
    const classId = studentSelect.options[studentSelect.selectedIndex]?.getAttribute('data-class') || "";
    const feeOpts = feeSelect.querySelectorAll('.fee-opt');
    feeOpts.forEach(opt => {
        const feeClass = opt.getAttribute('data-class') || "";
        const feeName = opt.textContent;

        if (feeClass && feeClass == classId) {
            opt.style.display = "";
        } else if (!feeClass || feeClass == "" || feeClass == "0") {
            opt.style.display = isFeeApplicable(feeName, classId) ? "" : "none";
        } else {
            opt.style.display = "none";
        }
    });
})();

document.getElementById('addFeeBtn').addEventListener('click', function() {
    if(!studentSelect.value) { alert('Please select a student first!'); return; }
    
    const feeId = feeSelect.value;
    const feeName = feeSelect.options[feeSelect.selectedIndex].text.split('(')[0].trim();
    const amount = parseFloat(amtInput.value);

    if(amount > 0) {
        cartData.push({ id: feeId, name: feeName, amount: amount });
        updateCart();
        // Reset selections
        feeSelect.value = "0";
        amtInput.value = "";
    } else {
        alert('Invalid amount.');
    }
});

function removeItem(index) {
    cartData.splice(index, 1);
    updateCart();
}

// Handle pre-fills from URL
(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const sid = urlParams.get('student_id');
    const ftype = urlParams.get('fee_type');
    if(sid) {
        studentSelect.value = sid;
        studentSelect.dispatchEvent(new Event('change'));
        if(ftype) {
            setTimeout(() => {
                const autoConfirm = urlParams.get('auto_confirm') === '1';
                const opts = feeSelect.querySelectorAll('option');
                for(let o of opts) {
                    if(o.text.includes(ftype)) {
                        feeSelect.value = o.value;
                        feeSelect.dispatchEvent(new Event('change'));
                        
                        // Ask before adding if not auto-confirmed
                        if (autoConfirm || confirm(`Do you want to apply ${ftype} charges for this student?`)) {
                            document.getElementById('addFeeBtn').click();
                        }
                        break;
                    }
                }
            }, 500);
        }
    }
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

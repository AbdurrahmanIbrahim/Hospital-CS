<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 2)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'lab';

/* =========================
   LOAD ALL ACTIVE TESTS
========================= */
$allTests = [];
$testQ = $db->query("SELECT * FROM tests WHERE status = 1 ORDER BY name ASC");
if ($testQ) {
    while ($row = $testQ->fetch_assoc()) {
        $allTests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Lab POS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../styles/styles.css">
<style>
/* ==========================================
   LAB POS STYLES
========================================== */
.pos-container {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 24px;
    padding: 0;
}

.pos-left {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.pos-right {
    position: sticky;
    top: 0;
    align-self: start;
}

/* Card overrides for POS */
.pos-card {
    background: var(--white);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
}

.pos-card-header {
    padding: 16px 20px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    font-weight: 600;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.pos-card-body {
    padding: 20px;
}

/* Patient Type Toggle */
.patient-toggle {
    display: flex;
    background: #f1f5f9;
    border-radius: 10px;
    padding: 4px;
    margin-bottom: 16px;
}

.patient-toggle label {
    flex: 1;
    text-align: center;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    color: var(--text-muted);
    transition: all 0.2s ease;
}

.patient-toggle input[type="radio"] {
    display: none;
}

.patient-toggle input[type="radio"]:checked + label {
    background: var(--primary);
    color: white;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
}

/* Search box */
.search-box {
    position: relative;
    margin-bottom: 12px;
}

.search-box input {
    width: 100%;
    padding: 12px 16px;
    padding-left: 40px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 14px;
    transition: border-color 0.2s;
    background: var(--white);
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.search-box .search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
}

/* Patient search results dropdown */
.patient-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid var(--border);
    border-radius: 10px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    z-index: 50;
    max-height: 260px;
    overflow-y: auto;
    display: none;
}

.patient-results .result-item {
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}

.patient-results .result-item:last-child {
    border-bottom: none;
}

.patient-results .result-item:hover {
    background: #f0f6ff;
}

.result-item .r-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--text-main);
}

.result-item .r-info {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
}

/* Selected patient card */
.selected-patient {
    display: none;
    background: #f0f6ff;
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 14px 16px;
    position: relative;
}

.selected-patient .sp-name {
    font-weight: 700;
    font-size: 15px;
    color: var(--primary-dark);
}

.selected-patient .sp-info {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 4px;
}

.selected-patient .sp-scheme {
    display: inline-block;
    margin-top: 6px;
    padding: 3px 10px;
    background: var(--primary);
    color: white;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.selected-patient .sp-remove {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: none;
    background: #ef4444;
    color: white;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.15s;
}

.selected-patient .sp-remove:hover {
    transform: scale(1.1);
}

/* Walk-in form */
.walkin-form {
    display: none;
}

.walkin-form .form-group {
    margin-bottom: 12px;
}

.walkin-form label {
    display: block;
    font-weight: 600;
    font-size: 13px;
    color: var(--text-main);
    margin-bottom: 6px;
}

.walkin-form input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
}

.walkin-form input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Test Grid */
.test-search {
    margin-bottom: 16px;
}

.test-search input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
}

.test-search input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.test-grid {
    max-height: 420px;
    overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 10px;
    padding: 4px;
}

.test-item {
    background: #f8fafc;
    border: 2px solid var(--border);
    border-radius: 10px;
    padding: 12px 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}

.test-item:hover {
    border-color: var(--primary);
    background: #f0f6ff;
}

.test-item.selected {
    border-color: var(--primary);
    background: #eff6ff;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15);
}

.test-item .ti-check {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 22px;
    height: 22px;
    border-radius: 6px;
    border: 2px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    transition: all 0.2s;
}

.test-item.selected .ti-check {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.test-item .ti-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--text-main);
    padding-right: 30px;
}

.test-item .ti-meta {
    display: flex;
    gap: 8px;
    margin-top: 6px;
    flex-wrap: wrap;
}

.test-item .ti-meta span {
    font-size: 11px;
    padding: 2px 8px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 20px;
    color: var(--text-muted);
}

.test-item .ti-price {
    font-weight: 700;
    font-size: 15px;
    color: var(--primary);
    margin-top: 8px;
}

.test-running-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #f0f6ff;
    border-radius: 10px;
    margin-top: 12px;
    font-weight: 600;
    font-size: 14px;
    color: var(--primary-dark);
}

.test-running-total .count-badge {
    background: var(--primary);
    color: white;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
}

/* Clinical Notes */
.clinical-textarea {
    width: 100%;
    min-height: 80px;
    padding: 12px 14px;
    border: 1px solid var(--border);
    border-radius: 10px;
    font-size: 14px;
    resize: vertical;
    font-family: inherit;
}

.clinical-textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Payment Summary */
.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    font-size: 14px;
    color: var(--text-main);
}

.summary-row.total-row {
    border-top: 2px solid var(--border);
    margin-top: 8px;
    padding-top: 14px;
    font-size: 18px;
    font-weight: 700;
    color: var(--primary-dark);
}

.summary-row .label {
    color: var(--text-muted);
    font-weight: 500;
}

.summary-row .value {
    font-weight: 600;
}

.summary-row .discount-val {
    color: var(--success);
}

.payment-method-select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid var(--border);
    border-radius: 8px;
    font-size: 14px;
    margin-top: 12px;
    background: white;
}

.payment-method-select:focus {
    outline: none;
    border-color: var(--primary);
}

.btn-complete {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    color: white;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 16px;
    transition: all 0.2s ease;
    letter-spacing: 0.3px;
}

.btn-complete:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
}

.btn-complete:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.history-link {
    display: block;
    text-align: center;
    margin-top: 12px;
    font-size: 13px;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.history-link:hover {
    text-decoration: underline;
}

/* Empty state */
.test-grid-empty {
    text-align: center;
    padding: 30px;
    color: var(--text-muted);
    font-size: 14px;
}

/* Responsive */
@media (max-width: 992px) {
    .pos-container {
        grid-template-columns: 1fr;
    }
    .pos-right {
        position: static;
    }
}

@media (max-width: 600px) {
    .test-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

<div class="pos-container">

<!-- ==========================================
     LEFT COLUMN
========================================== -->
<div class="pos-left">

    <!-- PATIENT SELECTION -->
    <div class="pos-card">
        <div class="pos-card-header">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Patient Selection
        </div>
        <div class="pos-card-body">
            <!-- Toggle -->
            <div class="patient-toggle">
                <input type="radio" name="patient_type" id="ptRegistered" value="registered" checked>
                <label for="ptRegistered">Registered Patient</label>
                <input type="radio" name="patient_type" id="ptWalkin" value="walkin">
                <label for="ptWalkin">Walk-In Customer</label>
            </div>

            <!-- Registered patient search -->
            <div id="registeredSection">
                <div class="search-box">
                    <svg class="search-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-width="2" d="m21 21-4.35-4.35"/></svg>
                    <input type="text" id="patientSearch" placeholder="Search by name, hospital number, or phone..." autocomplete="off">
                    <div class="patient-results" id="patientResults"></div>
                </div>
                <div class="selected-patient" id="selectedPatient">
                    <button class="sp-remove" onclick="clearPatient()">&times;</button>
                    <div class="sp-name" id="spName"></div>
                    <div class="sp-info" id="spInfo"></div>
                    <span class="sp-scheme" id="spScheme" style="display:none;"></span>
                </div>
            </div>

            <!-- Walk-in form -->
            <div class="walkin-form" id="walkinSection">
                <div class="form-group">
                    <label>Patient Name *</label>
                    <input type="text" id="walkinName" placeholder="Enter patient name">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" id="walkinPhone" placeholder="Enter phone number">
                </div>
            </div>
        </div>
    </div>

    <!-- TEST SELECTION -->
    <div class="pos-card">
        <div class="pos-card-header">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            Test Selection
        </div>
        <div class="pos-card-body">
            <div class="test-search">
                <input type="text" id="testSearch" placeholder="Search tests by name...">
            </div>

            <div class="test-grid" id="testGrid">
                <?php if (empty($allTests)): ?>
                    <div class="test-grid-empty">No active tests available.</div>
                <?php else: ?>
                    <?php foreach ($allTests as $t): ?>
                    <div class="test-item"
                         data-id="<?= $t['id'] ?>"
                         data-name="<?= htmlspecialchars($t['name']) ?>"
                         data-amount="<?= $t['amount'] ?>"
                         data-specimen="<?= htmlspecialchars(get_specimen($t['specimen'])) ?>"
                         data-type="<?= htmlspecialchars($t['type']) ?>"
                         onclick="toggleTest(this)">
                        <div class="ti-check">
                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div class="ti-name"><?= htmlspecialchars($t['name']) ?></div>
                        <div class="ti-meta">
                            <span><?= htmlspecialchars(get_specimen($t['specimen'])) ?></span>
                            <span><?= htmlspecialchars($t['type']) ?></span>
                        </div>
                        <div class="ti-price">N<?= number_format($t['amount'], 2) ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="test-running-total" id="runningTotal" style="display:none;">
                <span><span class="count-badge" id="testCount">0</span> test(s) selected</span>
                <span id="runningAmount">N0.00</span>
            </div>
        </div>
    </div>

    <!-- CLINICAL NOTES -->
    <div class="pos-card">
        <div class="pos-card-header">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Clinical Notes
        </div>
        <div class="pos-card-body">
            <textarea class="clinical-textarea" id="clinicalNotes" placeholder="Enter clinical notes or reason for tests (optional)..."></textarea>
        </div>
    </div>

</div>

<!-- ==========================================
     RIGHT COLUMN - PAYMENT SUMMARY
========================================== -->
<div class="pos-right">
    <div class="pos-card">
        <div class="pos-card-header">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14h6M9 10h6M5 4h14a2 2 0 012 2v14l-3-2-3 2-3-2-3 2-3-2-3 2V6a2 2 0 012-2z"/></svg>
            Payment Summary
        </div>
        <div class="pos-card-body">

            <!-- Selected Tests List -->
            <div id="selectedTestsList" style="margin-bottom:12px;">
                <p style="text-align:center;padding:20px 0;color:var(--text-muted);font-size:13px;">No tests selected yet</p>
            </div>

            <div class="summary-row">
                <span class="label">Subtotal</span>
                <span class="value" id="summarySubtotal">N0.00</span>
            </div>
            <div class="summary-row">
                <span class="label">Discount (<span id="discountPct">0</span>%)</span>
                <span class="value discount-val" id="summaryDiscount">- N0.00</span>
            </div>
            <div class="summary-row total-row">
                <span class="label">Net Amount</span>
                <span class="value" id="summaryNet">N0.00</span>
            </div>

            <select class="payment-method-select" id="paymentMethod">
                <option value="Cash">Cash</option>
                <option value="Card">Card</option>
                <option value="Transfer">Transfer</option>
                <option value="Insurance">Insurance</option>
            </select>

            <button class="btn-complete" id="btnComplete" onclick="completeOrder()" disabled>
                Complete Order
            </button>

            <a href="history.php" class="history-link">View POS Sales History</a>
        </div>
    </div>
</div>

</div><!-- /pos-container -->

</div><!-- /content-scroll -->

<?php include '../includes/footer.php'; ?>
</main>

<script>
/* ===========================================
   STATE
=========================================== */
let selectedPatient = null;   // {id, name, hospital_num, phone, scheme_name, discount_fee}
let selectedTests   = {};     // {test_id: {id, name, amount}}
let searchTimer     = null;

/* ===========================================
   PATIENT TYPE TOGGLE
=========================================== */
document.querySelectorAll('input[name="patient_type"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        if (this.value === 'registered') {
            document.getElementById('registeredSection').style.display = 'block';
            document.getElementById('walkinSection').style.display = 'none';
        } else {
            document.getElementById('registeredSection').style.display = 'none';
            document.getElementById('walkinSection').style.display = 'block';
            clearPatient();
        }
        updateSummary();
    });
});

/* ===========================================
   PATIENT SEARCH (AJAX)
=========================================== */
document.getElementById('patientSearch').addEventListener('input', function() {
    var q = this.value.trim();
    clearTimeout(searchTimer);

    if (q.length < 1) {
        document.getElementById('patientResults').style.display = 'none';
        return;
    }

    searchTimer = setTimeout(function() {
        $.ajax({
            url: 'search_patient.php',
            data: { q: q },
            dataType: 'json',
            success: function(data) {
                var container = document.getElementById('patientResults');
                container.innerHTML = '';

                if (data.length === 0) {
                    container.innerHTML = '<div class="result-item"><span class="r-name">No patients found</span></div>';
                    container.style.display = 'block';
                    return;
                }

                data.forEach(function(p) {
                    var div = document.createElement('div');
                    div.className = 'result-item';
                    div.innerHTML = '<div class="r-name">' + p.name + '</div>' +
                                    '<div class="r-info">' + p.hospital_num + ' | ' + p.phone +
                                    (p.scheme_name ? ' | ' + p.scheme_name + ' (' + p.discount_fee + '% off)' : '') +
                                    '</div>';
                    div.onclick = function() { selectPatient(p); };
                    container.appendChild(div);
                });

                container.style.display = 'block';
            }
        });
    }, 300);
});

// Close dropdown on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-box')) {
        document.getElementById('patientResults').style.display = 'none';
    }
});

/* ===========================================
   SELECT / CLEAR PATIENT
=========================================== */
function selectPatient(p) {
    selectedPatient = p;
    document.getElementById('patientResults').style.display = 'none';
    document.getElementById('patientSearch').value = '';

    document.getElementById('spName').textContent = p.name;
    document.getElementById('spInfo').textContent = p.hospital_num + ' | ' + p.phone;

    var schemeEl = document.getElementById('spScheme');
    if (p.scheme_name && p.scheme_name !== '') {
        schemeEl.textContent = p.scheme_name + ' (' + p.discount_fee + '% discount)';
        schemeEl.style.display = 'inline-block';
    } else {
        schemeEl.style.display = 'none';
    }

    document.getElementById('selectedPatient').style.display = 'block';
    updateSummary();
}

function clearPatient() {
    selectedPatient = null;
    document.getElementById('selectedPatient').style.display = 'none';
    document.getElementById('patientSearch').value = '';
    updateSummary();
}

/* ===========================================
   TEST SELECTION
=========================================== */
function toggleTest(el) {
    var id     = el.getAttribute('data-id');
    var name   = el.getAttribute('data-name');
    var amount = parseFloat(el.getAttribute('data-amount'));

    if (selectedTests[id]) {
        delete selectedTests[id];
        el.classList.remove('selected');
        el.querySelector('.ti-check svg').style.display = 'none';
    } else {
        selectedTests[id] = { id: id, name: name, amount: amount };
        el.classList.add('selected');
        el.querySelector('.ti-check svg').style.display = 'block';
    }

    updateSummary();
}

/* ===========================================
   TEST SEARCH FILTER
=========================================== */
document.getElementById('testSearch').addEventListener('input', function() {
    var q = this.value.toLowerCase();
    document.querySelectorAll('.test-item').forEach(function(el) {
        var name = el.getAttribute('data-name').toLowerCase();
        el.style.display = name.indexOf(q) !== -1 ? '' : 'none';
    });
});

/* ===========================================
   UPDATE SUMMARY
=========================================== */
function updateSummary() {
    var keys = Object.keys(selectedTests);
    var subtotal = 0;

    // Build selected tests list
    var listHtml = '';
    if (keys.length === 0) {
        listHtml = '<p style="text-align:center;padding:20px 0;color:var(--text-muted);font-size:13px;">No tests selected yet</p>';
    } else {
        listHtml = '<div style="max-height:200px;overflow-y:auto;">';
        keys.forEach(function(k) {
            var t = selectedTests[k];
            subtotal += t.amount;
            listHtml += '<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:13px;">' +
                        '<span style="color:var(--text-main);font-weight:500;">' + t.name + '</span>' +
                        '<span style="color:var(--primary);font-weight:600;">N' + t.amount.toLocaleString(undefined, {minimumFractionDigits:2}) + '</span>' +
                        '</div>';
        });
        listHtml += '</div>';
    }

    document.getElementById('selectedTestsList').innerHTML = listHtml;

    // Discount
    var discountPct = 0;
    var patientType = document.querySelector('input[name="patient_type"]:checked').value;

    if (patientType === 'registered' && selectedPatient && selectedPatient.discount_fee) {
        discountPct = parseFloat(selectedPatient.discount_fee);
    }

    var discountAmt = (subtotal * discountPct) / 100;
    var net = subtotal - discountAmt;

    document.getElementById('summarySubtotal').textContent = 'N' + subtotal.toLocaleString(undefined, {minimumFractionDigits:2});
    document.getElementById('discountPct').textContent = discountPct;
    document.getElementById('summaryDiscount').textContent = '- N' + discountAmt.toLocaleString(undefined, {minimumFractionDigits:2});
    document.getElementById('summaryNet').textContent = 'N' + net.toLocaleString(undefined, {minimumFractionDigits:2});

    // Running total bar
    var totalEl = document.getElementById('runningTotal');
    if (keys.length > 0) {
        totalEl.style.display = 'flex';
        document.getElementById('testCount').textContent = keys.length;
        document.getElementById('runningAmount').textContent = 'N' + subtotal.toLocaleString(undefined, {minimumFractionDigits:2});
    } else {
        totalEl.style.display = 'none';
    }

    // Enable/disable button
    document.getElementById('btnComplete').disabled = (keys.length === 0);
}

/* ===========================================
   COMPLETE ORDER
=========================================== */
function completeOrder() {
    var patientType = document.querySelector('input[name="patient_type"]:checked').value;
    var testsArr = [];
    Object.keys(selectedTests).forEach(function(k) {
        testsArr.push({ id: selectedTests[k].id, name: selectedTests[k].name });
    });

    // Validation
    if (testsArr.length === 0) {
        swal('Error', 'Please select at least one test', 'warning');
        return;
    }

    if (patientType === 'registered' && !selectedPatient) {
        swal('Error', 'Please select a registered patient', 'warning');
        return;
    }

    if (patientType === 'walkin' && document.getElementById('walkinName').value.trim() === '') {
        swal('Error', 'Please enter walk-in patient name', 'warning');
        return;
    }

    var payload = {
        patient_type:   patientType,
        patient_id:     selectedPatient ? selectedPatient.id : 0,
        walkin_name:    document.getElementById('walkinName').value.trim(),
        walkin_phone:   document.getElementById('walkinPhone').value.trim(),
        tests:          testsArr,
        clinical_notes: document.getElementById('clinicalNotes').value.trim(),
        payment_method: document.getElementById('paymentMethod').value
    };

    // Confirm
    swal({
        title: 'Confirm Order',
        text: 'Process ' + testsArr.length + ' test(s)? Payment will be marked as PAID.',
        icon: 'info',
        buttons: ['Cancel', 'Yes, Process']
    }).then(function(ok) {
        if (!ok) return;

        document.getElementById('btnComplete').disabled = true;
        document.getElementById('btnComplete').textContent = 'Processing...';

        $.ajax({
            url: 'process_test.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    swal('Success', 'Receipt: ' + res.receipt_num + '\nTests sent to lab workflow.', 'success')
                    .then(function() {
                        // Reset form
                        clearPatient();
                        selectedTests = {};
                        document.querySelectorAll('.test-item.selected').forEach(function(el) {
                            el.classList.remove('selected');
                            el.querySelector('.ti-check svg').style.display = 'none';
                        });
                        document.getElementById('clinicalNotes').value = '';
                        document.getElementById('walkinName').value = '';
                        document.getElementById('walkinPhone').value = '';
                        updateSummary();
                    });
                } else {
                    swal('Error', res.message || 'Failed to process order', 'error');
                }
                document.getElementById('btnComplete').disabled = false;
                document.getElementById('btnComplete').textContent = 'Complete Order';
            },
            error: function() {
                swal('Error', 'Network error. Please try again.', 'error');
                document.getElementById('btnComplete').disabled = false;
                document.getElementById('btnComplete').textContent = 'Complete Order';
            }
        });
    });
}
</script>

</body>
</html>

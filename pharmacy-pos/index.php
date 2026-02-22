<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 && $_SESSION['type'] != 6)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'pharmacy';

// Load all active drugs with stock
$drugsSql = "
    SELECT
        d.id,
        d.drug_name,
        d.dosage_form,
        d.strength,
        d.selling_price,
        COALESCE(ps.quantity, 0) AS stock_qty
    FROM drugs d
    LEFT JOIN pharmacy_stock ps ON ps.drug = d.id
    WHERE d.status = 1
    ORDER BY d.drug_name ASC
";
$drugsResult = $db->query($drugsSql);
$allDrugs = [];
while ($row = $drugsResult->fetch_assoc()) {
    $allDrugs[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= APP_NAME ?> | Pharmacy POS</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../styles/styles.css">
<style>
/* POS Layout */
.pos-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.pos-full-width {
    grid-column: 1 / -1;
}

/* Patient Type Toggle */
.patient-type-toggle {
    display: flex;
    background: #f1f5f9;
    border-radius: 10px;
    padding: 4px;
    margin-bottom: 20px;
}

.patient-type-toggle label {
    flex: 1;
    text-align: center;
    padding: 10px 16px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    color: #64748b;
    transition: all 0.3s ease;
}

.patient-type-toggle input[type="radio"] {
    display: none;
}

.patient-type-toggle input[type="radio"]:checked + label {
    background: var(--primary, #2563eb);
    color: white;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
}

/* Patient Search */
.patient-search-wrapper {
    position: relative;
}

.patient-search-input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
}

.patient-search-input:focus {
    outline: none;
    border-color: var(--primary, #2563eb);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.search-results-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 8px 8px;
    max-height: 250px;
    overflow-y: auto;
    z-index: 100;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    display: none;
}

.search-result-item {
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s;
}

.search-result-item:hover {
    background: #f8fafc;
}

.search-result-item:last-child {
    border-bottom: none;
}

.search-result-name {
    font-weight: 600;
    color: #1e293b;
    font-size: 14px;
}

.search-result-meta {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

/* Selected Patient Card */
.selected-patient-card {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #bfdbfe;
    border-radius: 10px;
    padding: 16px;
    display: none;
}

.selected-patient-card .patient-name {
    font-weight: 700;
    font-size: 16px;
    color: #1e40af;
    margin-bottom: 4px;
}

.selected-patient-card .patient-meta {
    font-size: 13px;
    color: #3b82f6;
}

.selected-patient-card .scheme-badge {
    display: inline-block;
    background: #2563eb;
    color: white;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    margin-top: 6px;
}

.btn-clear-patient {
    background: none;
    border: none;
    color: #ef4444;
    font-size: 13px;
    cursor: pointer;
    float: right;
    font-weight: 600;
}

.btn-clear-patient:hover {
    text-decoration: underline;
}

/* Walk-in Fields */
.walkin-fields {
    display: none;
}

.walkin-fields .form-group {
    margin-bottom: 16px;
}

.walkin-fields input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    box-sizing: border-box;
}

.walkin-fields input:focus {
    outline: none;
    border-color: var(--primary, #2563eb);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

/* Drug Grid */
.drug-search-input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 16px;
    box-sizing: border-box;
}

.drug-search-input:focus {
    outline: none;
    border-color: var(--primary, #2563eb);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.drug-grid {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}

.drug-item {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid #f1f5f9;
    gap: 12px;
    transition: background 0.2s;
}

.drug-item:hover {
    background: #f8fafc;
}

.drug-item:last-child {
    border-bottom: none;
}

.drug-item input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--primary, #2563eb);
    flex-shrink: 0;
}

.drug-info {
    flex: 1;
    min-width: 0;
}

.drug-info-name {
    font-weight: 600;
    font-size: 14px;
    color: #1e293b;
}

.drug-info-details {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

.drug-info-price {
    font-weight: 700;
    color: var(--primary, #2563eb);
    font-size: 14px;
    white-space: nowrap;
}

.drug-stock-badge {
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.drug-stock-badge.in-stock {
    background: #dcfce7;
    color: #166534;
}

.drug-stock-badge.out-of-stock {
    background: #fee2e2;
    color: #991b1b;
}

/* Drug qty/prescription inputs */
.drug-qty-inputs {
    display: none;
    padding: 8px 16px 12px 46px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    gap: 12px;
}

.drug-qty-inputs.active {
    display: flex;
    flex-wrap: wrap;
}

.drug-qty-inputs .form-group {
    margin: 0;
}

.drug-qty-inputs input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 13px;
}

.drug-qty-inputs input:focus {
    outline: none;
    border-color: var(--primary, #2563eb);
}

.drug-qty-inputs input[type="number"] {
    width: 80px;
}

.drug-qty-inputs input[type="text"] {
    width: 200px;
}

/* Running Total */
.running-total {
    padding: 14px 16px;
    background: #f1f5f9;
    border-top: 2px solid #e2e8f0;
    font-weight: 700;
    font-size: 16px;
    color: #1e293b;
    display: flex;
    justify-content: space-between;
    border-radius: 0 0 8px 8px;
}

/* Payment Summary */
.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    font-size: 15px;
    border-bottom: 1px solid #f1f5f9;
}

.summary-row:last-of-type {
    border-bottom: none;
}

.summary-row.total-row {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary, #2563eb);
    padding: 16px 0 8px;
    border-top: 2px solid #e2e8f0;
}

.summary-label {
    color: #64748b;
}

.summary-value {
    font-weight: 600;
    color: #1e293b;
}

.discount-info {
    font-size: 12px;
    color: #16a34a;
    font-weight: 500;
}

.payment-method-select {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    margin: 16px 0;
    background: white;
}

.payment-method-select:focus {
    outline: none;
    border-color: var(--primary, #2563eb);
}

.btn-complete-sale {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    letter-spacing: 0.5px;
}

.btn-complete-sale:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4);
}

.btn-complete-sale:disabled {
    background: #94a3b8;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Header Actions */
.pos-header-actions {
    display: flex;
    gap: 12px;
    align-items: center;
}

.pos-header-actions a {
    text-decoration: none;
}

/* Responsive */
@media (max-width: 900px) {
    .pos-container {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .drug-qty-inputs {
        padding-left: 16px;
        flex-direction: column;
    }

    .drug-qty-inputs input[type="text"] {
        width: 100%;
    }
}
</style>
</head>
<body>

<?php include '../includes/side_nav.php'; ?>

<main class="main-content">
<?php include '../includes/header.php'; ?>

<div class="content-scroll">

    <div class="view-header">
        <div>
            <h1>Pharmacy POS</h1>
            <p>Point of Sale - Drug Dispensing</p>
        </div>
        <div class="pos-header-actions">
            <a href="sales_history.php" class="btn-secondary" style="padding:10px 20px;border-radius:10px;font-weight:600;font-size:14px;">
                Sales History
            </a>
        </div>
    </div>

    <div class="pos-container">

        <!-- ============================================ -->
        <!-- PATIENT SELECTION CARD -->
        <!-- ============================================ -->
        <div class="card">
            <div class="card-header">
                <h2>Patient Selection</h2>
            </div>
            <div class="card-body" style="padding: 24px;">

                <div class="patient-type-toggle">
                    <input type="radio" name="patient_type" id="typeRegistered" value="registered" checked>
                    <label for="typeRegistered" onclick="switchPatientType('registered')">Registered Patient</label>
                    <input type="radio" name="patient_type" id="typeWalkin" value="walkin">
                    <label for="typeWalkin" onclick="switchPatientType('walkin')">Walk-In Customer</label>
                </div>

                <!-- Registered Patient Search -->
                <div id="registeredSection">
                    <div class="patient-search-wrapper">
                        <input type="text"
                               id="patientSearch"
                               class="patient-search-input"
                               placeholder="Search by name, hospital number, or phone..."
                               oninput="searchPatient()"
                               autocomplete="off">
                        <div id="searchResultsDropdown" class="search-results-dropdown"></div>
                    </div>

                    <div id="selectedPatientCard" class="selected-patient-card" style="margin-top: 16px;">
                        <button class="btn-clear-patient" onclick="clearPatient()">Clear</button>
                        <div class="patient-name" id="selPatientName"></div>
                        <div class="patient-meta" id="selPatientMeta"></div>
                        <span class="scheme-badge" id="selPatientScheme"></span>
                    </div>
                </div>

                <!-- Walk-In Customer Fields -->
                <div id="walkinSection" class="walkin-fields">
                    <div class="form-group">
                        <label style="font-weight:600;margin-bottom:6px;display:block;">Customer Name *</label>
                        <input type="text" id="walkinName" placeholder="Enter customer name">
                    </div>
                    <div class="form-group">
                        <label style="font-weight:600;margin-bottom:6px;display:block;">Phone Number</label>
                        <input type="text" id="walkinPhone" placeholder="Enter phone number">
                    </div>
                </div>

            </div>
        </div>

        <!-- ============================================ -->
        <!-- PAYMENT SUMMARY CARD -->
        <!-- ============================================ -->
        <div class="card">
            <div class="card-header">
                <h2>Payment Summary</h2>
            </div>
            <div class="card-body" style="padding: 24px;">

                <div class="summary-row">
                    <span class="summary-label">Subtotal</span>
                    <span class="summary-value" id="summarySubtotal">0.00</span>
                </div>

                <div class="summary-row">
                    <span class="summary-label">
                        Discount <span class="discount-info" id="discountInfo"></span>
                    </span>
                    <span class="summary-value" id="summaryDiscount" style="color: #16a34a;">-0.00</span>
                </div>

                <div class="summary-row total-row">
                    <span>Net Amount</span>
                    <span id="summaryNet">0.00</span>
                </div>

                <div class="form-group" style="margin-top: 16px;">
                    <label style="font-weight:600;margin-bottom:6px;display:block;">Payment Method</label>
                    <select id="paymentMethod" class="payment-method-select">
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                        <option value="Transfer">Transfer</option>
                        <option value="POS">POS</option>
                    </select>
                </div>

                <button class="btn-complete-sale" id="btnCompleteSale" onclick="completeSale()">
                    Complete Sale
                </button>

                <div style="margin-top:16px; text-align:center;">
                    <span id="selectedDrugsCount" style="font-size:13px; color:#64748b;">No drugs selected</span>
                </div>

            </div>
        </div>

        <!-- ============================================ -->
        <!-- DRUG SELECTION CARD (Full Width) -->
        <!-- ============================================ -->
        <div class="card pos-full-width">
            <div class="card-header">
                <h2>Drug Selection</h2>
                <span style="font-size:13px;color:#64748b;" id="drugCountLabel"><?= count($allDrugs) ?> drugs available</span>
            </div>
            <div class="card-body" style="padding: 24px;">

                <input type="text"
                       id="drugSearchInput"
                       class="drug-search-input"
                       placeholder="Search drugs by name, dosage form, or strength..."
                       oninput="filterDrugs()">

                <div class="drug-grid" id="drugGrid">
                    <?php foreach ($allDrugs as $drug): ?>
                    <div class="drug-item-wrapper" data-drug-name="<?= strtolower(htmlspecialchars($drug['drug_name'])) ?>" data-dosage="<?= strtolower(htmlspecialchars($drug['dosage_form'])) ?>" data-strength="<?= strtolower(htmlspecialchars($drug['strength'])) ?>">
                        <div class="drug-item">
                            <input type="checkbox"
                                   id="drug_<?= $drug['id'] ?>"
                                   data-id="<?= $drug['id'] ?>"
                                   data-name="<?= htmlspecialchars($drug['drug_name']) ?>"
                                   data-price="<?= $drug['selling_price'] ?>"
                                   data-stock="<?= $drug['stock_qty'] ?>"
                                   onchange="toggleDrug(this)"
                                   <?= $drug['stock_qty'] <= 0 ? 'disabled' : '' ?>>
                            <div class="drug-info">
                                <div class="drug-info-name"><?= htmlspecialchars($drug['drug_name']) ?></div>
                                <div class="drug-info-details">
                                    <?= htmlspecialchars($drug['dosage_form']) ?>
                                    <?= !empty($drug['strength']) ? ' | ' . htmlspecialchars($drug['strength']) : '' ?>
                                </div>
                            </div>
                            <div class="drug-info-price">&#8358;<?= number_format($drug['selling_price'], 2) ?></div>
                            <span class="drug-stock-badge <?= $drug['stock_qty'] > 0 ? 'in-stock' : 'out-of-stock' ?>">
                                <?= $drug['stock_qty'] > 0 ? 'Qty: ' . $drug['stock_qty'] : 'Out of Stock' ?>
                            </span>
                        </div>
                        <div class="drug-qty-inputs" id="qtyInputs_<?= $drug['id'] ?>">
                            <div class="form-group">
                                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Qty *</label>
                                <input type="number"
                                       id="qty_<?= $drug['id'] ?>"
                                       min="1"
                                       max="<?= $drug['stock_qty'] ?>"
                                       value="1"
                                       oninput="updateTotal()">
                            </div>
                            <div class="form-group">
                                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px;">Prescription</label>
                                <input type="text"
                                       id="presc_<?= $drug['id'] ?>"
                                       placeholder="e.g., 1 tab 3x daily">
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="running-total">
                    <span>Running Total:</span>
                    <span id="runningTotal">&#8358;0.00</span>
                </div>

            </div>
        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>
</main>

<script>
// State
let selectedPatient = null;
let patientType = 'registered';
let searchTimeout = null;

// ========================
// Patient Type Toggle
// ========================
function switchPatientType(type) {
    patientType = type;
    if (type === 'registered') {
        document.getElementById('registeredSection').style.display = 'block';
        document.getElementById('walkinSection').style.display = 'none';
    } else {
        document.getElementById('registeredSection').style.display = 'none';
        document.getElementById('walkinSection').style.display = 'block';
        selectedPatient = null;
        document.getElementById('selectedPatientCard').style.display = 'none';
    }
    updateTotal();
}

// ========================
// Patient Search (Debounced)
// ========================
function searchPatient() {
    clearTimeout(searchTimeout);
    var q = document.getElementById('patientSearch').value.trim();
    var dropdown = document.getElementById('searchResultsDropdown');

    if (q.length < 1) {
        dropdown.style.display = 'none';
        return;
    }

    searchTimeout = setTimeout(function() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'search_patient.php?q=' + encodeURIComponent(q), true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var results = JSON.parse(xhr.responseText);
                if (results.length === 0) {
                    dropdown.innerHTML = '<div class="search-result-item" style="color:#94a3b8;cursor:default;">No patients found</div>';
                } else {
                    var html = '';
                    for (var i = 0; i < results.length; i++) {
                        var p = results[i];
                        html += '<div class="search-result-item" onclick=\'selectPatient(' + JSON.stringify(p) + ')\'>';
                        html += '<div class="search-result-name">' + p.name + '</div>';
                        html += '<div class="search-result-meta">' + p.hospital_num + ' | ' + p.phone + ' | ' + p.scheme_name + ' (' + p.discount_fee + '% discount)</div>';
                        html += '</div>';
                    }
                    dropdown.innerHTML = html;
                }
                dropdown.style.display = 'block';
            }
        };
        xhr.send();
    }, 300);
}

// ========================
// Select Patient
// ========================
function selectPatient(patient) {
    selectedPatient = patient;

    document.getElementById('selPatientName').textContent = patient.name;
    document.getElementById('selPatientMeta').textContent = patient.hospital_num + ' | ' + patient.phone;
    document.getElementById('selPatientScheme').textContent = patient.scheme_name + ' (' + patient.discount_fee + '% discount)';

    document.getElementById('selectedPatientCard').style.display = 'block';
    document.getElementById('searchResultsDropdown').style.display = 'none';
    document.getElementById('patientSearch').value = '';

    updateTotal();
}

function clearPatient() {
    selectedPatient = null;
    document.getElementById('selectedPatientCard').style.display = 'none';
    document.getElementById('patientSearch').value = '';
    updateTotal();
}

// ========================
// Filter Drugs
// ========================
function filterDrugs() {
    var search = document.getElementById('drugSearchInput').value.toLowerCase();
    var items = document.querySelectorAll('.drug-item-wrapper');
    var visible = 0;

    for (var i = 0; i < items.length; i++) {
        var name = items[i].getAttribute('data-drug-name');
        var dosage = items[i].getAttribute('data-dosage');
        var strength = items[i].getAttribute('data-strength');

        if (name.indexOf(search) > -1 || dosage.indexOf(search) > -1 || strength.indexOf(search) > -1) {
            items[i].style.display = '';
            visible++;
        } else {
            items[i].style.display = 'none';
        }
    }

    document.getElementById('drugCountLabel').textContent = visible + ' drugs shown';
}

// ========================
// Toggle Drug Selection
// ========================
function toggleDrug(checkbox) {
    var drugId = checkbox.getAttribute('data-id');
    var qtySection = document.getElementById('qtyInputs_' + drugId);

    if (checkbox.checked) {
        qtySection.classList.add('active');
        document.getElementById('qty_' + drugId).value = 1;
    } else {
        qtySection.classList.remove('active');
        document.getElementById('qty_' + drugId).value = 1;
        document.getElementById('presc_' + drugId).value = '';
    }

    updateTotal();
}

// ========================
// Update Totals
// ========================
function updateTotal() {
    var checkboxes = document.querySelectorAll('#drugGrid input[type="checkbox"]:checked');
    var subtotal = 0;
    var count = 0;

    for (var i = 0; i < checkboxes.length; i++) {
        var drugId = checkboxes[i].getAttribute('data-id');
        var price = parseFloat(checkboxes[i].getAttribute('data-price'));
        var qty = parseInt(document.getElementById('qty_' + drugId).value) || 1;
        subtotal += price * qty;
        count++;
    }

    // Discount
    var discountPercent = 0;
    var schemeName = '';
    if (patientType === 'registered' && selectedPatient) {
        discountPercent = parseFloat(selectedPatient.discount_fee) || 0;
        schemeName = selectedPatient.scheme_name || '';
    }

    var discountAmount = (subtotal * discountPercent) / 100;
    var netAmount = Math.max(0, subtotal - discountAmount);

    // Update display
    document.getElementById('runningTotal').innerHTML = '&#8358;' + subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('summarySubtotal').textContent = subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('summaryDiscount').textContent = '-' + discountAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    document.getElementById('summaryNet').textContent = netAmount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    if (discountPercent > 0) {
        document.getElementById('discountInfo').textContent = '(' + schemeName + ' ' + discountPercent + '%)';
    } else {
        document.getElementById('discountInfo').textContent = '';
    }

    document.getElementById('selectedDrugsCount').textContent = count > 0 ? count + ' drug(s) selected' : 'No drugs selected';
}

// ========================
// Complete Sale
// ========================
function completeSale() {
    var checkboxes = document.querySelectorAll('#drugGrid input[type="checkbox"]:checked');

    if (checkboxes.length === 0) {
        swal('Warning', 'Please select at least one drug', 'warning');
        return;
    }

    // Validate patient
    if (patientType === 'registered' && !selectedPatient) {
        swal('Warning', 'Please select a registered patient', 'warning');
        return;
    }

    if (patientType === 'walkin') {
        var walkinName = document.getElementById('walkinName').value.trim();
        if (!walkinName) {
            swal('Warning', 'Please enter the walk-in customer name', 'warning');
            return;
        }
    }

    // Build drugs array
    var drugs = [];
    for (var i = 0; i < checkboxes.length; i++) {
        var drugId = checkboxes[i].getAttribute('data-id');
        var stock = parseInt(checkboxes[i].getAttribute('data-stock'));
        var qty = parseInt(document.getElementById('qty_' + drugId).value) || 1;
        var prescription = document.getElementById('presc_' + drugId).value.trim();

        if (qty <= 0) {
            swal('Warning', 'Invalid quantity for ' + checkboxes[i].getAttribute('data-name'), 'warning');
            return;
        }

        if (qty > stock) {
            swal('Warning', 'Quantity exceeds stock for ' + checkboxes[i].getAttribute('data-name') + ' (Available: ' + stock + ')', 'warning');
            return;
        }

        drugs.push({
            id: drugId,
            quantity: qty,
            prescription: prescription
        });
    }

    // Build payload
    var payload = {
        patient_type: patientType,
        patient_id: patientType === 'registered' ? selectedPatient.id : 0,
        walkin_name: patientType === 'walkin' ? document.getElementById('walkinName').value.trim() : '',
        walkin_phone: patientType === 'walkin' ? document.getElementById('walkinPhone').value.trim() : '',
        drugs: drugs,
        payment_method: document.getElementById('paymentMethod').value
    };

    // Disable button
    var btn = document.getElementById('btnCompleteSale');
    btn.disabled = true;
    btn.textContent = 'Processing...';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'process_sale.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            btn.disabled = false;
            btn.textContent = 'Complete Sale';

            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    swal('Sale Complete!', 'Receipt: ' + response.receipt_num, 'success', {
                        buttons: {
                            receipt: {
                                text: "View Receipt",
                                value: "receipt"
                            },
                            ok: {
                                text: "New Sale",
                                value: "new"
                            }
                        }
                    }).then(function(value) {
                        if (value === 'receipt') {
                            window.location.href = '../reciept/index.php?id=' + response.payment_id;
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    swal('Error', response.message, 'error');
                }
            } else {
                swal('Error', 'Server error occurred', 'error');
            }
        }
    };
    xhr.send(JSON.stringify(payload));
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    var dropdown = document.getElementById('searchResultsDropdown');
    var searchInput = document.getElementById('patientSearch');
    if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
        dropdown.style.display = 'none';
    }
});
</script>

</body>
</html>

<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 9)) {
    $_SESSION['error'] = 'Login To Continue';
    echo "<script>window.location.href='../login/index.php'</script>";
    exit;
}

$location = 'radiology';

// Fetch all active scans
$allScans = [];
$run = $db->query("SELECT id, name, modality, body_part, amount FROM scans WHERE status = 1 ORDER BY name ASC");
if ($run) while ($row = $run->fetch_assoc()) $allScans[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | Radiology - Walk-in Scans</title>
    <link rel="stylesheet" href="../styles/styles.css">
    <style>
        .pos-container { max-width: 900px; margin: 0 auto; }
        .patient-toggle { display: flex; gap: 12px; margin-bottom: 20px; }
        .patient-toggle label { flex: 1; text-align: center; padding: 12px; border: 2px solid #e5e7eb; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.2s; }
        .patient-toggle input { display: none; }
        .patient-toggle input:checked + span { background: var(--primary); color: white; border-color: var(--primary); display: block; border-radius: 8px; padding: 10px; }
        .patient-toggle label:has(input:checked) { border-color: var(--primary); background: #eff6ff; }
        .search-results { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e5e7eb; border-radius: 8px; max-height: 200px; overflow-y: auto; z-index: 10; box-shadow: 0 4px 12px rgba(0,0,0,0.1); display: none; }
        .search-results .result-item { padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .search-results .result-item:hover { background: #f0f9ff; }
        .selected-patient-card { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px; margin-top: 12px; }
        .scan-item-row { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-bottom: 1px solid #f3f4f6; cursor: pointer; transition: background 0.15s; }
        .scan-item-row:hover { background: #f9fafb; }
        .scan-item-row input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; flex-shrink: 0; }
        .scan-item-info { flex: 1; }
        .scan-item-info strong { font-size: 14px; color: #111827; }
        .scan-item-info small { font-size: 12px; color: #6b7280; }
        .summary-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 15px; }
        .summary-row.total { border-top: 2px solid #e5e7eb; padding-top: 12px; margin-top: 8px; font-weight: 700; font-size: 18px; }
    </style>
</head>
<body>
<?php include '../includes/side_nav.php'; ?>
<main class="main-content">
<?php include '../includes/header.php'; ?>
<div class="content-scroll">
    <div class="pos-container">
        <div class="view-header">
            <div>
                <h1>Radiology - Walk-in Scans</h1>
                <p>Order scans for walk-in or registered patients</p>
            </div>
            <a href="../radiology/index.php" class="btn-secondary">Scan Tracker</a>
        </div>

        <!-- Patient Selection -->
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h2>Patient Selection</h2></div>
            <div class="card-body" style="padding:24px;">
                <div class="patient-toggle">
                    <label>
                        <input type="radio" name="patient_type" value="registered" checked onchange="switchPatientType('registered')">
                        <span>Registered Patient</span>
                    </label>
                    <label>
                        <input type="radio" name="patient_type" value="walkin" onchange="switchPatientType('walkin')">
                        <span>Walk-In Patient</span>
                    </label>
                </div>

                <div id="registeredSection">
                    <div class="form-group" style="position:relative;">
                        <label>Search Patient</label>
                        <input type="text" id="patientSearch" placeholder="Search by name, hospital number, or phone..." oninput="searchPatient()">
                        <div class="search-results" id="searchResults"></div>
                    </div>
                    <div id="selectedPatient" style="display:none;"></div>
                </div>

                <div id="walkinSection" style="display:none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Patient Name *</label>
                            <input type="text" id="walkinName" placeholder="Full name">
                        </div>
                        <div class="form-group">
                            <label>Phone (Optional)</label>
                            <input type="text" id="walkinPhone" placeholder="Phone number">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scan Selection -->
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h2>Select Scans</h2></div>
            <div class="card-body" style="padding:24px;">
                <div class="form-group">
                    <input type="text" id="scanSearchInput" placeholder="Search scans..." oninput="filterScans()">
                </div>
                <div style="max-height:300px;overflow-y:auto;border:1px solid #e5e7eb;border-radius:8px;">
                    <?php foreach ($allScans as $scan): ?>
                    <label class="scan-item-row scan-row" data-name="<?= strtolower($scan['name'] . ' ' . $scan['modality'] . ' ' . $scan['body_part']) ?>">
                        <input type="checkbox" class="scan-check" data-id="<?= $scan['id'] ?>" data-name="<?= htmlspecialchars($scan['name']) ?>" data-price="<?= $scan['amount'] ?>" onchange="updateTotal()">
                        <div class="scan-item-info">
                            <strong><?= htmlspecialchars($scan['name']) ?></strong><br>
                            <small><?= $scan['modality'] ?> <?= $scan['body_part'] ? '- ' . htmlspecialchars($scan['body_part']) : '' ?> | &#8358;<?= number_format($scan['amount'], 2) ?></small>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label>Clinical Information (Optional)</label>
                    <textarea id="clinicalInfo" rows="2" placeholder="Relevant clinical information..."></textarea>
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header"><h2>Payment Summary</h2></div>
            <div class="card-body" style="padding:24px;">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotalDisplay">&#8358;0.00</span>
                </div>
                <div class="summary-row" id="discountRow" style="display:none;">
                    <span>Discount (<span id="discountLabel">0%</span>):</span>
                    <span id="discountDisplay" style="color:#dc2626;">-&#8358;0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Net Amount:</span>
                    <span id="netDisplay" style="color:var(--primary);">&#8358;0.00</span>
                </div>

                <div class="form-group" style="margin-top:20px;">
                    <label>Payment Method *</label>
                    <select id="paymentMethod">
                        <option value="">-- Select --</option>
                        <option value="Cash">Cash</option>
                        <option value="Card">Card</option>
                        <option value="Transfer">Transfer</option>
                        <option value="POS">POS</option>
                    </select>
                </div>

                <button class="btn-primary" style="width:100%;padding:14px;font-size:16px;margin-top:12px;" onclick="completeSale()" id="completeSaleBtn">
                    Complete Scan Order
                </button>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
</main>

<script>
let selectedPatientId = 0;
let selectedDiscount = 0;
let searchTimeout = null;

function switchPatientType(type) {
    document.getElementById('registeredSection').style.display = type === 'registered' ? 'block' : 'none';
    document.getElementById('walkinSection').style.display = type === 'walkin' ? 'block' : 'none';
    if (type === 'walkin') { selectedPatientId = 0; selectedDiscount = 0; }
    updateTotal();
}

function searchPatient() {
    clearTimeout(searchTimeout);
    const q = document.getElementById('patientSearch').value.trim();
    if (q.length < 2) { document.getElementById('searchResults').style.display = 'none'; return; }
    searchTimeout = setTimeout(() => {
        fetch('search_patient.php?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('searchResults');
                container.innerHTML = '';
                if (data.length === 0) {
                    container.innerHTML = '<div class="result-item" style="color:#6b7280;">No patients found</div>';
                } else {
                    data.forEach(p => {
                        container.innerHTML += '<div class="result-item" onclick=\'selectPatient(' + JSON.stringify(p) + ')\'><strong>' + p.name + '</strong> - ' + p.hospital_num + ' | ' + p.phone + (p.discount_fee > 0 ? ' <small style="color:#2563eb;">(' + p.scheme_name + ' ' + p.discount_fee + '% off)</small>' : '') + '</div>';
                    });
                }
                container.style.display = 'block';
            });
    }, 300);
}

function selectPatient(patient) {
    selectedPatientId = patient.id;
    selectedDiscount = patient.discount_fee;
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('patientSearch').value = patient.name;
    document.getElementById('selectedPatient').style.display = 'block';
    document.getElementById('selectedPatient').innerHTML = '<div class="selected-patient-card"><strong>' + patient.name + '</strong> | ' + patient.hospital_num + ' | ' + patient.phone + (patient.discount_fee > 0 ? '<br><small style="color:#2563eb;">Scheme: ' + patient.scheme_name + ' (' + patient.discount_fee + '% discount)</small>' : '') + '</div>';
    updateTotal();
}

function filterScans() {
    const q = document.getElementById('scanSearchInput').value.toLowerCase();
    document.querySelectorAll('.scan-row').forEach(row => {
        row.style.display = row.dataset.name.includes(q) ? 'flex' : 'none';
    });
}

function updateTotal() {
    let subtotal = 0;
    document.querySelectorAll('.scan-check:checked').forEach(cb => {
        subtotal += parseFloat(cb.dataset.price);
    });

    const patientType = document.querySelector('input[name="patient_type"]:checked').value;
    const discount = patientType === 'registered' ? selectedDiscount : 0;
    const discountAmt = subtotal * (discount / 100);
    const net = Math.max(0, subtotal - discountAmt);

    document.getElementById('subtotalDisplay').innerHTML = '&#8358;' + subtotal.toLocaleString(undefined, {minimumFractionDigits: 2});
    document.getElementById('netDisplay').innerHTML = '&#8358;' + net.toLocaleString(undefined, {minimumFractionDigits: 2});

    if (discount > 0) {
        document.getElementById('discountRow').style.display = 'flex';
        document.getElementById('discountLabel').textContent = discount + '%';
        document.getElementById('discountDisplay').innerHTML = '-&#8358;' + discountAmt.toLocaleString(undefined, {minimumFractionDigits: 2});
    } else {
        document.getElementById('discountRow').style.display = 'none';
    }
}

function completeSale() {
    const patientType = document.querySelector('input[name="patient_type"]:checked').value;
    const method = document.getElementById('paymentMethod').value;

    if (!method) { swal('Error', 'Please select a payment method', 'error'); return; }

    const selectedScans = [];
    document.querySelectorAll('.scan-check:checked').forEach(cb => {
        selectedScans.push({ id: cb.dataset.id, name: cb.dataset.name });
    });

    if (selectedScans.length === 0) { swal('Error', 'Please select at least one scan', 'error'); return; }

    if (patientType === 'registered' && selectedPatientId <= 0) { swal('Error', 'Please select a patient', 'error'); return; }
    if (patientType === 'walkin' && !document.getElementById('walkinName').value.trim()) { swal('Error', 'Please enter patient name', 'error'); return; }

    const btn = document.getElementById('completeSaleBtn');
    btn.disabled = true;
    btn.textContent = 'Processing...';

    const payload = {
        patient_type: patientType,
        patient_id: selectedPatientId,
        walkin_name: document.getElementById('walkinName').value.trim(),
        walkin_phone: document.getElementById('walkinPhone').value.trim(),
        scans: selectedScans,
        clinical_info: document.getElementById('clinicalInfo').value.trim(),
        payment_method: method
    };

    fetch('process_scan.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            swal('Success', 'Scan order completed! Receipt: ' + data.receipt_num, 'success').then(() => {
                window.location.href = '../reciept/index.php?id=' + data.payment_id;
            });
        } else {
            swal('Error', data.message, 'error');
            btn.disabled = false;
            btn.textContent = 'Complete Scan Order';
        }
    })
    .catch(() => {
        swal('Error', 'Something went wrong', 'error');
        btn.disabled = false;
        btn.textContent = 'Complete Scan Order';
    });
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#patientSearch') && !e.target.closest('#searchResults')) {
        document.getElementById('searchResults').style.display = 'none';
    }
});
</script>
</body>
</html>

alert();



document.getElementById('labTestSearch').addEventListener('keyup', function () {
    let query = this.value.toLowerCase();
    let tests = document.querySelectorAll('#labTestsGrid .checkbox-label');

    tests.forEach(function (label) {
        let name = label.getAttribute('data-name');

        if (name.includes(query)) {
            label.style.display = 'flex';
        } else {
            label.style.display = 'none';
        }
    });
});


// LIVE DRUG SEARCH
document.getElementById('drugSearch').addEventListener('keyup', function () {
    let query = this.value.toLowerCase();

    document.querySelectorAll('#drugGrid .drug-item').forEach(item => {
        let name = item.dataset.name;
        item.style.display = name.includes(query) ? 'flex' : 'none';
    });
});

// ENABLE / DISABLE PRESCRIPTION INPUT
document.querySelectorAll('.drug-checkbox').forEach(cb => {
    cb.addEventListener('change', function () {
        let prescription = this.closest('.drug-item')
                               .querySelector('.drug-prescription');

        prescription.disabled = !this.checked;
        if (!this.checked) prescription.value = '';
    });
});


// Pharmacy Modal Functions
let selectedDrugs = [];
let selectedLabTests = [];

// Open Pharmacy Modal
function sendToPharmacy() {
    // Collect selected drugs from the form
    const drugCheckboxes = document.querySelectorAll('.drug-checkbox:checked');
    selectedDrugs = [];
    
    drugCheckboxes.forEach(checkbox => {
        const drugItem = checkbox.closest('.drug-item');
        const drugId = checkbox.value;
        const drugName = drugItem.querySelector('.checkbox-text').textContent;
        const prescriptionInput = drugItem.querySelector('.drug-prescription');
        const prescription = prescriptionInput.value.trim();
        
        if (!prescription) {
            // Highlight the prescription input if empty
            prescriptionInput.style.borderColor = '#ef4444';
            prescriptionInput.focus();
            alert(`Please enter prescription details for ${drugName}`);
            return;
        }
        
        selectedDrugs.push({
            id: drugId,
            name: drugName,
            prescription: prescription
        });
    });
    
    if (selectedDrugs.length === 0) {
        alert('Please select at least one medication and enter prescription details.');
        return;
    }
    
    // Update modal content
    updatePharmacyModal();
    
    // Show modal
    document.getElementById('pharmacyModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Update Pharmacy Modal Content
function updatePharmacyModal() {
    const selectedDrugsList = document.getElementById('selectedDrugsList');
    const selectedDrugCount = document.getElementById('selectedDrugCount');
    
    // Update count
    selectedDrugCount.textContent = `${selectedDrugs.length} drug${selectedDrugs.length !== 1 ? 's' : ''} selected`;
    
    // Clear current list
    selectedDrugsList.innerHTML = '';
    
    if (selectedDrugs.length === 0) {
        selectedDrugsList.innerHTML = `
            <div class="empty-selection">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                <p>No drugs selected</p>
            </div>
        `;
        return;
    }
    
    // Add each selected drug
selectedDrugs.forEach((drug, index) => {
    const drugElement = document.createElement('div');
    drugElement.className = 'selected-item';
    drugElement.innerHTML = `
        <div class="selected-item-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
        </div>
        <div class="selected-item-details">
            <div class="selected-item-name">${drug.name}</div>
            <div class="selected-item-prescription">${drug.prescription}</div>
            <div class="selected-item-quantity">
                Quantity: 
                <input type="number" class="drug-quantity" value="${drug.quantity || 1}" min="1" 
                       style="width: 60px; margin-left: 5px;"
                       onchange="selectedDrugs[${index}].quantity = parseInt(this.value)">
            </div>
        </div>
        <button class="selected-item-remove" onclick="removeDrugFromModal(${index})">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    `;
    selectedDrugsList.appendChild(drugElement);
});

}

// Remove drug from pharmacy modal
function removeDrugFromModal(index) {
    selectedDrugs.splice(index, 1);
    updatePharmacyModal();
    
    // Also uncheck the corresponding checkbox in the main form
    const drugCheckboxes = document.querySelectorAll('.drug-checkbox');
    drugCheckboxes.forEach(checkbox => {
        if (checkbox.value === selectedDrugs[index]?.id) {
            checkbox.checked = false;
            const prescriptionInput = checkbox.closest('.drug-item').querySelector('.drug-prescription');
            prescriptionInput.disabled = true;
            prescriptionInput.style.borderColor = '';
        }
    });
}

// Close Pharmacy Modal
function closePharmacyModal() {
    document.getElementById('pharmacyModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Confirm Pharmacy Send
function confirmPharmacySend() {
    if (selectedDrugs.length === 0) {
        alert('Please select at least one medication.');
        return;
    }

    const priority = document.querySelector('input[name="pharmacyPriority"]:checked').value;
    const deliveryOption = document.querySelector('input[name="deliveryOption"]:checked').value;
    const notes = document.getElementById('pharmacyNotes').value;

    const appointment_id = document.querySelector('#appointment_id').value;

    const patient_id = document.querySelector('#patient_id').value;

    // Prepare data for AJAX request
    const data = {
        appointment_id:appointment_id,
        patient_id: patient_id,
        drugs: selectedDrugs.map(drug => ({
            id: drug.id,
            prescription: drug.prescription,
            quantity: drug.quantity || 1
        })),
        priority: priority,
        delivery_option: deliveryOption,
        notes: notes,
        doctor_id: '<?= getId() ?>',
        action: 'send_to_pharmacy'
    };


    // alert(data.appointment_id);
    // return;

    // Show loading state
    const confirmBtn = document.querySelector('#pharmacyModal .btn-primary');
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<span class="spinner"></span>Sending...';
    confirmBtn.disabled = true;

    // Send AJAX request to prescribe_drugs.php
    fetch('prescribe_drugs.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status.trim()=='success') {
            showToast('success', 'Prescription Sent', 'Medications have been sent to pharmacy successfully.');

            // Close modal
            closePharmacyModal();

            // Reset form
            document.querySelectorAll('.drug-checkbox:checked').forEach(checkbox => {
                checkbox.checked = false;
                const prescriptionInput = checkbox.closest('.drug-item').querySelector('.drug-prescription');
                prescriptionInput.disabled = true;
                prescriptionInput.value = '';
            });

            // Clear selectedDrugs array
            selectedDrugs = [];
        } else {
            showToast('error', 'Error', result.message || 'Failed to send prescription.');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('error', 'Error', 'An unexpected error occurred.');
    })
    .finally(() => {
        // Reset button
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
    });
}


// Lab Modal Functions
function sendToLab() {
    // Collect selected lab tests from the form
    const labCheckboxes = document.querySelectorAll('#labTestsGrid input[type="checkbox"]:checked');
    selectedLabTests = [];
    
    labCheckboxes.forEach(checkbox => {
        const testId = checkbox.value;
        const testName = checkbox.closest('label').querySelector('.checkbox-text').textContent;
        
        selectedLabTests.push({
            id: testId,
            name: testName
        });
    });
    
    if (selectedLabTests.length === 0) {
        alert('Please select at least one lab test.');
        return;
    }
    
    // Update modal content
    updateLabModal();
    
    // Show modal
    document.getElementById('labModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Update Lab Modal Content
function updateLabModal() {
    const selectedLabList = document.getElementById('selectedLabList');
    const selectedLabCount = document.getElementById('selectedLabCount');
    
    // Update count
    selectedLabCount.textContent = `${selectedLabTests.length} test${selectedLabTests.length !== 1 ? 's' : ''} selected`;
    
    // Clear current list
    selectedLabList.innerHTML = '';
    
    if (selectedLabTests.length === 0) {
        selectedLabList.innerHTML = `
            <div class="empty-selection">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
                <p>No lab tests selected</p>
            </div>
        `;
        return;
    }
    
    // Add each selected test
    selectedLabTests.forEach((test, index) => {
        const testElement = document.createElement('div');
        testElement.className = 'selected-item';
        testElement.innerHTML = `
            <div class="selected-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
            </div>
            <div class="selected-item-details">
                <div class="selected-item-name">${test.name}</div>
            </div>
            <button class="selected-item-remove" onclick="removeTestFromModal(${index})">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;
        selectedLabList.appendChild(testElement);
    });
}

// Remove test from lab modal
function removeTestFromModal(index) {
    const removedTest = selectedLabTests[index];
    selectedLabTests.splice(index, 1);
    updateLabModal();
    
    // Also uncheck the corresponding checkbox in the main form
    const testCheckboxes = document.querySelectorAll('#labTestsGrid input[type="checkbox"]');
    testCheckboxes.forEach(checkbox => {
        if (checkbox.value === removedTest.id) {
            checkbox.checked = false;
        }
    });
}

// Close Lab Modal
function closeLabModal() {
    document.getElementById('labModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Confirm Lab Send
function confirmLabSend() {
    if (selectedLabTests.length === 0) {
        alert('Please select at least one lab test.');
        return;
    }

    const appointment_id = document.querySelector('#appointment_id').value;

    const patient_id = document.querySelector('#patient_id').value;



    const payload = {
        appointment_id:appointment_id,
        patient_id:patient_id,
        doctor_id: "<?= getId() ?>",
        priority: document.querySelector('input[name="labPriority"]:checked').value,
        preferred_date: document.getElementById('preferredDate').value,
        preferred_time: document.getElementById('preferredTime').value,
        notes: document.getElementById('labNotes').value,
        specimen: {
            blood: document.getElementById('collectBlood').checked ? 1 : 0,
            urine: document.getElementById('collectUrine').checked ? 1 : 0,
            fasting: document.getElementById('fastingRequired').checked ? 1 : 0
        },
        tests: selectedLabTests
    };

    const btn = document.querySelector('#labModal .btn-primary');
    btn.disabled = true;
    btn.innerHTML = 'Sending...';

    fetch('send_lab_request.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.status === 'success') {
            showToast('success', 'Success', resp.message);
            closeLabModal();
        } else {
            showToast('error', 'Error', resp.message);
        }
        btn.disabled = false;
        btn.innerHTML = 'Send to Laboratory';
    })
    .catch(() => {
        alert('Network error');
        btn.disabled = false;
        btn.innerHTML = 'Send to Laboratory';
    });
}

// ============ RADIOLOGY SCAN FUNCTIONS ============

let selectedScans = [];

// Scan Search
document.getElementById('scanSearch')?.addEventListener('keyup', function () {
    let query = this.value.toLowerCase();
    document.querySelectorAll('#scanGrid .scan-item').forEach(item => {
        let name = item.dataset.name;
        item.style.display = name.includes(query) ? 'flex' : 'none';
    });
});

function sendToRadiology() {
    const scanCheckboxes = document.querySelectorAll('.scan-checkbox:checked');
    selectedScans = [];

    scanCheckboxes.forEach(checkbox => {
        selectedScans.push({
            id: checkbox.value,
            name: checkbox.dataset.name,
            modality: checkbox.dataset.modality
        });
    });

    if (selectedScans.length === 0) {
        alert('Please select at least one scan.');
        return;
    }

    updateScanModal();
    document.getElementById('scanModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function updateScanModal() {
    const list = document.getElementById('selectedScansList');
    const count = document.getElementById('selectedScanCount');

    count.textContent = selectedScans.length + ' scan' + (selectedScans.length !== 1 ? 's' : '') + ' selected';
    list.innerHTML = '';

    if (selectedScans.length === 0) {
        list.innerHTML = '<div class="empty-selection"><p>No scans selected</p></div>';
        return;
    }

    selectedScans.forEach((scan, index) => {
        const el = document.createElement('div');
        el.className = 'selected-item';
        el.innerHTML = `
            <div class="selected-item-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 16v-4"></path>
                    <path d="M12 8h.01"></path>
                </svg>
            </div>
            <div class="selected-item-details">
                <div class="selected-item-name">${scan.name}</div>
                <div style="font-size:12px;color:#6b7280;">${scan.modality}</div>
            </div>
            <button class="selected-item-remove" onclick="removeScanFromModal(${index})">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;
        list.appendChild(el);
    });
}

function removeScanFromModal(index) {
    const removed = selectedScans[index];
    selectedScans.splice(index, 1);
    updateScanModal();

    document.querySelectorAll('.scan-checkbox').forEach(cb => {
        if (cb.value === removed.id) cb.checked = false;
    });
}

function closeScanModal() {
    document.getElementById('scanModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function confirmScanSend() {
    if (selectedScans.length === 0) {
        alert('Please select at least one scan.');
        return;
    }

    const appointment_id = document.querySelector('#appointment_id').value;
    const patient_id = document.querySelector('#patient_id').value;

    const payload = {
        appointment_id: appointment_id,
        patient_id: patient_id,
        scans: selectedScans,
        priority: document.querySelector('input[name="scanPriority"]:checked').value,
        clinical_info: document.getElementById('scanClinicalInfo').value
    };

    const btn = document.querySelector('#scanModal .btn-primary');
    btn.disabled = true;
    btn.innerHTML = 'Sending...';

    fetch('send_scan_request.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.status === 'success') {
            showToast('success', 'Success', resp.message);
            closeScanModal();
            document.querySelectorAll('.scan-checkbox:checked').forEach(cb => cb.checked = false);
            selectedScans = [];
        } else {
            showToast('error', 'Error', resp.message);
        }
        btn.disabled = false;
        btn.innerHTML = 'Send to Radiology';
    })
    .catch(() => {
        alert('Network error');
        btn.disabled = false;
        btn.innerHTML = 'Send to Radiology';
    });
}

// Toast Notification Function
function showToast(type, title, message) {
    const toastContainer = document.getElementById('toastContainer');
    const toastId = 'toast-' + Date.now();
    
    const icon = type === 'success' ? `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
    ` : `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
    `;
    
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = 'toast';
    toast.innerHTML = `
        <div class="toast-icon">
            ${icon}
        </div>
        <div class="toast-content">
            <h5>${title}</h5>
            <p>${message}</p>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    // Auto-remove toast after 5 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 5000);
}

// Enable prescription input when drug is selected
document.addEventListener('DOMContentLoaded', function() {
    // For drugs
    document.querySelectorAll('.drug-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const prescriptionInput = this.closest('.drug-item').querySelector('.drug-prescription');
            if (this.checked) {
                prescriptionInput.disabled = false;
                prescriptionInput.style.borderColor = '';
                prescriptionInput.focus();
            } else {
                prescriptionInput.disabled = true;
                prescriptionInput.value = '';
            }
        });
    });
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closePharmacyModal();
            closeLabModal();
            closeScanModal();
        }
    });

    // Close modals when clicking outside
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                if (this.id === 'pharmacyModal') closePharmacyModal();
                if (this.id === 'labModal') closeLabModal();
                if (this.id === 'scanModal') closeScanModal();
            }
        });
    });
});

// Add spinner CSS
const spinnerStyle = document.createElement('style');
spinnerStyle.textContent = `
    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
        margin-right: 8px;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(spinnerStyle);
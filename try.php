<!-- PAYMENT MODAL -->
<div id="paymentModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Patient Payment</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="patient-details">
                <!-- Loaded dynamically via JS -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Close</button>
            <button class="btn btn-success" onclick="submitPayment()">Pay</button>
        </div>
    </div>
</div>

<script>
let currentPatientId = null;

function open_modal(patientId) {
    currentPatientId = patientId;
    const modal = document.getElementById('paymentModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    document.querySelector('.patient-details').innerHTML = `<p>Loading details...</p>`;

    fetch(`get_patient_payment_details.php?id=${patientId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayPaymentModal(data);
            } else {
                document.querySelector('.patient-details').innerHTML = `<p>${data.message}</p>`;
            }
        });
}

function displayPaymentModal(data) {
    const patient = data.patient;
    const appointments = data.appointments;
    const items = data.items;
    const lastPayment = data.last_payment;

    let appointmentsHtml = '';
    if (appointments.length > 0) {
        appointmentsHtml = appointments.map(a => `
            <div>
                <strong>Appointment:</strong> ${a.date} - ${a.reason} 
            </div>
        `).join('');
    } else {
        appointmentsHtml = '<p>No appointments found</p>';
    }

    let itemsHtml = '';
    if (items.length > 0) {
        itemsHtml = '<table style="width:100%;border-collapse:collapse;margin-top:10px;">' +
                    '<tr><th>Item</th><th>Type</th><th>Price</th></tr>' +
                    items.map(i => `
                        <tr>
                            <td>${i.name}</td>
                            <td>${i.type}</td>
                            <td>${i.price}</td>
                        </tr>
                    `).join('') +
                    '</table>';
    } else {
        itemsHtml = '<p>No items/drugs/lab tests found</p>';
    }

    let lastPaymentHtml = '';
    if (lastPayment) {
        lastPaymentHtml = `
            <div style="margin-top:10px;padding:10px;border:1px solid #ddd;border-radius:6px;background:#f1f1f1;">
                <strong>Last Payment:</strong> ${lastPayment.net_amount} (${lastPayment.payment_method}) on ${lastPayment.payment_date || lastPayment.record_date}
            </div>
        `;
    }

    document.querySelector('.patient-details').innerHTML = `
        <div class="detail-section">
            <h3>Patient Info</h3>
            <p><strong>Name:</strong> ${patient.name}</p>
            <p><strong>Hospital No:</strong> ${patient.hospital_num}</p>
            <p><strong>Phone:</strong> ${patient.phone}</p>
            <p><strong>Email:</strong> ${patient.email}</p>
        </div>

        <div class="detail-section">
            <h3>Appointments</h3>
            ${appointmentsHtml}
        </div>

        <div class="detail-section">
            <h3>Items / Drugs / Lab Tests</h3>
            ${itemsHtml}
        </div>

        <div class="detail-section">
            <h3>Payment</h3>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <label>Amount</label>
                <input type="number" id="paymentAmount" value="${data.total_amount || 0}" class="form-control">

                <label>Discount</label>
                <input type="number" id="paymentDiscount" value="0" class="form-control">

                <label>Net Amount</label>
                <input type="number" id="paymentNet" value="${data.total_amount || 0}" readonly class="form-control">

                <label>Payment Method</label>
                <select id="paymentMethod" class="form-control">
                    <option value="Cash">Cash</option>
                    <option value="Card">Card</option>
                    <option value="Insurance">Insurance</option>
                </select>
            </div>
            ${lastPaymentHtml}
        </div>
    `;

    // Update net amount on discount change
    const amountInput = document.getElementById('paymentAmount');
    const discountInput = document.getElementById('paymentDiscount');
    const netInput = document.getElementById('paymentNet');

    function updateNet() {
        const amt = parseFloat(amountInput.value) || 0;
        const disc = parseFloat(discountInput.value) || 0;
        netInput.value = Math.max(0, amt - disc);
    }

    amountInput.addEventListener('input', updateNet);
    discountInput.addEventListener('input', updateNet);
}

function submitPayment() {
    const amount = parseFloat(document.getElementById('paymentAmount').value) || 0;
    const discount = parseFloat(document.getElementById('paymentDiscount').value) || 0;
    const net = parseFloat(document.getElementById('paymentNet').value) || 0;
    const method = document.getElementById('paymentMethod').value;

    fetch('save_payment.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({
            patient_id: currentPatientId,
            amount,
            discount,
            net,
            payment_method: method
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Payment successful!');
            closeModal();
            location.reload(); // refresh page to show updated payment
        } else {
            alert('Payment failed: ' + data.message);
        }
    });
}

function closeModal() {
    const modal = document.getElementById('paymentModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// close on click outside
window.onclick = function(event) {
    const modal = document.getElementById('paymentModal');
    if (event.target == modal) closeModal();
}
</script>

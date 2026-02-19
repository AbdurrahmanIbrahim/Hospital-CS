<?php
include '../functions.php';

if (!isLoggedIn() || ($_SESSION['type'] != 0 AND $_SESSION['type'] != 3)) {
    $_SESSION['error'] = 'Unauthorized';
    echo "<script>window.history.back()</script>";
    exit;
}

if (empty($_POST['admission_id']) || empty($_POST['billing_mode'])) {
    $_SESSION['error'] = 'Invalid request';
    echo "<script>window.history.back()</script>";
    exit;
}

$admission_id = intval($_POST['admission_id']);
$billing_mode = sanitize($_POST['billing_mode']);

// Verify admission exists and is active
$admission = $db->query("SELECT * FROM admissions WHERE id = '$admission_id' AND status = 0");
if ($admission->num_rows == 0) {
    $_SESSION['error'] = 'Admission not found or already discharged';
    echo "<script>window.history.back()</script>";
    exit;
}

$added = 0;

if ($billing_mode === 'drug') {
    /* =========================
       DRUG BILLING
    ========================= */
    $drugs = $_POST['drugs'] ?? [];
    foreach ($drugs as $drug_id => $data) {
        if (empty($data['selected'])) continue;
        $drug_id = intval($drug_id);
        $qty = max(1, intval($data['qty'] ?? 1));

        $drug = $db->query("SELECT drug_name, selling_price FROM drugs WHERE id = '$drug_id' AND status = 1");
        if ($drug->num_rows == 0) continue;
        $drug_info = $drug->fetch_assoc();

        $total = $drug_info['selling_price'] * $qty;
        $prescription = sanitize($data['prescription'] ?? '');
        $desc_text = "Drug: " . $drug_info['drug_name'] . " (Qty: $qty)";
        if (!empty($prescription)) {
            $desc_text .= " - " . $prescription;
        }
        $desc = $db->real_escape_string($desc_text);

        $db->query("INSERT INTO admission_billing (admission_id, description, amount, billing_type, paid, created_at)
                    VALUES ('$admission_id', '$desc', '$total', 2, 0, NOW())");
        $added++;
    }

    if ($added > 0) {
        $_SESSION['success'] = "$added drug charge(s) added to billing";
    } else {
        $_SESSION['error'] = 'No drugs selected';
    }

} elseif ($billing_mode === 'lab') {
    /* =========================
       LAB TEST BILLING
    ========================= */
    $tests = $_POST['tests'] ?? [];
    foreach ($tests as $test_id) {
        $test_id = intval($test_id);
        $test = $db->query("SELECT name, amount FROM tests WHERE id = '$test_id' AND status = 1");
        if ($test->num_rows == 0) continue;
        $test_info = $test->fetch_assoc();

        $desc = $db->real_escape_string("Lab: " . $test_info['name']);
        $amount = $test_info['amount'];

        $db->query("INSERT INTO admission_billing (admission_id, description, amount, billing_type, paid, created_at)
                    VALUES ('$admission_id', '$desc', '$amount', 3, 0, NOW())");
        $added++;
    }

    if ($added > 0) {
        $_SESSION['success'] = "$added lab test charge(s) added to billing";
    } else {
        $_SESSION['error'] = 'No lab tests selected';
    }

} elseif ($billing_mode === 'other') {
    /* =========================
       OTHER / MANUAL BILLING
    ========================= */
    $description = sanitize($_POST['description'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);

    if (empty($description) || $amount <= 0) {
        $_SESSION['error'] = 'Description and amount are required';
        header("Location: billing.php?id=$admission_id");
        exit;
    }

    $db->query("INSERT INTO admission_billing (admission_id, description, amount, billing_type, paid, created_at)
                VALUES ('$admission_id', '$description', '$amount', 3, 0, NOW())");
    $_SESSION['success'] = 'Billing item added successfully';

} else {
    $_SESSION['error'] = 'Invalid billing mode';
}

header("Location: billing.php?id=$admission_id");
exit;

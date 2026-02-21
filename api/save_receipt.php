<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = intval($_POST['booking_id']);
    $r_date = $_POST['receipt_date'];
    $mode = $_POST['payment_mode'];
    $inst = mysqli_real_escape_string($conn, $_POST['instrument_no']);
    $bank = mysqli_real_escape_string($conn, $_POST['bank_name']);
    $rent = floatval($_POST['amount_rent']);
    $rsd = floatval($_POST['amount_rsd']);
    $total = $rent + $rsd;

    // VALIDATION: Reject empty/zero payments
    if ($total <= 0) {
        die("<script>alert('Error: Payment amount cannot be zero or empty.'); window.history.back();</script>");
    }
    
    // Generate unique Receipt No (e.g., RCPT-2026-0001)
    $year = date('Y');
    $count_res = $conn->query("SELECT COUNT(id) as total FROM vms_receipts");
    $next_id = $count_res->fetch_assoc()['total'] + 1;
    $receipt_no = "RCPT-" . $year . "-" . str_pad($next_id, 4, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO vms_receipts (booking_id, receipt_no, amount_rent, amount_rsd, total_amount, payment_mode, instrument_no, bank_name, receipt_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isddsssss", $booking_id, $receipt_no, $rent, $rsd, $total, $mode, $inst, $bank, $r_date);

    if ($stmt->execute()) {
        header("Location: ../admin/booking_view.php?id=$booking_id&msg=Receipt Generated: $receipt_no");
    } else {
        die("Error saving receipt: " . $conn->error);
    }
}
?>
<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $b_id = intval($_POST['booking_id']);
    
    // Safety Catch: Check if record already exists
    $duplicate_check = $conn->query("SELECT id FROM vms_rsd_refunds WHERE booking_id = $b_id");
    if($duplicate_check->num_rows > 0) {
        die("<script>alert('Error: Payout already exists for this booking.'); window.location.href='../admin/booking_list.php';</script>");
    }

    $stmt = $conn->prepare("INSERT INTO vms_rsd_refunds (
        booking_id, refund_type, voucher_no, 
        rent_refund_amount, rent_payment_mode, rent_ref_no,
        rsd_refund_amount, rsd_payment_mode, rsd_ref_no,
        total_refund_amount, refund_date, trust_bank_details, remarks
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // i=int, s=string, d=decimal
    // Count: 13 placeholders (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    // Bind types: i (b_id), s (type), s (voucher), d (rent_amt), s (rent_mode), s (rent_ref), d (rsd_amt), s (rsd_mode), s (rsd_ref), d (total), s (date), s (bank), s (remarks)
    $stmt->bind_param("issdssdssdsss", 
        $b_id, 
        $_POST['refund_type'],
        $_POST['voucher_no'], 
        $_POST['rent_refund_amount'],
        $_POST['rent_payment_mode'],
        $_POST['rent_ref_no'],
        $_POST['rsd_refund_amount'],
        $_POST['rsd_payment_mode'],
        $_POST['rsd_ref_no'],
        $_POST['total_refund_amount'],
        $_POST['refund_date'], 
        $_POST['trust_bank_details'],
        $_POST['remarks']
    );

    if ($stmt->execute()) {
        $conn->query("UPDATE vms_cancellation_bills SET rsd_refund_status = 'Refunded' WHERE booking_id = $b_id");
        header("Location: ../admin/print_rsd_refund.php?voucher_no=" . urlencode($_POST['voucher_no']));
    } else {
        die("Error saving refund: " . $stmt->error);
    }
}
?>
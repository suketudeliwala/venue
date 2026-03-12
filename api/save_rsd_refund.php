<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $b_id = intval($_POST['booking_id']);
    
    $stmt = $conn->prepare("INSERT INTO vms_rsd_refunds (booking_id, refund_type, voucher_no, refund_date, refund_amount, payment_mode, ref_no, trust_bank_details, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("isssdssss", 
        $b_id, 
        $_POST['refund_type'],
        $_POST['voucher_no'], 
        $_POST['refund_date'], 
        $_POST['refund_amount'], 
        $_POST['payment_mode'], 
        $_POST['ref_no'], 
        $_POST['trust_bank_details'],
        $_POST['remarks']
    );

    if ($stmt->execute()) {
        // If it was a cancellation refund, close the status in that table
        $conn->query("UPDATE vms_cancellation_bills SET rsd_refund_status = 'Refunded' WHERE booking_id = $b_id");
        
        header("Location: ../admin/booking_list.php?status=refund_issued");
    } else {
        die("Error: " . $stmt->error);
    }
}
?>
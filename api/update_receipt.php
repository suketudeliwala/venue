<?php
include("../includes/config.php");
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $r_id = intval($_POST['receipt_id']);
    $b_id = intval($_POST['booking_id']);
    $rent = floatval($_POST['amount_rent']);
    $rsd  = floatval($_POST['amount_rsd']);
    $total = $rent + $rsd;

    $sql = "UPDATE vms_receipts SET amount_rent = ?, amount_rsd = ?, total_amount = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dddi", $rent, $rsd, $total, $r_id);
    
    if($stmt->execute()) {
        header("Location: ../admin/booking_view.php?id=$b_id&msg=Receipt Updated");
    }
}
?>
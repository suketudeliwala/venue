<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $invoice_id = intval($_POST['invoice_id']);
    
    // Sanitize and Recalculate
    $base_rent = floatval($_POST['f_rent']);
    $extra_services = floatval($_POST['f_extra']);
    $damages = floatval($_POST['f_damage']);
    $discount = floatval($_POST['f_discount']);
    $advance_paid = floatval($_POST['advance_paid']);

    $taxable = ($base_rent + $extra_services + $damages) - $discount;
    $cgst = $taxable * 0.09;
    $sgst = $taxable * 0.09;
    $grand_total = $taxable + $cgst + $sgst;
    $final_balance = $grand_total - $advance_paid;
    
    $narration = mysqli_real_escape_string($conn, $_POST['narration']);

    $sql = "UPDATE vms_invoices SET 
            base_rent = ?, total_damages = ?, extra_charges = ?, 
            discount_amount = ?, taxable_amount = ?, cgst_amount = ?, 
            sgst_amount = ?, grand_total = ?, final_balance = ?, 
            narration = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dddddddddsi", 
        $base_rent, $damages, $extra_services, $discount, 
        $taxable, $cgst, $sgst, $grand_total, $final_balance, 
        $narration, $invoice_id
    );

    if($stmt->execute()) {
        header("Location: ../admin/billing_list_report.php?msg=Invoice Updated Successfully");
    } else {
        die("Error updating invoice: " . $conn->error);
    }
}
?>
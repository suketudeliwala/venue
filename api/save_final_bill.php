<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = intval($_POST['booking_id']);
    
    // 1. Fetch the exact advance rent paid at this moment to save in the invoice
    $paid_res = $conn->query("SELECT SUM(amount_rent) as total_rent_paid FROM vms_receipts WHERE booking_id = $booking_id");
    $paid_row = $paid_res->fetch_assoc();
    $advance_paid = $paid_row['total_rent_paid'] ?? 0;

    // 2. Financial Sanitization
    $base_rent = floatval($_POST['f_rent']);
    $extra_services = floatval($_POST['f_extra']);
    $damages = floatval($_POST['f_damage']);
    $discount = floatval($_POST['f_discount']);
    
    $taxable_amount = ($base_rent + $extra_services + $damages) - $discount;
    $cgst = $taxable_amount * 0.09;
    $sgst = $taxable_amount * 0.09;
    $grand_total = $taxable_amount + $cgst + $sgst;
    
    // Final balance calculation
    $final_balance = $grand_total - $advance_paid;
    $narration = mysqli_real_escape_string($conn, $_POST['narration']);
    $invoice_no = "INV-" . date('Ymd') . "-" . $booking_id;

    $conn->begin_transaction();

    try {
        // Updated INSERT to include total_advance_paid
        $stmt = $conn->prepare("INSERT INTO vms_invoices (
            booking_id, invoice_no, invoice_date, base_rent, total_damages, 
            extra_charges, discount_amount, taxable_amount, cgst_amount, 
            sgst_amount, grand_total, total_advance_paid, final_balance, narration, status
        ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Final')");

        // Types String: 14 parameters (i, s, d, d, d, d, d, d, d, d, d, d, s)
        $stmt->bind_param("issdddddddddds", 
            $booking_id, $invoice_no, $base_rent, $damages, 
            $extra_services, $discount, $taxable_amount, $cgst, 
            $sgst, $grand_total, $advance_paid, $final_balance, $narration
        );
        
        $stmt->execute();

        // 3. Update Booking Master Status to 'Billed'
        $conn->query("UPDATE vms_booking_master SET status = 'Billed' WHERE id = $booking_id");

        $conn->commit();
        header("Location: ../admin/booking_list.php?msg=Invoice $invoice_no Generated & Booking Billed");

    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}
?>
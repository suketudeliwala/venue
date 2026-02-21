<?php
include("../includes/config.php");

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // VALIDATION: Check if any receipt exists
    $receipt_check = $conn->query("SELECT COUNT(id) as count FROM vms_receipts WHERE booking_id = $id");
    $has_payment = $receipt_check->fetch_assoc()['count'];

    if ($has_payment == 0) {
        die("<script>alert('Cancellation Denied: No payment has been received for this booking yet.'); window.location.href='booking_view.php?id=$id';</script>");
    }    

    // 1. Get the Booking details AND the linked Enquiry ID
    $sql = "SELECT b.total_rent, b.total_rsd, b.enquiry_id, MIN(s.booking_date) as start_date 
            FROM vms_booking_master b 
            JOIN vms_booking_slots s ON b.id = s.booking_id 
            WHERE b.id = $id";
    $data = $conn->query($sql)->fetch_assoc();
    $enquiry_id = $data['enquiry_id'];
    
    // 2. Calculate days until the event for deduction
    $today = new DateTime();
    $eventDate = new DateTime($data['start_date']);
    $interval = $today->diff($eventDate);
    $daysRemaining = $interval->invert ? 0 : $interval->days;

    // 3. Fetch matching policy
    $policy_res = $conn->query("SELECT deduction_percent FROM vms_cancellation_policy 
                                WHERE $daysRemaining BETWEEN days_before_min AND days_before_max LIMIT 1");
    
    $deductionPercent = ($policy_res->num_rows > 0) ? $policy_res->fetch_assoc()['deduction_percent'] : 100;

    // 4. Calculate Final Amounts
    $deductionAmount = ($data['total_rent'] * $deductionPercent) / 100;
    $refundRent = $data['total_rent'] - $deductionAmount;
    $refundRSD = $data['total_rsd']; 

    // 5. Build the Summary Remark
    $status_msg = "Cancelled $daysRemaining days before. Deduction: $deductionPercent%. Refund Rent: ₹$refundRent, Refund RSD: ₹$refundRSD";
    
    $conn->begin_transaction();
    try {
        // UPDATE 1: Update Booking Master
        $conn->query("UPDATE vms_booking_master SET status='Cancelled', remarks='$status_msg' WHERE id=$id");

        // UPDATE 2: Update linked Enquiry (Requirement: Sync statuses)
        if ($enquiry_id) {
            $conn->query("UPDATE vms_enquiries SET status = 'Cancelled' WHERE id = $enquiry_id");
        }

        $conn->commit();
        header("Location: booking_list.php?status=success&msg=Booking and Enquiry updated to Cancelled.");
    } catch (Exception $e) {
        $conn->rollback();
        die("Cancellation Error: " . $e->getMessage());
    }
}
?>
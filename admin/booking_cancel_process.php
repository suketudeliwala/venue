<?php
include("../includes/config.php");

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // 1. Get Booking and earliest event date
    $sql = "SELECT b.total_rent, b.total_rsd, MIN(s.booking_date) as start_date 
            FROM vms_booking_master b 
            JOIN vms_booking_slots s ON b.id = s.booking_id 
            WHERE b.id = $id";
    $data = $conn->query($sql)->fetch_assoc();
    
    // 2. Calculate days difference
    $today = new DateTime();
    $eventDate = new DateTime($data['start_date']);
    $daysRemaining = $today->diff($eventDate)->days;
    if ($eventDate < $today) $daysRemaining = 0; // Past date

    // 3. Determine Deduction % based on your policy
    $deductionPercent = 100; // Default: No refund
    if($daysRemaining >= 90) $deductionPercent = 5;
    elseif($daysRemaining >= 60) $deductionPercent = 25;
    elseif($daysRemaining >= 45) $deductionPercent = 50;

    $deductionAmount = ($data['total_rent'] * $deductionPercent) / 100;
    $refundRent = $data['total_rent'] - $deductionAmount;
    $refundRSD = $data['total_rsd']; // RSD always fully refunded as per your rule

    // 4. Update Status and store cancellation notes
    $remarks = "Cancelled $daysRemaining days before. Deduction: $deductionPercent%. Refund: Rent(".($refundRent).") + RSD(".($refundRSD).")";
    
    $conn->query("UPDATE vms_booking_master SET status='Cancelled', remarks='$remarks' WHERE id=$id");
    
    header("Location: booking_list.php?status=success&msg=Booking Cancelled and Refund Calculated.");
}
?>
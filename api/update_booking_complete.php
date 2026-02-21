<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();
    try {
        $booking_id = intval($_POST['booking_id']);
        $function_name = mysqli_real_escape_string($conn, $_POST['function_name']);
        
        // 1. Update the Master record
        $stmt_m = $conn->prepare("UPDATE vms_booking_master SET function_name = ? WHERE id = ?");
        $stmt_m->bind_param("si", $function_name, $booking_id);
        $stmt_m->execute();

        // 2. Clear old slots
        $conn->query("DELETE FROM vms_booking_slots WHERE booking_id = $booking_id");

        // 3. Insert fresh slots
        $total_rent = 0; $total_rsd = 0;
        $venues = $_POST['slot_venue_id'];
        $dates  = $_POST['slot_date'];
        $starts = $_POST['slot_start'];
        $ends   = $_POST['slot_end'];
        $rates  = $_POST['slot_rate_id']; // Added
        $rents  = $_POST['slot_rent'];
        $rsds   = $_POST['slot_rsd'];

        $stmt_s = $conn->prepare("INSERT INTO vms_booking_slots (booking_id, venue_id, booking_date, start_time, finish_time, slot_rate_id, slot_rent, slot_rsd) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($venues as $i => $v_id) {
            $r_amt = floatval($rents[$i]);
            $d_amt = floatval($rsds[$i]);
            $r_id  = intval($rates[$i]);
            $total_rent += $r_amt;
            $total_rsd += $d_amt;
            
            $stmt_s->bind_param("issssidd", $booking_id, $v_id, $dates[$i], $starts[$i], $ends[$i], $r_id, $r_amt, $d_amt);
            $stmt_s->execute();
        }

        // 4. Re-calculate Financials
        $tax = $total_rent * 0.18;
        $net = $total_rent + $tax + $total_rsd;
        $conn->query("UPDATE vms_booking_master SET total_rent=$total_rent, total_rsd=$total_rsd, total_tax=$tax, net_payable=$net WHERE id=$booking_id");

        $conn->commit();
        header("Location: ../admin/booking_list.php?status=success&msg=Booking Updated");
    } catch (Exception $e) {
        $conn->rollback();
        die("Update Error: " . $e->getMessage());
    }
}
?>
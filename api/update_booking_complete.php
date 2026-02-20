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

        // 2. Clear old slots to prepare for updated ones
        $conn->query("DELETE FROM vms_booking_slots WHERE booking_id = $booking_id");

        // 3. Insert updated slots (Looping through arrays)
        $total_rent = 0;
        $total_rsd = 0;
        
        $venues = $_POST['slot_venue_id'];
        $dates  = $_POST['slot_date'];
        $starts = $_POST['slot_start'];
        $ends   = $_POST['slot_end'];
        $rents  = $_POST['slot_rent'];
        $rsds   = $_POST['slot_rsd'];

        $stmt_s = $conn->prepare("INSERT INTO vms_booking_slots (booking_id, venue_id, booking_date, start_time, finish_time, slot_rent, slot_rsd) VALUES (?, ?, ?, ?, ?, ?, ?)");

        foreach ($venues as $i => $v_id) {
            $r = floatval($rents[$i]);
            $d = floatval($rsds[$i]);
            $total_rent += $r;
            $total_rsd += $d;
            
            $stmt_s->bind_param("issssdd", $booking_id, $v_id, $dates[$i], $starts[$i], $ends[$i], $r, $d);
            $stmt_s->execute();
        }

        // 4. Re-calculate Financials (18% GST)
        $tax = $total_rent * 0.18;
        $net = $total_rent + $tax + $total_rsd;
        
        $stmt_u = $conn->prepare("UPDATE vms_booking_master SET total_rent = ?, total_rsd = ?, total_tax = ?, net_payable = ? WHERE id = ?");
        $stmt_u->bind_param("ddddd", $total_rent, $total_rsd, $tax, $net, $booking_id);
        $stmt_u->execute();

        $conn->commit();
        header("Location: ../admin/booking_list.php?status=success&msg=Booking Updated Successfully");

    } catch (Exception $e) {
        $conn->rollback();
        die("Update Error: " . $e->getMessage());
    }
}
?>
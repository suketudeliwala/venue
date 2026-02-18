<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Begin Transaction
    $conn->begin_transaction();

    try {
        // 2. Capture Master Data
        $customer_id = intval($_POST['customer_id']);
        $function_name = mysqli_real_escape_string($conn, $_POST['function_name']);
        $tracking_no = mysqli_real_escape_string($conn, $_POST['tracking_no']);
        $enquiry_id = !empty($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : NULL;
        
        // Vendors
        $decorator = !empty($_POST['decorator_id']) ? intval($_POST['decorator_id']) : NULL;
        $caterer = !empty($_POST['caterer_id']) ? intval($_POST['caterer_id']) : NULL;
        
        // Aggregate totals (Calculated on Frontend, Verified here)
        $total_rent = 0;
        $total_rsd = 0;

        // 3. Insert into vms_booking_master
        $stmt_m = $conn->prepare("INSERT INTO vms_booking_master (
            enquiry_id, tracking_no, customer_id, function_name, 
            decorator_id, caterer_id, status
        ) VALUES (?, ?, ?, ?, ?, ?, 'Confirmed')");
        
        $stmt_m->bind_param("isisii", $enquiry_id, $tracking_no, $customer_id, $function_name, $decorator, $caterer);
        $stmt_m->execute();
        $booking_id = $conn->insert_id;

        // 4. Loop through Venue Slots
        $stmt_s = $conn->prepare("INSERT INTO vms_booking_slots (
            booking_id, venue_id, booking_date, start_time, finish_time, 
            slot_rent, slot_rsd
        ) VALUES (?, ?, ?, ?, ?, ?, ?)");

        // Arrays from the dynamic form rows
        $venues = $_POST['slot_venue_id'];
        $dates  = $_POST['slot_date'];
        $starts = $_POST['slot_start'];
        $ends   = $_POST['slot_end'];
        $rents  = $_POST['slot_rent'];
        $rsds   = $_POST['slot_rsd'];

        foreach ($venues as $i => $v_id) {
            $v_id = intval($v_id);
            $b_date = $dates[$i];
            $s_time = $starts[$i];
            $f_time = $ends[$i];
            $s_rent = floatval($rents[$i]);
            $s_rsd  = floatval($rsds[$i]);

            $total_rent += $s_rent;
            $total_rsd  += $s_rsd;

            $stmt_s->bind_param("issssdd", $booking_id, $v_id, $b_date, $s_time, $f_time, $s_rent, $s_rsd);
            $stmt_s->execute();
        }

        // 5. Update Master with Final Calculated Totals & Taxes
        $tax = $total_rent * 0.18; // 18% Total GST
        $net = $total_rent + $tax + $total_rsd;

        $update_sql = "UPDATE vms_booking_master SET total_rent = ?, total_rsd = ?, total_tax = ?, net_payable = ? WHERE id = ?";
        $stmt_u = $conn->prepare($update_sql);
        $stmt_u->bind_param("ddddd", $total_rent, $total_rsd, $tax, $net, $booking_id);
        $stmt_u->execute();

        // 6. If converted from Enquiry, update Enquiry status
        if ($enquiry_id) {
            $conn->query("UPDATE vms_enquiries SET status = 'Converted' WHERE id = $enquiry_id");
        }

        // 7. Commit Transaction
        $conn->commit();
        header("Location: ../admin/booking_view.php?id=$booking_id&status=success&msg=Booking Confirmed Successfully");

    } catch (Exception $e) {
        // Rollback on any error
        $conn->rollback();
        header("Location: ../admin/booking_new.php?status=error&msg=System Error: " . $e->getMessage());
    }
}
?>
<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();

    try {
        // --- STEP 1: CAPTURE FORM DATA ---
        $applicant_name = mysqli_real_escape_string($conn, $_POST['applicant_name']);
        $mobile = mysqli_real_escape_string($conn, $_POST['applicant_mobile']);
        $email = mysqli_real_escape_string($conn, $_POST['applicant_email']);
        $is_member = intval($_POST['is_member']);
        $function_name = mysqli_real_escape_string($conn, $_POST['function_name']);
        
        // Fix: If tracking_no is empty in POST, generate a new one
        $tracking_no = !empty($_POST['tracking_no']) ? mysqli_real_escape_string($conn, $_POST['tracking_no']) : 'BK-' . date('YmdHis');
        $enquiry_id = !empty($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : NULL;

        // --- STEP 2: AUTO-REGISTER CUSTOMER ---
        $check_cust = $conn->query("SELECT id FROM vms_customers WHERE mobile = '$mobile' LIMIT 1");
        if ($check_cust->num_rows == 0) {
            $ins_cust = $conn->prepare("INSERT INTO vms_customers (contact_person, mobile, email, is_member, customer_type) VALUES (?, ?, ?, ?, 'Customer')");
            $ins_cust->bind_param("sssi", $applicant_name, $mobile, $email, $is_member);
            $ins_cust->execute();
            $customer_id = $conn->insert_id;
        } else {
            $customer_id = $check_cust->fetch_assoc()['id'];
        }

        // --- STEP 3: INSERT INTO BOOKING MASTER ---
        // Ensure your vms_booking_master table has the 'is_member' column
        $stmt_m = $conn->prepare("INSERT INTO vms_booking_master (
            enquiry_id, tracking_no, customer_id, function_name, is_member, status
        ) VALUES (?, ?, ?, ?, ?, 'Confirmed')");
        
        $stmt_m->bind_param("isisi", $enquiry_id, $tracking_no, $customer_id, $function_name, $is_member);
        $stmt_m->execute();
        $booking_id = $conn->insert_id;

        // --- STEP 4: LOOP THROUGH SLOTS ---
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
            $s_rent = floatval($rents[$i]);
            $s_rsd  = floatval($rsds[$i]);
            $total_rent += $s_rent;
            $total_rsd  += $s_rsd;
            $stmt_s->bind_param("issssdd", $booking_id, $v_id, $dates[$i], $starts[$i], $ends[$i], $s_rent, $s_rsd);
            $stmt_s->execute();
        }

        // --- STEP 5: FINANCIAL TOTALS ---
        $tax = $total_rent * 0.18; 
        $net = $total_rent + $tax + $total_rsd;
        $conn->query("UPDATE vms_booking_master SET total_rent=$total_rent, total_rsd=$total_rsd, total_tax=$tax, net_payable=$net WHERE id=$booking_id");

        if ($enquiry_id) { $conn->query("UPDATE vms_enquiries SET status = 'Converted' WHERE id = $enquiry_id"); }

        $conn->commit();
        header("Location: ../admin/booking_list.php?status=success&msg=Booking Saved");
    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}
?>
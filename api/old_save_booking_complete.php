<?php
include("../includes/config.php");

// DEBUG TOGGLE: Set to true to see exactly what is happening
$debug = true; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if($debug) {
        echo "<pre>--- DEBUG DATA RECEIVED ---<br>";
        print_r($_POST);
        echo "</pre>";
    }

    $conn->begin_transaction();

    try {
        $applicant_name = mysqli_real_escape_string($conn, $_POST['applicant_name']);
        $mobile = mysqli_real_escape_string($conn, $_POST['applicant_mobile']);
        $email = mysqli_real_escape_string($conn, $_POST['applicant_email']);
        $is_member = isset($_POST['is_member']) ? intval($_POST['is_member']) : 0;
        $function_name = mysqli_real_escape_string($conn, $_POST['function_name']);
        $tracking_no = !empty($_POST['tracking_no']) ? mysqli_real_escape_string($conn, $_POST['tracking_no']) : 'BK-' . date('YmdHis');
        $enquiry_id = !empty($_POST['enquiry_id']) ? intval($_POST['enquiry_id']) : NULL;

        // --- STEP 1: CUSTOMER CHECK ---
        $check_cust = $conn->query("SELECT id FROM vms_customers WHERE mobile = '$mobile' LIMIT 1");
        if ($check_cust->num_rows == 0) {
            $ins_cust = $conn->prepare("INSERT INTO vms_customers (contact_person, mobile, email, is_member, customer_type) VALUES (?, ?, ?, ?, 'Customer')");
            $ins_cust->bind_param("sssi", $applicant_name, $mobile, $email, $is_member);
            $ins_cust->execute();
            $customer_id = $conn->insert_id;
        } else {
            $customer_id = $check_cust->fetch_assoc()['id'];
        }

        // --- STEP 2: THE "PROBLEM" INSERT ---
        // We will use a standard query here instead of prepare to make debugging easier
        $sql_master = "INSERT INTO vms_booking_master 
                      (enquiry_id, tracking_no, customer_id, function_name, is_member, status) 
                      VALUES 
                      (".($enquiry_id ?? 'NULL').", '$tracking_no', $customer_id, '$function_name', $is_member, 'Confirmed')";
        
        if($debug) echo "Executing Query: " . $sql_master . "<br>";

        if (!$conn->query($sql_master)) {
            throw new Exception("Master Insert Failed: " . $conn->error);
        }
        
        $booking_id = $conn->insert_id;

        // --- STEP 3: SLOTS ---
        $venues = $_POST['slot_venue_id'];
        $dates  = $_POST['slot_date'];
        $starts = $_POST['slot_start'];
        $ends   = $_POST['slot_end'];
        $rents  = $_POST['slot_rent'];
        $rsds   = $_POST['slot_rsd'];

        foreach ($venues as $i => $v_id) {
            $r = floatval($rents[$i]);
            $d = floatval($rsds[$i]);
            $v = intval($v_id);
            $dt = $dates[$i];
            $st = $starts[$i];
            $et = $ends[$i];

            $sql_slot = "INSERT INTO vms_booking_slots (booking_id, venue_id, booking_date, start_time, finish_time, slot_rent, slot_rsd) 
                         VALUES ($booking_id, $v, '$dt', '$st', '$et', $r, $d)";
            
            if (!$conn->query($sql_slot)) {
                throw new Exception("Slot Insert Failed at index $i: " . $conn->error);
            }
        }

        $conn->commit();
        
        if($debug) {
            echo "Success! Booking ID: " . $booking_id;
            exit; 
        }

        header("Location: ../admin/booking_list.php?status=success");

    } catch (Exception $e) {
        $conn->rollback();
        echo "<h3 style='color:red'>DETAILED ERROR:</h3>";
        echo $e->getMessage();
        echo "<br><br><strong>Note:</strong> If it says 'Unknown column is_member', go to phpMyAdmin, open vms_booking_master, and click the 'Structure' tab. Ensure it is exactly 'is_member' with no leading spaces.";
        exit;
    }
}
?>
<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $b_id = intval($_POST['booking_id']);
    $s_id = intval($_POST['slot_id']);
    
    // Ensure numeric fields are cleaned
    $eb_start = !empty($_POST['eb_start']) ? floatval($_POST['eb_start']) : 0;
    $eb_end   = !empty($_POST['eb_end']) ? floatval($_POST['eb_end']) : 0;
    $dmg_amt  = !empty($_POST['damage_charges']) ? floatval($_POST['damage_charges']) : 0;
    $ext_amt  = !empty($_POST['extra_charges']) ? floatval($_POST['extra_charges']) : 0;
    $ot_flag  = isset($_POST['overtime_flag']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO vms_utilization_reports (
        booking_id, slot_id, report_date, actual_start_time, actual_end_time, 
        eb_reading_start, eb_reading_end, decorator_id, caterer_id, 
        damage_details, damage_charges, extra_services_details, 
        extra_services_charges, overtime_flag, manager_remarks
    ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Corrected Types String: 15 parameters
    // i(2), s(2), d(2), i(2), s(1), d(1), s(1), d(1), i(1), s(1)
    $types = "iisssddiissddis"; 

    $stmt->bind_param($types, 
        $b_id, 
        $s_id, 
        $_POST['actual_start'], 
        $_POST['actual_end'], 
        $eb_start, 
        $eb_end, 
        $_POST['decorator_id'], 
        $_POST['caterer_id'], 
        $_POST['damage_details'], 
        $dmg_amt, 
        $_POST['extra_details'], 
        $ext_amt, 
        $ot_flag, 
        $_POST['manager_remarks']
    );

    if($stmt->execute()) {
        // Redirecting to Pending Utilization tab as requested
        header("Location: ../admin/booking_list.php?tab=pending_utilization&msg=Utilization Reported Successfully");
    } else {
        die("Error: " . $conn->error);
    }
}
?>
<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $b_id = intval($_POST['booking_id']);
    $s_id = intval($_POST['slot_id']);
    
    $stmt = $conn->prepare("INSERT INTO vms_utilization_reports (
        booking_id, slot_id, report_date, actual_start_time, actual_end_time, 
        eb_reading_start, eb_reading_end, decorator_id, caterer_id, 
        damage_details, damage_charges, extra_services_details, 
        extra_services_charges, overtime_flag, manager_remarks
    ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("iisssddissddis", 
        $b_id, $s_id, $_POST['actual_start'], $_POST['actual_end'], 
        $_POST['eb_start'], $_POST['eb_end'], $_POST['decorator_id'], 
        $_POST['caterer_id'], $_POST['damage_details'], $_POST['damage_charges'], 
        $_POST['extra_details'], $_POST['extra_charges'], $_POST['overtime_flag'], 
        $_POST['manager_remarks']
    );

    if($stmt->execute()) {
        header("Location: ../admin/booking_list.php?tab=pending_utilization&msg=Utilization Reported Successfully");
    } else {
        die("Error saving report: " . $conn->error);
    }
}
?>
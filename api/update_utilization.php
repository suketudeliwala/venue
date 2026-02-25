<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $report_id = intval($_POST['report_id']);
    $b_id = intval($_POST['booking_id']);
    
    $stmt = $conn->prepare("UPDATE vms_utilization_reports SET 
        actual_start_time = ?, actual_end_time = ?, 
        eb_reading_start = ?, eb_reading_end = ?, 
        decorator_id = ?, caterer_id = ?, 
        damage_details = ?, damage_charges = ?, 
        extra_services_details = ?, extra_services_charges = ?, 
        overtime_flag = ?, manager_remarks = ?
        WHERE id = ?");

    $overtime = isset($_POST['overtime_flag']) ? 1 : 0;

    $stmt->bind_param("ssddiissddisi", 
        $_POST['actual_start'], $_POST['actual_end'], 
        $_POST['eb_start'], $_POST['eb_end'], $_POST['decorator_id'], 
        $_POST['caterer_id'], $_POST['damage_details'], $_POST['damage_charges'], 
        $_POST['extra_details'], $_POST['extra_charges'], $overtime, 
        $_POST['manager_remarks'], $report_id
    );

    if($stmt->execute()) {
        header("Location: ../admin/report_utilization.php?msg=Report Updated Successfully");
    } else {
        die("Error updating report: " . $conn->error);
    }
}
?>
<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $b_id = intval($_POST['booking_id']);
    $cb_no = mysqli_real_escape_string($conn, $_POST['cb_no']);
    
    $total_rent_orig = floatval($_POST['orig_rent']); 
    $gst_orig        = floatval($_POST['orig_gst']);  
    $rsd_orig        = floatval($_POST['orig_rsd']);  
    $deduct_pc       = floatval($_POST['deduct_pc']);
    $adjustment      = floatval($_POST['manual_adj']); 
    $final_penalty   = floatval($_POST['final_penalty']); 
    $net_refund      = floatval($_POST['net_refund']);
    $remarks         = mysqli_real_escape_string($conn, $_POST['remarks']);

    $conn->begin_transaction();
    try {
        $sql_cb = "INSERT INTO vms_cancellation_bills (
                        booking_id, cb_no, cancellation_date, 
                        total_rent_original, gst_original, rsd_original, 
                        deduction_percent, deduction_amount, adjustment_amount, 
                        net_refund_amount, remarks, rsd_refund_status
                    ) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

        $stmt = $conn->prepare($sql_cb);
        $stmt->bind_param("isddddddds", 
            $b_id, $cb_no, $total_rent_orig, $gst_orig, $rsd_orig, 
            $deduct_pc, $final_penalty, $adjustment, $net_refund, $remarks
        );
        
        if (!$stmt->execute()) { throw new Exception($stmt->error); }

        $conn->query("UPDATE vms_booking_master SET status = 'Cancelled' WHERE id = $b_id");
        
        $enq_data = $conn->query("SELECT enquiry_id FROM vms_booking_master WHERE id = $b_id")->fetch_assoc();
        if(!empty($enq_data['enquiry_id'])) {
            $conn->query("UPDATE vms_enquiries SET status = 'Cancelled' WHERE id = " . intval($enq_data['enquiry_id']));
        }

        $conn->commit();
        header("Location: ../admin/print_cancellation.php?cb_no=" . urlencode($cb_no));
    } catch (Exception $e) {
        $conn->rollback();
        die("Save Error: " . $e->getMessage());
    }
}
?>
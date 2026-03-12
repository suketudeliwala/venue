<?php
include("../includes/config.php");

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Final security check before deleting
    $paid_res = $conn->query("SELECT SUM(amount_rent + amount_rsd) as total_paid FROM vms_receipts WHERE booking_id = $id");
    $paid = $paid_res->fetch_assoc()['total_paid'] ?? 0;
    $util = $conn->query("SELECT id FROM vms_utilization_reports WHERE booking_id = $id")->num_rows;

    if($paid > 0 || $util > 0) {
        die("<script>alert('Delete Rejected: This booking has existing financial transactions or utilization reports.'); window.location.href='../admin/booking_list.php';</script>");
    }

    $conn->begin_transaction();
    try {
        $conn->query("DELETE FROM vms_booking_slots WHERE booking_id = $id");
        $conn->query("DELETE FROM vms_booking_master WHERE id = $id");
        $conn->commit();
        header("Location: ../admin/booking_list.php?msg=BookingDeleted");
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error deleting record: " . $e->getMessage();
    }
}
?>
<?php
include("../includes/config.php");
$id = intval($_GET['id']);
$b_id = intval($_GET['booking_id']);

if($conn->query("DELETE FROM vms_receipts WHERE id = $id")) {
    header("Location: ../admin/booking_view.php?id=$b_id&msg=Receipt Deleted");
}
?>
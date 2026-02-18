<?php
include("../includes/config.php");
if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM vms_blocked_dates WHERE id = $id");
    header("Location: blocked_dates.php?status=success&msg=Block Removed");
}
?>
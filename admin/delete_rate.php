<?php
include("../includes/config.php");
if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $conn->query("DELETE FROM vms_venue_rates WHERE id = $id");
    header("Location: venue_rates.php?status=success&msg=Rate Deleted");
}
?>
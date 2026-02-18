<?php
include("../includes/config.php");
header('Content-Type: application/json');

if(isset($_GET['venue_id'])) {
    $v_id = intval($_GET['venue_id']);
    $res = $conn->query("SELECT * FROM vms_venue_rates WHERE venue_id = $v_id");
    $rates = [];
    while($row = $res->fetch_assoc()) {
        $rates[] = $row;
    }
    echo json_encode($rates);
}
?>
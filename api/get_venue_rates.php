<?php
include("../includes/config.php");

// Set header to return JSON data
header('Content-Type: application/json');

if (isset($_GET['venue_id'])) {
    $v_id = intval($_GET['venue_id']);
    
    // Fetch all rate durations defined for this specific venue
    $sql = "SELECT id, duration_label, member_rate, non_member_rate, rsd_amount 
            FROM vms_venue_rates 
            WHERE venue_id = $v_id";
            
    $res = $conn->query($sql);
    $rates = [];
    
    while ($row = $res->fetch_assoc()) {
        $rates[] = $row;
    }
    
    echo json_encode($rates);
}
?>
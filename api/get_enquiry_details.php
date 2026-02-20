<?php
include("../includes/config.php");

// Set header to return JSON data
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Fetch enquiry details
    $sql = "SELECT id, tracking_no, applicant_name, applicant_mobile, applicant_email, 
                   function_name, is_member, venue_id, start_date 
            FROM vms_enquiries 
            WHERE id = $id LIMIT 1";
            
    $res = $conn->query($sql);
    
    if ($row = $res->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Enquiry not found']);
    }
}
?>
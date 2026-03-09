<?php
include("../includes/config.php");
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Fetch critical details for booking logic
    $sql = "SELECT contact_person, mobile, email, is_member FROM vms_customers WHERE id = $id AND customer_type = 'Customer' LIMIT 1";
    $result = $conn->query($sql);
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'Customer not found']);
    }
}
?>
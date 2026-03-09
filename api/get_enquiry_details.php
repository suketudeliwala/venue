<?php
include("../includes/config.php");

// Ensure no whitespace or errors are output before the header
ob_clean(); 
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Explicitly listing all fields to match your vms_enquiries table
    $sql = "SELECT id, tracking_no, applicant_name, applicant_mobile, applicant_email, 
               applicant_address, function_name, start_date, end_date, start_time, finish_time, is_member,
               venue_id, approx_attendees, need_decorator, need_caterer, need_sound, company_name 
            FROM vms_enquiries WHERE id = $id LIMIT 1";
            
    try {
        $res = $conn->query($sql);
        
        if ($res && $row = $res->fetch_assoc()) {
            // Logic: Ensure company_name is never null for the Javascript
            if (empty($row['company_name'])) {
                $row['company_name'] = $row['applicant_name'];
            }
            echo json_encode($row);
        } else {
            echo json_encode(['error' => 'Enquiry ID not found in database']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Database Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No ID provided']);
}
exit;
?>
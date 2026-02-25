<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Unique Tracking ID
    $tracking_no = "ENQ-" . date('Y') . "-" . strtoupper(substr(md5(uniqid()), 0, 5));

    // 2. Format Multiple Equipment Selection
    $equipments = isset($_POST['equipment_requested']) ? implode(',', $_POST['equipment_requested']) : '';

    // 3. Mapping 28 Variables
    $f_name    = mysqli_real_escape_string($conn, $_POST['function_name']);
    $s_date    = $_POST['start_date'];
    $e_date    = $_POST['end_date'];
    $s_time    = $_POST['start_time'];
    $f_time    = $_POST['finish_time'];
    $duration  = $_POST['duration'];
    $f_type    = $_POST['function_type'];
    $purpose   = $_POST['purpose'];
    $details   = mysqli_real_escape_string($conn, $_POST['function_details']);
    $attendees = intval($_POST['approx_attendees']);

    $dec = isset($_POST['need_decorator']) ? 1 : 0;
    $cat = isset($_POST['need_caterer']) ? 1 : 0;
    $snd = isset($_POST['need_sound']) ? 1 : 0;
    $wif = isset($_POST['need_wifi']) ? 1 : 0;
    $mgr = isset($_POST['need_manager']) ? 1 : 0;
    
    $other_services = mysqli_real_escape_string($conn, $_POST['other_services'] ?? '');
    $joint_org      = mysqli_real_escape_string($conn, $_POST['joint_org_name'] ?? '');
    $pol = intval($_POST['is_political']);
    $tic = intval($_POST['is_ticketed']);

    $c_name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $a_name = mysqli_real_escape_string($conn, $_POST['applicant_name']);
    $a_mail = mysqli_real_escape_string($conn, $_POST['applicant_email']);
    $a_mob  = mysqli_real_escape_string($conn, $_POST['applicant_mobile']);
    $a_addr = mysqli_real_escape_string($conn, $_POST['applicant_address']);
    
    $is_mem = isset($_POST['is_member']) ? 1 : 0;
    $mem_no = ($is_mem) ? mysqli_real_escape_string($conn, $_POST['member_no']) : NULL;

    // 4. SQL with EXACTLY 28 Question Marks
    $sql = "INSERT INTO vms_enquiries (
        tracking_no, status, function_name, start_date, end_date, start_time, finish_time, duration, 
        function_type, purpose, function_details, approx_attendees, 
        need_decorator, need_caterer, need_sound, need_wifi, need_manager, 
        other_services, joint_org_name, is_political, is_ticketed, 
        company_name, applicant_name, applicant_email, applicant_mobile, applicant_address, 
        is_member, member_no, equipment_requested
    ) VALUES (?, 'New', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    // 5. Types String: 28 characters total
    // s(10), i(1), i(5), s(2), i(2), s(5), i(1), s(2) = 28
    $types = "ssssssssssiiiiiissiisssssiss"; 
    
    $stmt->bind_param($types, 
        $tracking_no, $f_name, $s_date, $e_date, $s_time, $f_time, $duration, $f_type, $purpose, $details, 
        $attendees, $dec, $cat, $snd, $wif, $mgr, $other_services, $joint_org, $pol, $tic, 
        $c_name, $a_name, $a_mail, $a_mob, $a_addr, $is_mem, $mem_no, $equipments
    );

    if ($stmt->execute()) {
        header("Location: ../pages/enquiry_success.php?ref=$tracking_no");
    } else {
        echo "Database Save Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
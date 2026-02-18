<?php
include("../includes/config.php");

header('Content-Type: application/json');

if (isset($_GET['venue_id']) && isset($_GET['date'])) {
    $v_id = intval($_GET['venue_id']);
    $date = $_GET['date'];
    $s_time = $_GET['start_time'];
    $f_time = $_GET['finish_time'];

    $response = ['status' => 'available', 'message' => 'Slot is available'];

    // 1. Check Blocked Dates Master
    $block_check = $conn->prepare("SELECT reason, is_hard_block FROM vms_blocked_dates WHERE block_date = ? LIMIT 1");
    $block_check->bind_param("s", $date);
    $block_check->execute();
    $block_res = $block_check->get_result();
    
    if ($block_row = $block_res->fetch_assoc()) {
        $response = [
            'status' => ($block_row['is_hard_block'] ? 'blocked' : 'warning'),
            'message' => "Date is reserved: " . $block_row['reason']
        ];
        echo json_encode($response);
        exit;
    }

    // 2. Check Existing Bookings for Time Overlap
    // Logic: (StartA < FinishB) AND (FinishA > StartB)
    $booking_check = $conn->prepare("
        SELECT b.function_name 
        FROM vms_booking_slots s
        JOIN vms_booking_master b ON s.booking_id = b.id
        WHERE s.venue_id = ? 
        AND s.booking_date = ? 
        AND (? < s.finish_time AND ? > s.start_time)
        LIMIT 1
    ");
    $booking_check->bind_param("isss", $v_id, $date, $s_time, $f_time);
    $booking_check->execute();
    $booking_res = $booking_check->get_result();

    if ($b_row = $booking_res->fetch_assoc()) {
        $response = [
            'status' => 'conflict',
            'message' => "Conflict with existing booking: " . $b_row['function_name']
        ];
    }

    echo json_encode($response);
}
?>
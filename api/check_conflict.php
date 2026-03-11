<?php
include("../includes/config.php");

$venue_id = $_GET['venue_id'];
$date = $_GET['date'];
$new_start = $_GET['start'];
$new_end = $_GET['end'];

$sql = "SELECT b.tracking_no FROM vms_booking_slots s
        JOIN vms_booking_master b ON s.booking_id = b.id
        WHERE s.venue_id = '$venue_id' 
        AND s.booking_date = '$date'
        AND b.status != 'Cancelled'
        AND ('$new_start' < s.finish_time AND '$new_end' > s.start_time)";

$res = $conn->query($sql);
if($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    echo json_encode(['conflict' => true, 'booking' => $row['tracking_no']]);
} else {
    echo json_encode(['conflict' => false]);
}
?>
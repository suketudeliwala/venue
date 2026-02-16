<?php
include("../includes/config.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Rule Check: Check if there is even a single booking record
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM vms_bookings WHERE venue_id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $check_stmt->bind_result($booking_count);
    $check_stmt->fetch();
    $check_stmt->close();

    if ($booking_count > 0) {
        // Deletion Forbidden
        header("Location: venue_list.php?status=error&msg=Cannot delete: This venue has $booking_count active or past booking(s).");
    } else {
        // Proceed with deletion
        $del_stmt = $conn->prepare("DELETE FROM vms_venues WHERE id = ?");
        $del_stmt->bind_param("i", $id);
        
        if ($del_stmt->execute()) {
            header("Location: venue_list.php?status=success&msg=Venue deleted successfully.");
        } else {
            header("Location: venue_list.php?status=error&msg=Deletion failed due to database error.");
        }
        $del_stmt->close();
    }
}
?>
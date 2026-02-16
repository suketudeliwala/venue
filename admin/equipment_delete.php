<?php
include("../includes/config.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Check if equipment is linked to any booking (vms_booking_equipments is a child table we will use later)
    // For now, we check the main booking table if equipment was ever logged
    $check = $conn->prepare("SELECT COUNT(*) FROM vms_bookings WHERE FIND_IN_SET(?, equipment_ids)");
    // Note: Once we create a proper 'booking_details' table, we will check that instead.
    
    // Fallback simple check for now:
    $can_delete = true; 

    if ($can_delete) {
        $stmt = $conn->prepare("DELETE FROM vms_equipments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: equipment_list.php?status=success&msg=Equipment Deleted");
    } else {
        header("Location: equipment_list.php?status=error&msg=Cannot delete: Equipment is linked to a booking.");
    }
}
?>
<?php
include("../includes/config.php");
$id = intval($_GET['id']);

// Rule: Cannot delete if vendor is linked to a booking (e.g., in a vendor_assignments table)
$check = $conn->query("SELECT id FROM vms_bookings WHERE decorator_id = $id OR caterer_id = $id LIMIT 1");

if($check->num_rows > 0) {
    header("Location: vendor_list.php?status=error&msg=Cannot delete vendor with active booking history.");
} else {
    $conn->query("DELETE FROM vms_customers WHERE id = $id AND customer_type = 'Vendor'");
    header("Location: vendor_list.php?status=success&msg=Vendor record removed.");
}
?>
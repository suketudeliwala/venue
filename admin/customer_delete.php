<?php
include("../includes/config.php");
$id = intval($_GET['id']);
// Safety Check: Can't delete if customer has bookings (to be added in future booking table)
$check = $conn->query("SELECT id FROM vms_bookings WHERE customer_id = $id LIMIT 1");
if($check->num_rows > 0) {
    header("Location: customer_list.php?status=error&msg=Cannot delete customer with history.");
} else {
    $conn->query("DELETE FROM vms_customers WHERE id = $id");
    header("Location: customer_list.php?status=success&msg=Customer Deleted");
}
?>
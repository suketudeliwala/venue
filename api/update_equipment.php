<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['equipment_name']);
    $qty = intval($_POST['total_quantity']);
    $rent = floatval($_POST['daily_rent']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE vms_equipments SET equipment_name=?, total_quantity=?, daily_rent=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sidsi", $name, $qty, $rent, $status, $id);

    if ($stmt->execute()) {
        header("Location: ../admin/equipment_list.php?status=success&msg=Equipment Updated");
    } else {
        header("Location: ../admin/equipment_list.php?status=error&msg=Update Failed");
    }
    $stmt->close();
}
?>
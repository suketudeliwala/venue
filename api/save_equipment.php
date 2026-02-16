<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['equipment_name']);
    $qty = intval($_POST['total_quantity']);
    $rent = floatval($_POST['daily_rent']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "INSERT INTO vms_equipments (equipment_name, total_quantity, daily_rent, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sids", $name, $qty, $rent, $status);

    if ($stmt->execute()) {
        header("Location: ../admin/equipment_list.php?status=success&msg=Equipment Added");
    } else {
        header("Location: ../admin/equipment_add.php?status=error&msg=Database Error");
    }
    $stmt->close();
}
?>
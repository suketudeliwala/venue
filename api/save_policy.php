<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $min = intval($_POST['days_min']);
    $max = intval($_POST['days_max']);
    $pct = floatval($_POST['percent']);

    $stmt = $conn->prepare("INSERT INTO vms_cancellation_policy (days_before_min, days_before_max, deduction_percent) VALUES (?, ?, ?)");
    $stmt->bind_param("iid", $min, $max, $pct);
    $stmt->execute();
    
    header("Location: ../admin/cancellation_policy.php?msg=Policy Added");
}
?>
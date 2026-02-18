<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $v_id = intval($_POST['venue_id']);
    $label = mysqli_real_escape_string($conn, $_POST['duration_label']);
    $rsd = floatval($_POST['rsd_amount']);
    $m_rate = floatval($_POST['member_rate']);
    $nm_rate = floatval($_POST['non_member_rate']);

    $stmt = $conn->prepare("INSERT INTO vms_venue_rates (venue_id, duration_label, member_rate, non_member_rate, rsd_amount) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isddd", $v_id, $label, $m_rate, $nm_rate, $rsd);
    
    if($stmt->execute()) {
        header("Location: ../admin/venue_rates.php?status=success&msg=Rate Added");
    }
    $stmt->close();
}
?>
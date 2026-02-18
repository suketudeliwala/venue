<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $label = mysqli_real_escape_string($conn, $_POST['duration_label']);
    $m_rate = floatval($_POST['member_rate']);
    $nm_rate = floatval($_POST['non_member_rate']);
    $rsd = floatval($_POST['rsd_amount']);
    $late = floatval($_POST['late_fee_per_hour']);

    $stmt = $conn->prepare("UPDATE vms_venue_rates SET duration_label=?, member_rate=?, non_member_rate=?, rsd_amount=?, late_fee_per_hour=? WHERE id=?");
    $stmt->bind_param("sddddi", $label, $m_rate, $nm_rate, $rsd, $late, $id);
    
    if($stmt->execute()) {
        header("Location: ../admin/venue_rates.php?status=success&msg=Rate Updated Successfully");
    } else {
        header("Location: ../admin/venue_rate_edit.php?id=$id&status=error&msg=Update Failed");
    }
    $stmt->close();
}
?>
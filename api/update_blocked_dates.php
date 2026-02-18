<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $b_date = $_POST['block_date'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $hard = isset($_POST['is_hard_block']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE vms_blocked_dates SET block_date=?, reason=?, is_hard_block=? WHERE id=?");
    $stmt->bind_param("ssii", $b_date, $reason, $hard, $id);
    
    if($stmt->execute()) {
        header("Location: ../admin/blocked_dates.php?status=success&msg=Restriction Updated");
    } else {
        header("Location: ../admin/blocked_dates_edit.php?id=$id&status=error&msg=Error updating restriction");
    }
    $stmt->close();
}
?>
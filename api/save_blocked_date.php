<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $b_date = $_POST['block_date'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    $hard = isset($_POST['is_hard_block']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO vms_blocked_dates (block_date, reason, is_hard_block) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $b_date, $reason, $hard);
    
    if($stmt->execute()) {
        header("Location: ../admin/blocked_dates.php?status=success&msg=Date Reserved");
    }
    $stmt->close();
}
?>
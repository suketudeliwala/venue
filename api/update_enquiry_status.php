<?php
include("../includes/config.php");

// Strict Admin Session Check
if (!isset($_SESSION['admin'])) {
    header("Location: ../admin/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Capture and sanitize inputs
    $id = intval($_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // 2. Prepare the update statement
    $sql = "UPDATE vms_enquiries SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $id);

    // 3. Execute and provide feedback
    if ($stmt->execute()) {
        // Redirect back to the view page with a success message
        header("Location: ../admin/enquiry_view.php?id=$id&status=success&msg=Enquiry status updated to $status");
    } else {
        // Redirect back with an error message
        header("Location: ../admin/enquiry_view.php?id=$id&status=error&msg=Database Error: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    // Prevent direct access to the script
    header("Location: ../admin/enquiry_list.php");
    exit;
}
?>
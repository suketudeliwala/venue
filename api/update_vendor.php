<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Duplicate Check excluding current ID
    $check = $conn->prepare("SELECT id FROM vms_customers WHERE (mobile = ? OR (email = ? AND email != '')) AND id != ? LIMIT 1");
    $check->bind_param("ssi", $mobile, $email, $id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        header("Location: ../admin/vendor_edit.php?id=$id&status=error&msg=Mobile or Email already used by another vendor.");
        exit();
    }

    $sql = "UPDATE vms_customers SET contact_person=?, business_type=?, mobile=?, email=?, pan_no=?, gst_no=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $_POST['contact_person'], $_POST['business_type'], $mobile, $email, $_POST['pan_no'], $_POST['gst_no'], $id);
    
    if($stmt->execute()) header("Location: ../admin/vendor_list.php?status=success&msg=Vendor Updated");
}
?>
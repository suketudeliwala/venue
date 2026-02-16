<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // 1. Duplicate Check: Check others but exclude current ID
    $check_sql = "SELECT id FROM vms_customers WHERE (mobile = ? OR (email = ? AND email != '')) AND id != ? LIMIT 1";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("ssi", $mobile, $email, $id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        header("Location: ../admin/customer_edit.php?id=$id&status=error&msg=Error: Another customer is already using this Mobile or Email.");
        exit();
    }
    $stmt_check->close();

    // 2. Proceed with Update
    $sql = "UPDATE vms_customers SET 
            company_name = ?, contact_person = ?, address = ?, 
            phone = ?, mobile = ?, email = ?, 
            pan_no = ?, gst_no = ?, remarks = ? 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssi", 
        $_POST['company_name'], $_POST['contact_person'], $_POST['address'], 
        $_POST['phone'], $mobile, $email, 
        $_POST['pan_no'], $_POST['gst_no'], $_POST['remarks'], $id
    );

    if ($stmt->execute()) {
        header("Location: ../admin/customer_list.php?status=success&msg=Customer Updated Successfully");
    } else {
        header("Location: ../admin/customer_edit.php?id=$id&status=error&msg=Update Failed.");
    }
    $stmt->close();
}
?>
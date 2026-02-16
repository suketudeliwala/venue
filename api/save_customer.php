<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact_person = mysqli_real_escape_string($conn, $_POST['contact_person']);

    // 1. Check for Duplicate Entry (Mobile or Email)
    $check_sql = "SELECT id FROM vms_customers WHERE mobile = ? OR (email = ? AND email != '') LIMIT 1";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $mobile, $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Duplicate Found
        header("Location: ../admin/customer_add.php?status=error&msg=Duplicate Entry: A customer with this Mobile or Email already exists.");
        exit();
    }
    $check_stmt->close();

    // 2. Proceed with Saving if no duplicate found
    $sql = "INSERT INTO vms_customers (company_name, contact_person, address, phone, mobile, email, pan_no, gst_no, remarks, customer_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Customer')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", 
        $_POST['company_name'], $contact_person, $_POST['address'], 
        $_POST['phone'], $mobile, $email, 
        $_POST['pan_no'], $_POST['gst_no'], $_POST['remarks']
    );
    
    if($stmt->execute()) {
        header("Location: ../admin/customer_list.php?status=success&msg=Customer Added Successfully");
    } else {
        header("Location: ../admin/customer_add.php?status=error&msg=Error saving record.");
    }
    $stmt->close();
}
?>
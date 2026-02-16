<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_person']);

    // 1. Duplicate Check (Mobile/Email)
    $check = $conn->prepare("SELECT id FROM vms_customers WHERE (mobile = ? OR (email = ? AND email != '')) AND customer_type = 'Vendor' LIMIT 1");
    $check->bind_param("ss", $mobile, $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        header("Location: ../admin/vendor_add.php?status=error&msg=Duplicate Vendor: Mobile or Email already exists.");
        exit();
    }

    // 2. Insert Record
    $sql = "INSERT INTO vms_customers (company_name, contact_person, business_type, address, mobile, email, pan_no, gst_no, customer_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Vendor')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $_POST['company_name'], $contact, $_POST['business_type'], $_POST['address'], $mobile, $email, $_POST['pan_no'], $_POST['gst_no']);
    
    if($stmt->execute()) {
        header("Location: ../admin/vendor_list.php?status=success&msg=Vendor Added Successfully");
    } else {
        header("Location: ../admin/vendor_add.php?status=error&msg=Error saving vendor.");
    }
}
?>
<?php
include("../includes/config.php");

// Only process if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize and collect input data
    $venue_name = mysqli_real_escape_string($conn, $_POST['venue_name']);
    $capacity = intval($_POST['capacity']);
    $is_ac = intval($_POST['is_ac']);
    $base_deposit = floatval($_POST['base_deposit']);
    $tax_percentage = floatval($_POST['tax_percentage']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $amenities = mysqli_real_escape_string($conn, $_POST['amenities']);

    // 2. Simple Validation
    if (empty($venue_name)) {
        header("Location: ../admin/venue_add.php?status=error&msg=Name is required");
        exit();
    }

    // 3. Prepare the SQL Statement
    $sql = "INSERT INTO vms_venues (venue_name, capacity_person, is_ac, amenities, base_deposit, tax_percentage, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    // "s" = string, "i" = integer, "d" = double/float
    $stmt->bind_param("siisdds", $venue_name, $capacity, $is_ac, $amenities, $base_deposit, $tax_percentage, $status);

    // 4. Execute and Redirect
    if ($stmt->execute()) {
        // Success: Redirect to a list page (which we will create next)
        header("Location: ../admin/venue_list.php?status=success&msg=Venue Created Successfully");
    } else {
        // Error
        header("Location: ../admin/venue_add.php?status=error&msg=Database Error: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    // Direct access to this file is not allowed
    header("Location: ../admin/venue_add.php");
    exit();
}
?>
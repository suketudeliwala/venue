<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $venue_name = mysqli_real_escape_string($conn, $_POST['venue_name']);
    $capacity = intval($_POST['capacity']);
    $sq_ft = intval($_POST['sq_ft']);
    $is_ac = intval($_POST['is_ac']);
    $base_deposit = floatval($_POST['base_deposit']);
    $sgst = floatval($_POST['sgst_percent']);
    $cgst = floatval($_POST['cgst_percent']);
    
    // Check if new image is uploaded
    if(isset($_FILES['venue_image']) && $_FILES['venue_image']['error'] == 0) {
        $temp_name = $_FILES['venue_image']['tmp_name'];
        $image_name = time() . "_" . basename($_FILES['venue_image']['name']);
        move_uploaded_file($temp_name, "../assets/images/venues/" . $image_name);
        
        $sql = "UPDATE vms_venues SET venue_name=?, capacity_person=?, sq_ft=?, is_ac=?, base_deposit=?, sgst_percent=?, cgst_percent=?, venue_image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiidddsi", $venue_name, $capacity, $sq_ft, $is_ac, $base_deposit, $sgst, $cgst, $image_name, $id);
    } else {
        $sql = "UPDATE vms_venues SET venue_name=?, capacity_person=?, sq_ft=?, is_ac=?, base_deposit=?, sgst_percent=?, cgst_percent=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiidddi", $venue_name, $capacity, $sq_ft, $is_ac, $base_deposit, $sgst, $cgst, $id);
    }

    if ($stmt->execute()) {
        header("Location: ../admin/venue_list.php?status=success&msg=Venue Updated Successfully");
    } else {
        header("Location: ../admin/venue_edit.php?id=$id&status=error&msg=Update Failed");
    }
}
?>
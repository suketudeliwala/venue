<?php
include("../includes/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $venue_name = mysqli_real_escape_string($conn, $_POST['venue_name']);
    $capacity = intval($_POST['capacity']);
    $sq_ft = intval($_POST['sq_ft']);
    $is_ac = intval($_POST['is_ac']);
    $base_deposit = floatval($_POST['base_deposit']);
    $sgst = floatval($_POST['sgst_percent']);
    $cgst = floatval($_POST['cgst_percent']);
    
    // Image Handling
    $image_name = "default_venue.jpg";
    if(isset($_FILES['venue_image']) && $_FILES['venue_image']['error'] == 0) {
        $temp_name = $_FILES['venue_image']['tmp_name'];
        $image_name = time() . "_" . basename($_FILES['venue_image']['name']); // Unique filename
        $target_dir = "../assets/images/venues/";
        move_uploaded_file($temp_name, $target_dir . $image_name);
    }

    $sql = "INSERT INTO vms_venues (venue_name, capacity_person, sq_ft, is_ac, base_deposit, sgst_percent, cgst_percent, venue_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiiddds", $venue_name, $capacity, $sq_ft, $is_ac, $base_deposit, $sgst, $cgst, $image_name);

    if ($stmt->execute()) {
        header("Location: ../admin/venue_list.php?status=success&msg=Venue Saved");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
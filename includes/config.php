<?php
// Database Configuration
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "vms"; // Updated to your new database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Global Organization Settings for VMS
$org_full_name = "VMS - Trust/NGO Management";
$org_short_name = "VMS-NGO";
$slogan = "Professional Venue Management for Welfare";
$org_logo_path = "assets/images/org_logo.png";
$path_prefix = (basename($_SERVER['PHP_SELF']) === 'index.php') ? '' : '../';

// Include the functions file (we will create this next for Royalty logic)
if (file_exists($path_prefix . 'includes/functions.php')) {
    include($path_prefix . 'includes/functions.php');
}
?>
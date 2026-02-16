<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "vms"; 

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

// Fetch Organization Settings from the 'organization_settings' table
$org_settings = [];
$res = $conn->query("SELECT * FROM organization_settings LIMIT 1");
if ($res && $res->num_rows > 0) {
    $org_settings = $res->fetch_assoc();
} else {
    // Basic fallback if table is empty
    $org_settings = ['org_full_name' => 'Venue Management System'];
}

// Global Variables
$org_full_name = $org_settings['org_full_name'];
$org_short_name = $org_settings['org_short_name'];
$slogan        = $org_settings['slogan'];
$org_regd      = $org_settings['org_regd_no'];
$org_address   = $org_settings['org_address'];
$org_comm_email      = $org_settings['org_comm_email'];
$org_comm_phone      = $org_settings['org_comm_phone'];
$org_logo_path = $org_settings['org_logo_path'];
$path_prefix   = (basename($_SERVER['PHP_SELF']) === 'index.php') ? '' : '../';

// Securely include functions once
include_once($path_prefix . 'includes/functions.php');
?>
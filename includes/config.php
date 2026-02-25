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

/**
 * Convert a number into Indian Rupee words
 */
if (!function_exists('getIndianCurrency')) {
    function getIndianCurrency(float $number) {
        $decimal = round($number - ($no = floor($number)), 2) * 100;
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(0 => '', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty', 30 => 'thirty', 40 => 'forty', 50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety');
        $digits = array('', 'hundred','thousand','lakh', 'crore');
        while( $i < $digits_length ) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += $divider == 10 ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number].' '. $digits[$counter]. $plural.' '.$hundred:$words[floor($number / 10) * 10].' '.$words[$number % 10]. ' '.$digits[$counter].$plural.' '.$hundred;
            } else $str[] = null;
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($decimal > 0) ? "." . ($words[$decimal / 10] . " " . $words[$decimal % 10]) . ' Paise' : '';
        return ($Rupees ? $Rupees . ' Rupees ' : '') . $paise . " Only";
    }
}
?>
<?php
// generate_guest_hash.php
$plain_guest_password = "mpavs@Gurukul_6"; // Choose a strong default guest password
$hashed_guest_password = password_hash($plain_guest_password, PASSWORD_BCRYPT);
echo "Plain Guest Password: " . $plain_guest_password . "<br>";
echo "Hashed Guest Password: " . $hashed_guest_password . "<br>";
?>


<!-- This is the SQL statment to send password to guest_access_settings table.
INSERT INTO guest_access_settings (setting_name, setting_value)
VALUES ('global_guest_password', 'PASTE_YOUR_GENERATED_HASH_HERE'); -->
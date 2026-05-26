<?php
// Database Credentials
$servername = "sql100.infinityfree.com";
$username = "if0_41248478";
$password = "dsUJK1fB8OFXFc";
$dbname = "if0_41248478_kolkata_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Set Charset to utf8mb4 (Crucial for Emojis and Bengali Script)
$conn->set_charset("utf8mb4");

// 2. Set Timezone to Kolkata (Ensures your review_date matches local time)
$conn->query("SET time_zone = '+05:30'");

// Admin Password for your admin_panel.php
$admin_password = "7679152928raj"; 
?>

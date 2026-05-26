<?php
// 1. Database Connection Details
$host = "sql100.infinityfree.com"; 
$user = "if0_41248478";            
$pass = "dsUJK1fB8OFXFc";           
$dbname = "if0_41248478_explore";   

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("<p style='color:red; text-align:center;'>Database connection failed.</p>");
}

$conn->set_charset("utf8mb4");
date_default_timezone_set('Asia/Kolkata');

if (!defined('ADMIN_PASSWORD')) {
    define('ADMIN_PASSWORD', '7679152928raj'); 
}

// --- EXTERNAL API KEYS ---
define('IMGBB_API_KEY', '2f33970a0734ea7d3883124fa1b4df09');

define('CLOUDINARY_CLOUD_NAME', 'dsadydkan'); 
define('CLOUDINARY_API_KEY', '921192662141937');
define('CLOUDINARY_API_SECRET', '6EU8Kh_tLUKWNMIjYW-D9xrQvSM');

// --- FIXED AUTOLOADER (Safety Switch) ---
// We check for both the file AND the internal composer directory to prevent the Fatal Error
$autoload_path = __DIR__ . '/vendor/autoload.php';
$composer_path = __DIR__ . '/vendor/composer/autoload_real.php';

if (file_exists($autoload_path) && file_exists($composer_path)) {
    require_once $autoload_path;
} else {
    // If the folder is broken, we don't 'require' it, so the site stays alive.
    // Note: Cloudinary features won't work until the folder is fixed, but the site won't crash.
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}
?>

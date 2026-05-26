<?php
$host = 'sql100.infinityfree.com';
$db   = 'if0_41248478_gallery';
$user = 'if0_41248478'; // Default for XAMPP
$pass = 'dsUJK1fB8OFXFc';     // Default for XAMPP
$charset = 'utf8mb4';

$admin_password = '7679152928rajesh'; // Set your password here

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Create table if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS instagram_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    link TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
?>
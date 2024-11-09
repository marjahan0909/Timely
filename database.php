<?php
// database.php
$host = 'localhost';
$dbname = 'userdatafor_timely';
$username = 'root'; // Replace with your DB username if different
$password = '';     // Leave blank since you didn’t set a password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

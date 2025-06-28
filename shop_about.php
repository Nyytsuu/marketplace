<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// updateprofile.php - Save changes to MySQL database

// Database connection
$servername = "localhost";
$username = "root";
$password = " " ;
$dbname = "marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch 15 random products
$sql = "SELECT * FROM products ORDER BY RAND() LIMIT 15";
$result = $conn->query($sql);
?>

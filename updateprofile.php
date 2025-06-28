<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// updateprofile.php - Save changes to MySQL database

$host = 'localhost';
$db   = 'marketplace';

$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $Address = $_POST['Address'];

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("User not logged in.");
}


    $stmt = $pdo->prepare("UPDATE sellers SET fullname = ?, email = ?, phone = ?, Address = ? WHERE id = ?");
    $stmt->execute([$fullname, $email, $phone, $Address, $user_id]);
} else {
    echo "<p>Invalid request.</p>";
}
  header("Location: sellershop.php");
    exit();
?>

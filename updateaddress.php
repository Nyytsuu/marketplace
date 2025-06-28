<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// updateaddress.php - Save shipping address to MySQL database
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
    $name = $_POST['shipname'];
    $street = $_POST['street'];
    $region = $_POST['region'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $phone = $_POST['shipphone'];

$userId = $_SESSION['user_id'];

if (!$userId) {
    echo "User not logged in.";
    exit;
}

    $stmt = $pdo->prepare("UPDATE sellers SET ship_name = ?, ship_street = ?, ship_region = ?, ship_city = ?, ship_province = ?, ship_phone = ? WHERE id = ?");
    $stmt->execute([$name, $street, $region, $city, $province, $phone, $userId]);

} else {
    echo "<p>Invalid request.</p>";
}
  header("Location: sellershop.php");
    exit();
?>
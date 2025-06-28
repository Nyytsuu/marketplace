<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$buyer_id = $_SESSION['AccountID'] ?? null;

$pdo = new PDO("mysql: host=localhost;dbname=marketplace", "root", "");
$stmt = $pdo->prepare("SELECT buyer_profile FROM buyers WHERE AccountID = ?");
$stmt->execute([$buyer_id]);
$user = $stmt->fetch();

$imagePath = (!empty($user['buyer_profile']) && file_exists($user['buyer_profile']))    
    ? $user['buyer_profile']
    : 'pics/dp.png'; // Default picture path

echo '<img src="' . $imagePath . '" class="product-preview" alt="Profile Image">';
?>

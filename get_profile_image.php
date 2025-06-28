<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userId = $_SESSION['user_id'];

$pdo = new PDO("mysql: host=localhost;dbname=marketplace", "root", "");
$stmt = $pdo->prepare("SELECT profile_image FROM sellers WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$imagePath = (!empty($user['profile_image']) && file_exists($user['profile_image']))
    ? $user['profile_image']
    : 'pics/dp.png'; // Default picture path

echo '<img src="' . $imagePath . '" class="profile_image" alt="Profile Image" width="100px">';
?>

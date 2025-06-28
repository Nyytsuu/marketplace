<?php
session_start();
$userId = $_SESSION['user_id'];

$pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");

// Get current image
$stmt = $pdo->prepare("SELECT profile_image FROM sellers WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!empty($user['profile_image']) && file_exists($user['profile_image'])) {
    unlink($user['profile_image']); // delete image file
}

// Set profile_image to NULL
$stmt = $pdo->prepare("UPDATE sellers SET profile_image = NULL WHERE id = ?");
$stmt->execute([$userId]);

// Redirect silently back to editprofilee.php
header("Location: editprofile.php");
exit;
?>

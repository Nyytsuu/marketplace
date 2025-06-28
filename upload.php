<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: finalslogin.php");
    exit;
}

$userId = $_SESSION['user_id'];

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    // Configuration
    $targetDir = "uploads/";
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = basename($_FILES['image']['name']);
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    // Validate file type
    if (!in_array(strtolower($fileExtension), $allowedTypes)) {
        header("Location: editprofile.php");
        exit;
    }
    // Create a unique filename
    $newFileName = uniqid('img_', true) . '.' . $fileExtension;
    $targetFilePath = $targetDir . $newFileName;

    // Move file
    if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
            $stmt = $pdo->prepare("UPDATE sellers SET profile_image = ? WHERE id = ?");
            $stmt->execute([$targetFilePath, $userId]);
        } catch (PDOException $e) {
            // Optional: Log error or redirect silently
        }
    }
}

// Always go back to edit profile page
header("Location: editprofile.php");
exit;
?>
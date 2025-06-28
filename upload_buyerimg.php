<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['AccountID'])) {   
    header("Location: finalslogin.php");
    exit;
}

$buyer_id = $_SESSION['AccountID'];

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    // Configuration
    $targetDir = "uploads/buyerprofiles/";
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = basename($_FILES['image']['name']);
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    // Validate file type
    if (!in_array(strtolower($fileExtension), $allowedTypes)) {
        header("Location: buyer.settings.php");
        exit;
    }
    // Create a unique filename
    $newFileName = uniqid('img_', true) . '.' . $fileExtension;
    $targetFilePath = $targetDir . $newFileName;

    // Move file
    if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
            $stmt = $pdo->prepare("UPDATE buyers SET buyer_profile = ? WHERE AccountID = ?");
            $stmt->execute([$targetFilePath, $buyer_id]);
        } catch (PDOException $e) {
            // Optional: Log error or redirect silently
        }
    }
}

// Always go back to edit profile page
header("Location: buyer.settings.php");
exit;
?>
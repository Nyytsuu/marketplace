<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in and product ID is available
if (!isset($_SESSION['user_id']) || !isset($_SESSION['product_id'])) {
    die("Product ID not found in session.");
}

$productId = $_SESSION['product_id'];

// Check if image is uploaded without error
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $targetDir = "uploads/productimages/";
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = basename($_FILES['image']['name']);
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    // Validate file type
    if (!in_array(strtolower($fileExtension), $allowedTypes)) {
        die("Invalid file type.");
    }

    // Generate unique filename
    $newFileName = uniqid('img_', true) . '.' . $fileExtension;
    $targetFilePath = $targetDir . $newFileName;

    // Create uploads directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Move uploaded file
    if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Update the product's main image
            $stmt = $pdo->prepare("UPDATE products SET main_image = ? WHERE product_id = ?");
            $stmt->execute([$targetFilePath, $productId]);
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    } else {
        die("Failed to upload the image.");
    }
}

// Redirect back to seller's product list
header("Location: sellerproductss.php");
exit;
?>

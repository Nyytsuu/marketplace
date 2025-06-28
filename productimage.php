<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$defaultImagePath = "uploads/productimages/default.jpg";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['product_name'] ?? '';
    $description = $_POST['product_description'] ?? '';
    $category = $_POST['category'] ?? '';
    $sellerId = $_SESSION['user_id'] ?? null;

    if (!$sellerId) {
        die("User not logged in.");
    }

    $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");

    // Step 1: Insert product without image first
    $stmt = $pdo->prepare("INSERT INTO products (seller_id, product_name, product_description, category) VALUES (?, ?, ?, ?)");
    $stmt->execute([$sellerId, $productName, $description, $category]);

    $productId = $pdo->lastInsertId();
    $_SESSION['product_id'] = $productId;

    // Step 2: Handle image upload (optional)
    $imagePathToStore = $defaultImagePath;

    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === 0) {
        $image = $_FILES['product_image'];
        $uploadDir = "uploads/productimages/";
        $imageName = uniqid('prod_') . '_' . basename($image['name']);
        $uploadFile = $uploadDir . $imageName;

        if (move_uploaded_file($image['tmp_name'], $uploadFile)) {
            $imagePathToStore = $uploadFile;
        }
    }

    // Step 3: Update product with image path
    $stmt = $pdo->prepare("UPDATE products SET main_image = ? WHERE product_id = ?");
    $stmt->execute([$imagePathToStore, $productId]);

    echo "Product created successfully!";
    echo '<br><a href="sellerproductss.php">Go to My Products</a>';
} else {
    echo "Invalid request.";
}
?>

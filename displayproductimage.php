<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$productId = $_GET['product_id'] ?? null;

if ($productId) {
    $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
    $stmt = $pdo->prepare("SELECT main_image FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    $imagePath = (!empty($product['main_image']) && file_exists($product['main_image']))
        ? $product['main_image']
        : 'uploads/productimages/default.jpg';
} else {
    $imagePath = 'uploads/productimages/default.jpg';
}

echo '<img src="' . htmlspecialchars($imagePath) . '" alt="Product Image" width="900px" height="400px" style="object-fit: cover;">';
?>


<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$productId = $_SESSION['product_id'] ?? null;

$pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
$stmt = $pdo->prepare("SELECT main_image FROM products WHERE product_id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

$image = (!empty($product['main_image']) && file_exists($product['main_image']))
    ? $product['main_image']
    : 'uploads/productimages/default.jpg';

echo '<img src="' . htmlspecialchars($image) . '" class="main_image" alt="Product Image" width="900px" height="400px" style="object-fit: cover;">';
?>

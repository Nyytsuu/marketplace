<?php
// delete_product.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get the product_id from GET or POST
    $productId = $_GET['product_id'] ?? $_POST['product_id'] ?? null;

    if (!$productId) {
        die("No product ID provided.");
    }

    // (Optional) Check if product exists
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        die("Product not found.");
    }

    // Delete product (variations will be deleted via CASCADE)
    $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);

    // (Optional) Delete product images
    $stmt = $pdo->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
    $stmt->execute([$productId]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

    foreach ($images as $imagePath) {
        if (file_exists($imagePath)) {
            unlink($imagePath); // Remove the file from the server
        }
    }

    // Delete product image records
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE product_id = ?");
    $stmt->execute([$productId]);

    // Delete main product image if needed
    if (!empty($product['main_image']) && file_exists($product['main_image'])) {
        unlink($product['main_image']);
    }

    echo "Product and associated variations deleted successfully.";
     header("Location: sellerproductss.php?page=1");
    exit();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

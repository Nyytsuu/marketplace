<?php
// Then in every script
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get product data
    
    $productName = $_POST['product_name'] ?? '';
    $price = $_POST['price'] ?? null;
    $stock = $_POST['stocks'] ?? null;
    $productId = $_SESSION['product_id'] ?? null;
  $sellerId = $_SESSION['seller_id'] ?? null;

    // Check if variations are submitted
    $hasVariations = !empty($_POST['variation_name']);

    if (!$hasVariations) {
        // No variations: Insert product with price and stock
        $stmt = $pdo->prepare("INSERT INTO products (seller_id, product_name, price, stocks) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sellerId, $productName, $price, $stock]);
        echo "Product saved without variations.";
    } else {
        // With variations: Insert product without price/stock
        $stmt = $pdo->prepare("INSERT INTO products (seller_id, product_name) VALUES (?, ?)");
        $stmt->execute([$sellerId, $productName]);
        $product_id = $pdo->lastInsertId();

        // Extract variation data
        $variationNames = $_POST['variation_name'];
        $variationPrices = $_POST['variation_price'];
        $variationStocks = $_POST['variation_stock'];

        foreach ($variationNames as $i => $name) {
            // Insert each variation
            $stmt = $pdo->prepare("INSERT INTO product_variations (product_id, variation_name, price, stock) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $product_id,
                $name,
                $variationPrices[$i] ?? null,
                $variationStocks[$i] ?? 0
            ]);
            $variation_id = $pdo->lastInsertId();

            // Handle image uploads for each variation
            $imageKey = "variation_image" . $i;
            if (!empty($_FILES[$imageKey]['name'][0])) {
                foreach ($_FILES[$imageKey]['tmp_name'] as $j => $tmpName) {
                    if ($tmpName) {
                        $originalName = basename($_FILES[$imageKey]['name'][$j]);
                        $imageName = uniqid('var_') . '_' . $originalName;
                        $uploadPath = "uploads/variationimages/" . $imageName;

                        if (move_uploaded_file($tmpName, $uploadPath)) {
                            // Save image path in the database
                            $stmt = $pdo->prepare("INSERT INTO variation_image (variation_id, image_path) VALUES (?, ?)");
                            $stmt->execute([$variation_id, $uploadPath]);
                        }
                    }
                }
            }
        }

        echo "Product with variations saved successfully.";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

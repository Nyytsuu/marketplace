
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=marketplace", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sellerId = $_SESSION['user_id'] ?? null;
    if (!$sellerId) {
        die("User not logged in.");
    }

    // Insert product
    $productName = $_POST['product_name'] ?? '';
    $description = $_POST['product_description'] ?? '';
    $price = $_POST['price'] ?? '';
    $category = $_POST['category'] ?? '';
    $stocks = $_POST['stocks'] ?? '';

    $stmt = $pdo->prepare("
        INSERT INTO products (seller_id, product_name, product_description, category, price, stocks) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$sellerId, $productName, $description, $category, $price, $stocks]);

    $productId = $pdo->lastInsertId(); // ✅ Keep this

    // Handle main product images
    $mainImageSet = false;
    if (!empty($_FILES['product_image']['name'][0])) {
        foreach ($_FILES['product_image']['tmp_name'] as $index => $tmpName) {
            if ($tmpName) {
                if (isset($_FILES['product_image']['error'][$index]) && $_FILES['product_image']['error'][$index] !== UPLOAD_ERR_OK) {
                    echo "Upload Error in file $index: " . $_FILES['product_image']['error'][$index];
                    continue;
                }

                if (!file_exists($tmpName)) {
                    echo "Temp file missing for main image index $index";
                    continue;
                }

                $originalName = basename($_FILES['product_image']['name'][$index]);
                $imageName = uniqid('main_') . '_' . $originalName;
                $uploadPath = "uploads/productimages/" . $imageName;

                if (move_uploaded_file($tmpName, $uploadPath)) {
                    if (!$mainImageSet) {
                        $stmt = $pdo->prepare("UPDATE products SET main_image = ? WHERE product_id = ?");
                        $stmt->execute([$uploadPath, $productId]);
                        $mainImageSet = true;
                    }

                    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                    $stmt->execute([$productId, $uploadPath]);
                } else {
                    echo "Failed to move main image file $index";
                }
            }
        }
    }

 // Variations
$variationNames = $_POST['variation_name'] ?? [];
$variationColors = $_POST['variation_color'] ?? [];
$variationSizes = $_POST['variation_size'] ?? [];
$variationPrices = $_POST['variation_price'] ?? [];
$variationStocks = $_POST['variation_stock'] ?? [];
$variationDesc = $_POST['additional_information'] ??[];
$variationSubCat = $_POST['sub_category'] ??[];
$variationImages = $_FILES['variation_image'] ?? [];

if (!empty($variationNames)) {
    for ($i = 0; $i < count($variationNames); $i++) {
        $name = $variationNames[$i] ?? '';
        $color = $variationColors[$i] ?? '';
        $size = $variationSizes[$i] ?? '';
        $price = $variationPrices[$i] ?? 0;
        $stock = $variationStocks[$i] ?? 0;
        $adddesc = $variationDesc[$i] ?? '';
        $subcat = $variationSubCat[$i] ?? '';

        $imagePath = null;
        if (isset($variationImages['tmp_name'][$i]) && $variationImages['tmp_name'][$i] != '') {
            $originalName = basename($variationImages['name'][$i]);
            $imageName = uniqid('var_') . '_' . $originalName;
            $uploadPath = "uploads/variationimages/" . $imageName;

            if (move_uploaded_file($variationImages['tmp_name'][$i], $uploadPath)) {
                $imagePath = $uploadPath;
            } else {
                echo "Failed to upload image for variation #{$i}.";
            }
        }

        $stmt = $pdo->prepare("
            INSERT INTO product_variations 
                (product_id, variation_name, sub_category, additional_information, variation_color, variation_size, variation_price, variation_stock,   variation_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $productId,
            $name,
            $subcat,       
            $adddesc,
            $color,
            $size,
            $price,
            $stock,
            $imagePath
        ]);
    }
}


    // Variations
header("Location: sellerproductss.php");
exit();


} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>
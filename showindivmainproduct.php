<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli("localhost", "root", "", "marketplace");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get product ID from either POST or GET
$productId = $_POST['product_id'] ?? $_GET['id'] ?? 0;
if (!$productId) {
    die("No product ID provided.");
}

// Fetch product details (no size here)
$stmt = $conn->prepare("SELECT product_name, main_image, price, stocks FROM products WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

$name = $product['product_name'];
$mainImage = $product['main_image'] ?? 'uploads/fallback.jpg';
$price = $product['price'];
$stocks = $product['stocks']; // optional if you want global stock
$bought = $product['bought'] ?? 0; // if tracking bought count


// 🎨 Get distinct color options
$colorOptions = [];
$colorStmt = $conn->prepare("SELECT DISTINCT variation_color FROM product_variations WHERE product_id = ? AND variation_color IS NOT NULL AND variation_color != ''");
$colorStmt->bind_param("i", $productId);
$colorStmt->execute();
$result = $colorStmt->get_result();
while ($row = $result->fetch_assoc()) {
    $colorOptions[] = $row['variation_color'];
}

// 🖼 Get variation images (for thumbnails)
// Query variations for current product
$sql = "SELECT variation_color, variation_image FROM product_variations WHERE product_id = ? ORDER BY variation_id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productId);
$stmt->execute();

$result = $stmt->get_result();

$variationImagesByColor = [];

while ($row = $result->fetch_assoc()) {
    $color = $row['variation_color'];
    $image = $row['variation_image'];

    if (!isset($variationImagesByColor[$color])) {
        $variationImagesByColor[$color] = [];
    }
    $variationImagesByColor[$color][] = $image;
}

// Pass it to JS

// 🟣 Get distinct sizes from product_variations
// Fetch sizes from DB as before
$sizeOptions = [];
$sizeStmt = $conn->prepare("SELECT DISTINCT variation_size FROM product_variations WHERE product_id = ? AND variation_size IS NOT NULL AND variation_size != ''");
$sizeStmt->bind_param("i", $productId);
$sizeStmt->execute();
$sizeResult = $sizeStmt->get_result();
while ($row = $sizeResult->fetch_assoc()) {
    $sizeOptions[] = trim($row['variation_size']);
}

// Define custom size order
$customOrder = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL']; // Add more if needed

// Sort sizes using the custom order
usort($sizeOptions, function ($a, $b) use ($customOrder) {
    $posA = array_search($a, $customOrder);
    $posB = array_search($b, $customOrder);
    return $posA - $posB;
});

$thumbnails = [];

// Fetch additional images
$imageStmt = $conn->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
$imageStmt->bind_param("i", $productId);
$imageStmt->execute();
$imageResult = $imageStmt->get_result();
while ($row = $imageResult->fetch_assoc()) {
    $thumbnails[] = $row['image_path'];
}

// Normalize comparison (remove directory path for comparison)
$thumbnailBasenames = array_map('basename', $thumbnails);
$mainImageBase = basename($mainImage);

// Add main image if not already in thumbnails
if (!in_array($mainImageBase, $thumbnailBasenames)) {
    array_unshift($thumbnails, $mainImage);
}

// Remove duplicates (strictly, just in case)
$thumbnails = array_unique($thumbnails);

// Debug check
// var_dump($thumbnails);

?>
